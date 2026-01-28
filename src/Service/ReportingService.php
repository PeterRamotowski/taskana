<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Entity\Project;
use App\Repository\TimeEntryRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReportingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TimeEntryRepository $timeEntryRepository
    ) {}

    /**
     * Generate comprehensive time report for a user
     * @return array<string, mixed>
     */
    public function generateUserReport(
        User $user,
        \DateTime $startDate,
        \DateTime $endDate
    ): array {
        $entries = $this->timeEntryRepository->findByUserAndDateRange($user, $startDate, $endDate);

        $report = [
            'user' => [
                'id' => $user->getId()->toRfc4122(),
                'name' => $user->getName(),
                'email' => $user->getEmail()
            ],
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'days' => $startDate->diff($endDate)->days + 1
            ],
            'summary' => [
                'total_hours' => 0,
                'total_entries' => 0,
                'completed_tasks' => 0,
                'average_hours_per_day' => 0,
                'billable_hours' => 0
            ],
            'by_task' => [],
            'by_project' => [],
            'by_date' => [],
            'by_day_of_week' => $this->initializeDaysOfWeek(),
            'by_priority' => [
                'low' => 0,
                'medium' => 0,
                'high' => 0
            ],
            'top_tasks' => [],
            'entries' => []
        ];

        $taskMap = [];
        $projectMap = [];
        $dateMap = [];

        foreach ($entries as $entry) {
            if ($entry->isRunning()) {
                continue;
            }

            $hours = $entry->getDurationInHours();
            $report['summary']['total_hours'] += $hours;
            $report['summary']['total_entries']++;

            $task = $entry->getTask();
            $taskId = $task->getId()->toRfc4122();
            $date = $entry->getStartTime()->format('Y-m-d');
            $dayOfWeek = $entry->getStartTime()->format('l');

            $project = $task->getProject();
            $projectId = $project ? $project->getId()->toRfc4122() : null;

            if (!isset($taskMap[$taskId])) {
                $taskMap[$taskId] = [
                    'task_id' => $taskId,
                    'task_title' => $task->getTitle(),
                    'task_status' => $task->getStatus()->value,
                    'task_priority' => $task->getPriority()->value,
                    'estimated_hours' => $task->getEstimatedHours(),
                    'total_hours' => 0,
                    'entries_count' => 0,
                    'project_id' => $projectId
                ];
            }
            $taskMap[$taskId]['total_hours'] += $hours;
            $taskMap[$taskId]['entries_count']++;

            if ($project) {
                if (!isset($projectMap[$projectId])) {
                    $projectMap[$projectId] = [
                        'project_id' => $projectId,
                        'project_title' => $project->getTitle(),
                        'total_hours' => 0,
                        'tasks_count' => 0,
                        'entries_count' => 0
                    ];
                }
                $projectMap[$projectId]['total_hours'] += $hours;
                $projectMap[$projectId]['entries_count']++;
            }

            if (!isset($dateMap[$date])) {
                $dateMap[$date] = 0;
            }
            $dateMap[$date] += $hours;

            $report['by_day_of_week'][$dayOfWeek] += $hours;

            $priority = $task->getPriority()->value;
            $report['by_priority'][$priority] += $hours;

            $report['entries'][] = [
                'id' => $entry->getId()->toRfc4122(),
                'task_id' => $taskId,
                'task_title' => $task->getTitle(),
                'project_title' => $project?->getTitle(),
                'start_time' => $entry->getStartTime()->format('Y-m-d H:i:s'),
                'end_time' => $entry->getEndTime()?->format('Y-m-d H:i:s'),
                'duration_hours' => $hours,
                'description' => $entry->getDescription()
            ];
        }

        $report['by_task'] = array_values($taskMap);
        $report['by_project'] = array_values($projectMap);
        $report['by_date'] = $this->formatDateMap($dateMap);

        foreach ($report['by_project'] as &$projectData) {
            if (is_array($projectData) && isset($projectData['project_id'])) {
                $projectId = $projectData['project_id'];
                $projectData['tasks_count'] = count(array_filter($report['by_task'], function ($task) use ($projectId) {
                    return is_array($task) && isset($task['project_id']) && $task['project_id'] === $projectId;
                }));
            }
        }

        $report['top_tasks'] = $this->getTopTasks($report['by_task'], 5);

        if ($report['period']['days'] > 0) {
            $report['summary']['average_hours_per_day'] = round(
                $report['summary']['total_hours'] / $report['period']['days'],
                2
            );
        }

        $report['summary']['total_hours'] = round($report['summary']['total_hours'], 2);

        return $report;
    }

    /**
     * Generate team-wide report
     * @return array<string, mixed>
     */
    public function generateTeamReport(
        \DateTime $startDate,
        \DateTime $endDate,
        ?Project $project = null
    ): array {
        $startDate = (clone $startDate)->setTime(0, 0, 0);
        $endDate = (clone $endDate)->setTime(23, 59, 59);

        $qb = $this->entityManager->getRepository(TimeEntry::class)
            ->createQueryBuilder('te')
            ->where('te.startTime BETWEEN :startDate AND :endDate')
            ->andWhere('te.endTime IS NOT NULL')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if ($project !== null) {
            $qb->join('te.task', 't')
                ->andWhere('t.project = :project')
                ->setParameter('project', $project);
        }

        $entries = $qb->getQuery()->getResult();

        $report = [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'days' => $startDate->diff($endDate)->days + 1
            ],
            'project' => $project ? [
                'id' => $project->getId()->toRfc4122(),
                'title' => $project->getTitle()
            ] : null,
            'summary' => [
                'total_hours' => 0,
                'total_entries' => 0,
                'total_users' => 0,
                'total_tasks' => 0,
                'average_hours_per_user' => 0
            ],
            'by_user' => [],
            'by_task' => [],
            'by_project' => [],
            'by_date' => [],
            'top_contributors' => [],
            'productivity_trends' => []
        ];

        $userMap = [];
        $taskMap = [];
        $projectMap = [];
        $dateMap = [];

        foreach ($entries as $entry) {
            $hours = $entry->getDurationInHours();
            $report['summary']['total_hours'] += $hours;
            $report['summary']['total_entries']++;

            $user = $entry->getUser();
            $userId = $user->getId()->toRfc4122();

            if (!isset($userMap[$userId])) {
                $userMap[$userId] = [
                    'user_id' => $userId,
                    'user_name' => $user->getName(),
                    'total_hours' => 0,
                    'entries_count' => 0,
                    'tasks_count' => 0
                ];
            }
            $userMap[$userId]['total_hours'] += $hours;
            $userMap[$userId]['entries_count']++;

            $task = $entry->getTask();
            $taskId = $task->getId()->toRfc4122();

            if (!isset($taskMap[$taskId])) {
                $taskMap[$taskId] = [
                    'task_id' => $taskId,
                    'task_title' => $task->getTitle(),
                    'total_hours' => 0,
                    'contributors_count' => 0
                ];
            }
            $taskMap[$taskId]['total_hours'] += $hours;

            $taskProject = $task->getProject();
            if ($taskProject) {
                $projectId = $taskProject->getId()->toRfc4122();
                if (!isset($projectMap[$projectId])) {
                    $projectMap[$projectId] = [
                        'project_id' => $projectId,
                        'project_title' => $taskProject->getTitle(),
                        'total_hours' => 0
                    ];
                }
                $projectMap[$projectId]['total_hours'] += $hours;
            }

            $date = $entry->getStartTime()->format('Y-m-d');
            if (!isset($dateMap[$date])) {
                $dateMap[$date] = 0;
            }
            $dateMap[$date] += $hours;
        }

        $report['by_user'] = array_values($userMap);
        $report['by_task'] = array_values($taskMap);
        $report['by_project'] = array_values($projectMap);
        $report['by_date'] = $this->formatDateMap($dateMap);

        $report['summary']['total_users'] = count($userMap);
        $report['summary']['total_tasks'] = count($taskMap);

        if ($report['summary']['total_users'] > 0) {
            $report['summary']['average_hours_per_user'] = round(
                $report['summary']['total_hours'] / $report['summary']['total_users'],
                2
            );
        }

        $report['top_contributors'] = $this->getTopContributors($report['by_user'], 5);

        $report['productivity_trends'] = $this->calculateProductivityTrends($dateMap);

        $report['summary']['total_hours'] = round($report['summary']['total_hours'], 2);

        return $report;
    }

    /**
     * Generate project-specific report
     * @return array<string, mixed>
     */
    public function generateProjectReport(
        Project $project,
        \DateTime $startDate,
        \DateTime $endDate
    ): array {
        $startDateStart = (clone $startDate)->setTime(0, 0, 0);
        $endDateEnd = (clone $endDate)->setTime(23, 59, 59);

        $tasks = $this->entityManager->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->where('t.project = :project')
            ->setParameter('project', $project->getId()->toBinary(), 'uuid')
            ->getQuery()
            ->getResult();

        $report = [
            'project' => [
                'id' => $project->getId()->toRfc4122(),
                'title' => $project->getTitle(),
                'description' => $project->getDescription()
            ],
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_tasks' => count($tasks),
                'completed_tasks' => 0,
                'in_progress_tasks' => 0,
                'pending_tasks' => 0,
                'total_hours' => 0,
                'estimated_hours' => 0,
                'remaining_hours' => 0,
                'completion_percentage' => 0
            ],
            'by_user' => [],
            'by_task' => [],
            'by_status' => [
                'pending' => ['count' => 0, 'hours' => 0],
                'active' => ['count' => 0, 'hours' => 0],
                'complete' => ['count' => 0, 'hours' => 0]
            ]
        ];

        $userMap = [];

        foreach ($tasks as $task) {
            $status = $task->getStatus()->value;
            $report['by_status'][$status]['count']++;

            if ($status === 'complete') {
                $report['summary']['completed_tasks']++;
            } elseif ($status === 'active') {
                $report['summary']['in_progress_tasks']++;
            } else {
                $report['summary']['pending_tasks']++;
            }

            $estimatedHours = $task->getEstimatedHours() ?? 0;
            $report['summary']['estimated_hours'] += $estimatedHours;

            $taskEntries = $this->timeEntryRepository->findByTask($task);
            $taskHours = 0;

            foreach ($taskEntries as $entry) {
                if ($entry->isRunning()) continue;

                if ($entry->getStartTime() < $startDateStart || $entry->getStartTime() > $endDateEnd) {
                    continue;
                }

                $hours = $entry->getDurationInHours();
                $taskHours += $hours;
                $report['summary']['total_hours'] += $hours;
                $report['by_status'][$status]['hours'] += $hours;

                $user = $entry->getUser();
                $userId = $user->getId()->toRfc4122();
                if (!isset($userMap[$userId])) {
                    $userMap[$userId] = [
                        'user_id' => $userId,
                        'user_name' => $user->getName(),
                        'total_hours' => 0,
                        'tasks_worked_on' => []
                    ];
                }
                $userMap[$userId]['total_hours'] += $hours;
                $userMap[$userId]['tasks_worked_on'][$task->getId()->toRfc4122()] = true;
            }

            $report['by_task'][] = [
                'task_id' => $task->getId()->toRfc4122(),
                'task_title' => $task->getTitle(),
                'status' => $status,
                'priority' => $task->getPriority()->value,
                'estimated_hours' => $estimatedHours,
                'actual_hours' => round($taskHours, 2),
                'variance' => round($taskHours - $estimatedHours, 2),
                'assigned_to' => $task->getWorker()->getName()
            ];
        }

        foreach ($userMap as &$userData) {
            $userData['tasks_count'] = count($userData['tasks_worked_on']);
            unset($userData['tasks_worked_on']);
        }

        $report['by_user'] = array_values($userMap);
        $report['summary']['remaining_hours'] = round(
            $report['summary']['estimated_hours'] - $report['summary']['total_hours'],
            2
        );

        if ($report['summary']['estimated_hours'] > 0) {
            $report['summary']['completion_percentage'] = min(100, round(
                ($report['summary']['total_hours'] / $report['summary']['estimated_hours']) * 100,
                1
            ));
        }

        $report['summary']['total_hours'] = round($report['summary']['total_hours'], 2);
        $report['summary']['estimated_hours'] = round($report['summary']['estimated_hours'], 2);

        return $report;
    }

    /**
     * Generate time comparison report between two periods
     * @return array<string, mixed>
     */
    public function generateComparisonReport(
        User $user,
        \DateTime $period1Start,
        \DateTime $period1End,
        \DateTime $period2Start,
        \DateTime $period2End
    ): array {
        $period1Data = $this->generateUserReport($user, $period1Start, $period1End);
        $period2Data = $this->generateUserReport($user, $period2Start, $period2End);

        $p1Summary = is_array($period1Data['summary'] ?? null) ? $period1Data['summary'] : [];
        $p2Summary = is_array($period2Data['summary'] ?? null) ? $period2Data['summary'] : [];

        $p1TotalHours = (float)($p1Summary['total_hours'] ?? 0);
        $p2TotalHours = (float)($p2Summary['total_hours'] ?? 0);
        $p1TotalEntries = (int)($p1Summary['total_entries'] ?? 0);
        $p2TotalEntries = (int)($p2Summary['total_entries'] ?? 0);
        $p1AvgHours = (float)($p1Summary['average_hours_per_day'] ?? 0);
        $p2AvgHours = (float)($p2Summary['average_hours_per_day'] ?? 0);

        $comparison = [
            'user' => $period1Data['user'],
            'period1' => $period1Data['period'],
            'period2' => $period2Data['period'],
            'comparison' => [
                'total_hours_change' => round($p2TotalHours - $p1TotalHours, 2),
                'total_hours_change_percent' => $this->calculatePercentageChange($p1TotalHours, $p2TotalHours),
                'entries_change' => $p2TotalEntries - $p1TotalEntries,
                'avg_hours_per_day_change' => round($p2AvgHours - $p1AvgHours, 2)
            ],
            'period1_summary' => $period1Data['summary'],
            'period2_summary' => $period2Data['summary']
        ];

        return $comparison;
    }

    /**
     * Initialize days of week with zero hours
     * @return array<string, float>
     */
    private function initializeDaysOfWeek(): array
    {
        return [
            'Monday' => 0,
            'Tuesday' => 0,
            'Wednesday' => 0,
            'Thursday' => 0,
            'Friday' => 0,
            'Saturday' => 0,
            'Sunday' => 0
        ];
    }

    /**
     * Format date map for better readability
     * @param array<string, float> $dateMap
     * @return array<int, array<string, mixed>>
     */
    private function formatDateMap(array $dateMap): array
    {
        $formatted = [];
        foreach ($dateMap as $date => $hours) {
            $formatted[] = [
                'date' => $date,
                'hours' => round($hours, 2)
            ];
        }
        usort($formatted, fn($a, $b) => strcmp($a['date'], $b['date']));
        return $formatted;
    }

    /**
     * Get top N tasks by hours
     * @param array<int, array<string, mixed>> $tasks
     * @return array<int, array<string, mixed>>
     */
    private function getTopTasks(array $tasks, int $limit): array
    {
        usort($tasks, fn($a, $b) => $b['total_hours'] <=> $a['total_hours']);
        return array_slice($tasks, 0, $limit);
    }

    /**
     * Get top N contributors by hours
     * @param array<int, array<string, mixed>> $users
     * @return array<int, array<string, mixed>>
     */
    private function getTopContributors(array $users, int $limit): array
    {
        usort($users, fn($a, $b) => $b['total_hours'] <=> $a['total_hours']);
        return array_slice($users, 0, $limit);
    }

    /**
     * Calculate productivity trends from date map
     * @param array<string, float> $dateMap
     * @return array<int, array<string, mixed>>
     */
    private function calculateProductivityTrends(array $dateMap): array
    {
        $trends = [];
        $dates = array_keys($dateMap);
        sort($dates);

        foreach ($dates as $date) {
            $trends[] = [
                'date' => $date,
                'hours' => round($dateMap[$date], 2),
                'day_of_week' => (new \DateTime($date))->format('l')
            ];
        }

        return $trends;
    }

    /**
     * Calculate percentage change between two values
     */
    private function calculatePercentageChange(float $oldValue, float $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue > 0 ? 100.0 : 0.0;
        }
        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }
}
