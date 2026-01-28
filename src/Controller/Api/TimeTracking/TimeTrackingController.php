<?php

namespace App\Controller\Api\TimeTracking;

use App\Entity\Task;
use App\Entity\TimeEntry;
use App\Entity\User;
use App\Repository\TimeEntryRepository;
use App\Service\TimeTrackingService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/time-tracking')]
#[IsGranted('ROLE_USER')]
class TimeTrackingController extends AbstractController
{
    public function __construct(
        private readonly TimeTrackingService $timeTrackingService,
        private readonly TimeEntryRepository $timeEntryRepository
    ) {
    }

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            throw new \RuntimeException('User must be authenticated');
        }
        
        return $user;
    }

    #[Route('/task/{task}/start', name: 'api_time_tracking_start', requirements: ['task' => '%uuid_pattern%'], methods: ['POST'])]
    #[OA\Post(
        summary: 'Start time tracking for a task',
        tags: ['Time Tracking']
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'description', type: 'string', nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 200, description: 'Timer started successfully')]
    #[OA\Response(response: 400, description: 'Timer already running')]
    public function startTimer(Task $task, Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $data = json_decode($request->getContent(), true) ?? [];
        $description = $data['description'] ?? null;

        try {
            $timeEntry = $this->timeTrackingService->startTimer($task, $user, $description);
            
            return $this->json([
                'status' => 'success',
                'message' => 'Timer started',
                'data' => $this->serializeTimeEntry($timeEntry)
            ]);
        } catch (\RuntimeException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/entry/{id}/stop', name: 'api_time_tracking_stop', requirements: ['id' => '%uuid_pattern%'], methods: ['POST'])]
    #[OA\Post(
        summary: 'Stop time tracking',
        tags: ['Time Tracking']
    )]
    #[OA\Response(response: 200, description: 'Timer stopped successfully')]
    #[OA\Response(response: 400, description: 'Timer not running')]
    public function stopTimer(string $id): JsonResponse
    {
        $timeEntry = $this->timeEntryRepository->find($id);
        
        if (!$timeEntry) {
            return $this->json([
                'status' => 'error',
                'message' => 'Time entry not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Verify ownership
        if ($timeEntry->getUser()->getId()->toRfc4122() !== $this->getCurrentUser()->getId()->toRfc4122()) {
            return $this->json([
                'status' => 'error',
                'message' => 'You can only stop your own timer'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $this->timeTrackingService->stopTimer($timeEntry);
            
            return $this->json([
                'status' => 'success',
                'message' => 'Timer stopped',
                'data' => $this->serializeTimeEntry($timeEntry)
            ]);
        } catch (\RuntimeException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/task/{task}/entries', name: 'api_time_tracking_entries', requirements: ['task' => '%uuid_pattern%'], methods: ['GET'])]
    #[OA\Get(
        summary: 'Get time entries for a task',
        tags: ['Time Tracking']
    )]
    #[OA\Response(response: 200, description: 'List of time entries')]
    public function getTaskEntries(Task $task): JsonResponse
    {
        $entries = $this->timeTrackingService->getTimeEntriesForTask($task);
        
        $totalHours = $this->timeTrackingService->getTotalTimeForTask($task);
        $remainingHours = $this->timeTrackingService->getRemainingEstimate($task);
        $progress = $this->timeTrackingService->getProgressPercentage($task);

        $entriesData = array_map(
            fn(TimeEntry $entry) => $this->serializeTimeEntryWithUser($entry),
            $entries
        );

        return $this->json([
            'status' => 'success',
            'data' => [
                'entries' => $entriesData,
                'summary' => [
                    'total_hours' => $totalHours,
                    'estimated_hours' => $task->getEstimatedHours(),
                    'remaining_hours' => $remainingHours,
                    'progress_percentage' => $progress
                ]
            ]
        ]);
    }

    #[Route('/task/{task}/manual', name: 'api_time_tracking_manual', requirements: ['task' => '%uuid_pattern%'], methods: ['POST'])]
    #[OA\Post(
        summary: 'Add manual time entry',
        tags: ['Time Tracking']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['start_time', 'end_time'],
            properties: [
                new OA\Property(property: 'start_time', type: 'string', format: 'date-time'),
                new OA\Property(property: 'end_time', type: 'string', format: 'date-time'),
                new OA\Property(property: 'description', type: 'string', nullable: true)
            ]
        )
    )]
    #[OA\Response(response: 201, description: 'Manual entry created')]
    public function addManualEntry(Task $task, Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $data = json_decode($request->getContent(), true) ?? [];

        if (!isset($data['start_time']) || !isset($data['end_time'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'start_time and end_time are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $startTime = new \DateTime($data['start_time']);
            $endTime = new \DateTime($data['end_time']);
            $description = $data['description'] ?? null;

            $timeEntry = $this->timeTrackingService->addManualEntry(
                $task,
                $user,
                $startTime,
                $endTime,
                $description
            );

            return $this->json([
                'status' => 'success',
                'message' => 'Manual entry added',
                'data' => $this->serializeTimeEntry($timeEntry)
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/active', name: 'api_time_tracking_active', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get active timer for current user',
        tags: ['Time Tracking']
    )]
    #[OA\Response(response: 200, description: 'Active timer or null')]
    public function getActiveTimer(): JsonResponse
    {
        $user = $this->getCurrentUser();
        $activeEntry = $this->timeTrackingService->getActiveTimerForUser($user);

        if ($activeEntry === null) {
            return $this->json([
                'status' => 'success',
                'data' => null
            ]);
        }

        return $this->json([
            'status' => 'success',
            'data' => [
                'id' => $activeEntry->getId()->toRfc4122(),
                'task' => [
                    'id' => $activeEntry->getTask()->getId()->toRfc4122(),
                    'title' => $activeEntry->getTask()->getTitle()
                ],
                'start_time' => $activeEntry->getStartTime()->format('c'),
                'description' => $activeEntry->getDescription()
            ]
        ]);
    }

    /**
     * Serialize a time entry to array format
     * @return array<string, mixed>
     */
    private function serializeTimeEntry(TimeEntry $entry): array
    {
        return [
            'id' => $entry->getId()->toRfc4122(),
            'task_id' => $entry->getTask()->getId()->toRfc4122(),
            'start_time' => $entry->getStartTime()->format('c'),
            'end_time' => $entry->getEndTime()?->format('c'),
            'duration' => $entry->getDuration(),
            'duration_hours' => $entry->getDurationInHours(),
            'description' => $entry->getDescription(),
            'is_running' => $entry->isRunning()
        ];
    }

    /**
     * Serialize a time entry with user info to array format
     * @return array<string, mixed>
     */
    private function serializeTimeEntryWithUser(TimeEntry $entry): array
    {
        $data = $this->serializeTimeEntry($entry);
        $data['user'] = [
            'id' => $entry->getUser()->getId()->toRfc4122(),
            'name' => $entry->getUser()->getName()
        ];
        
        return $data;
    }
}
