<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Repository\TimeEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class TimeTrackingService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TimeEntryRepository $timeEntryRepository
    ) {}

    /**
     * Start tracking time for a task
     */
    public function startTimer(Task $task, User $user, ?string $description = null): TimeEntry
    {
        $activeEntry = $this->timeEntryRepository->findActiveEntry($user, $task);

        if ($activeEntry !== null) {
            throw new \RuntimeException('Timer is already running for this task');
        }

        $this->stopAllActiveTimersForUser($user);

        $timeEntry = new TimeEntry();
        $timeEntry->setTask($task);
        $timeEntry->setUser($user);
        $timeEntry->setStartTime(new \DateTime());
        $timeEntry->setDescription($description);

        $this->entityManager->persist($timeEntry);
        $this->entityManager->flush();

        return $timeEntry;
    }

    /**
     * Stop tracking time for a specific entry
     */
    public function stopTimer(TimeEntry $timeEntry): TimeEntry
    {
        if (!$timeEntry->isRunning()) {
            throw new \RuntimeException('Timer is not running');
        }

        $timeEntry->stop();
        $this->entityManager->flush();

        return $timeEntry;
    }

    /**
     * Stop all active timers for a user
     */
    public function stopAllActiveTimersForUser(User $user): int
    {
        $activeEntries = $this->timeEntryRepository->findActiveEntriesForUser($user);

        foreach ($activeEntries as $entry) {
            $entry->stop();
        }

        if (count($activeEntries) > 0) {
            $this->entityManager->flush();
        }

        return count($activeEntries);
    }

    /**
     * Get active timer for a user on any task
     */
    public function getActiveTimerForUser(User $user): ?TimeEntry
    {
        return $this->timeEntryRepository->findActiveTimerForUser($user);
    }

    /**
     * Add manual time entry (for backdated entries)
     */
    public function addManualEntry(
        Task $task,
        User $user,
        \DateTime $startTime,
        \DateTime $endTime,
        ?string $description = null
    ): TimeEntry {
        if ($endTime <= $startTime) {
            throw new \InvalidArgumentException('End time must be after start time');
        }

        $timeEntry = new TimeEntry();
        $timeEntry->setTask($task);
        $timeEntry->setUser($user);
        $timeEntry->setStartTime($startTime);
        $timeEntry->setEndTime($endTime);
        $timeEntry->setDescription($description);

        $this->entityManager->persist($timeEntry);
        $this->entityManager->flush();

        return $timeEntry;
    }

    /**
     * Update time entry
     */
    public function updateTimeEntry(
        TimeEntry $timeEntry,
        ?\DateTime $startTime = null,
        ?\DateTime $endTime = null,
        ?string $description = null
    ): TimeEntry {
        if ($startTime !== null) {
            $timeEntry->setStartTime($startTime);
        }

        if ($endTime !== null) {
            if ($endTime <= $timeEntry->getStartTime()) {
                throw new \InvalidArgumentException('End time must be after start time');
            }
            $timeEntry->setEndTime($endTime);
        }

        if ($description !== null) {
            $timeEntry->setDescription($description);
        }

        $this->entityManager->flush();

        return $timeEntry;
    }

    /**
     * Delete time entry
     */
    public function deleteTimeEntry(TimeEntry $timeEntry): void
    {
        $this->entityManager->remove($timeEntry);
        $this->entityManager->flush();
    }

    /**
     * Get total time tracked for a task (in hours)
     */
    public function getTotalTimeForTask(Task $task): float
    {
        $totalSeconds = $this->timeEntryRepository->getTotalTimeForTask($task);
        return $totalSeconds / 3600;
    }

    /**
     * Get time entries for a task
     *
     * @return TimeEntry[]
     */
    public function getTimeEntriesForTask(Task $task, ?int $limit = null): array
    {
        return $this->timeEntryRepository->findByTask($task, $limit);
    }

    /**
     * Get time tracking summary for a user within date range
     * @return array<string, mixed>
     */
    public function getUserTimeSummary(
        User $user,
        \DateTime $startDate,
        \DateTime $endDate
    ): array {
        $entries = $this->timeEntryRepository->findByUserAndDateRange($user, $startDate, $endDate);

        $summary = [
            'total_hours' => 0,
            'entries_count' => count($entries),
            'by_task' => [],
            'by_date' => []
        ];

        foreach ($entries as $entry) {
            if ($entry->isRunning()) {
                continue;
            }

            $hours = $entry->getDurationInHours();
            $summary['total_hours'] += $hours;

            $taskId = $entry->getTask()->getId()->toRfc4122();
            if (!isset($summary['by_task'][$taskId])) {
                $summary['by_task'][$taskId] = [
                    'task_title' => $entry->getTask()->getTitle(),
                    'hours' => 0,
                    'entries_count' => 0
                ];
            }
            $summary['by_task'][$taskId]['hours'] += $hours;
            $summary['by_task'][$taskId]['entries_count']++;

            $date = $entry->getStartTime()->format('Y-m-d');
            if (!isset($summary['by_date'][$date])) {
                $summary['by_date'][$date] = 0;
            }
            $summary['by_date'][$date] += $hours;
        }

        $summary['total_hours'] = round($summary['total_hours'], 2);

        return $summary;
    }

    /**
     * Calculate remaining time estimate
     */
    public function getRemainingEstimate(Task $task): ?float
    {
        $estimatedHours = $task->getEstimatedHours();
        if ($estimatedHours === null) {
            return null;
        }

        $trackedHours = $this->getTotalTimeForTask($task);
        $remaining = $estimatedHours - $trackedHours;

        return $remaining;
    }

    /**
     * Get progress percentage based on time tracking
     */
    public function getProgressPercentage(Task $task): ?int
    {
        $estimatedHours = $task->getEstimatedHours();
        if ($estimatedHours === null || $estimatedHours == 0) {
            return null;
        }

        $trackedHours = $this->getTotalTimeForTask($task);
        $percentage = ($trackedHours / $estimatedHours) * 100;

        return min(100, (int) round($percentage));
    }
}
