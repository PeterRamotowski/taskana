<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\Enum\TaskStatus;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;

class RecurringTaskService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}

    /**
     * Generate recurring task instances that are due
     * Should be called by a cron job daily
     */
    public function generateDueRecurringTasks(\DateTime $upToDate = null): int
    {
        $upToDate = $upToDate ?? new \DateTime('+1 week');
        $tasksCreated = 0;

        $recurringTasks = $this->entityManager->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->where('t.isRecurring = :isRecurring')
            ->andWhere('t.parentTask IS NULL')
            ->andWhere('t.recurrencePattern IS NOT NULL')
            ->setParameter('isRecurring', true)
            ->getQuery()
            ->getResult();

        foreach ($recurringTasks as $task) {
            $generated = $this->generateInstancesForTask($task, $upToDate);
            $tasksCreated += $generated;
        }

        $this->entityManager->flush();

        $this->logger->info("Generated {$tasksCreated} recurring task instances");

        return $tasksCreated;
    }

    /**
     * Generate instances for a specific recurring task
     */
    private function generateInstancesForTask(Task $masterTask, \DateTime $upToDate): int
    {
        $instancesCreated = 0;
        $lastGeneration = $masterTask->getLastRecurrenceGeneration() ?? $masterTask->getCreatedDate();

        $currentDate = clone($lastGeneration ?? new \DateTime());

        while (true) {
            $nextOccurrence = $masterTask->getNextOccurrenceDate($currentDate);

            if ($nextOccurrence === null || $nextOccurrence > $upToDate) {
                break;
            }

            if (!$this->instanceExistsForDate($masterTask, $nextOccurrence)) {
                $this->createTaskInstance($masterTask, $nextOccurrence);
                $instancesCreated++;
            }

            $currentDate = $nextOccurrence;
        }

        if ($instancesCreated > 0) {
            $masterTask->setLastRecurrenceGeneration(new \DateTime());
        }

        return $instancesCreated;
    }

    /**
     * Check if a task instance already exists for a specific date
     */
    private function instanceExistsForDate(Task $masterTask, \DateTime $date): bool
    {
        $startOfDay = (clone $date)->setTime(0, 0, 0);
        $endOfDay = (clone $date)->setTime(23, 59, 59);

        $existingInstance = $this->entityManager->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->where('t.parentTask = :parentTask')
            ->andWhere('t.dueDate BETWEEN :startOfDay AND :endOfDay')
            ->setParameter('parentTask', $masterTask)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $existingInstance !== null;
    }

    /**
     * Create a new task instance from a recurring task
     */
    private function createTaskInstance(Task $masterTask, \DateTime $dueDate): Task
    {
        $instance = new Task();
        $instance->setId(Uuid::v4()->toRfc4122());
        $instance->setTitle($masterTask->getTitle());
        $instance->setDescription($masterTask->getDescription());
        $instance->setPriority($masterTask->getPriority());
        $instance->setStatus(TaskStatus::PENDING);
        $instance->setProject($masterTask->getProject());
        $instance->setWorker($masterTask->getWorker());
        $instance->setCreator($masterTask->getCreator());
        $instance->setDueDate($dueDate);
        $instance->setEstimatedHours($masterTask->getEstimatedHours());
        $instance->setParentTask($masterTask);

        $instance->setIsRecurring(false);

        $this->entityManager->persist($instance);

        $this->logger->debug("Created recurring task instance", [
            'master_task_id' => $masterTask->getId()->toRfc4122(),
            'instance_id' => $instance->getId()->toRfc4122(),
            'due_date' => $dueDate->format('Y-m-d')
        ]);

        return $instance;
    }

    /**
     * Create a single instance manually (useful for testing or manual generation)
     */
    public function createSingleInstance(Task $masterTask, ?\DateTime $dueDate = null): Task
    {
        if (!$masterTask->getIsRecurring()) {
            throw new \InvalidArgumentException('Task is not a recurring task');
        }

        $dueDate = $dueDate ?? $masterTask->getNextOccurrenceDate();

        if ($dueDate === null) {
            throw new \InvalidArgumentException('Cannot determine next occurrence date');
        }

        $instance = $this->createTaskInstance($masterTask, $dueDate);
        $this->entityManager->flush();

        return $instance;
    }

    /**
     * Update all future instances of a recurring task when master is updated
     * @param array<string, mixed> $fieldsToUpdate
     */
    public function updateFutureInstances(Task $masterTask, array $fieldsToUpdate): int
    {
        if (!$masterTask->getIsRecurring()) {
            return 0;
        }

        $futureInstances = $this->entityManager->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->where('t.parentTask = :parentTask')
            ->andWhere('t.status = :status')
            ->andWhere('t.dueDate > :now')
            ->setParameter('parentTask', $masterTask)
            ->setParameter('status', TaskStatus::PENDING)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        $updatedCount = 0;

        foreach ($futureInstances as $instance) {
            foreach ($fieldsToUpdate as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($instance, $setter)) {
                    $instance->$setter($value);
                    $updatedCount++;
                }
            }
        }

        if ($updatedCount > 0) {
            $this->entityManager->flush();
        }

        return count($futureInstances);
    }
}
