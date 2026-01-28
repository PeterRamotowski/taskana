<?php

namespace App\Controller\Api\Reports;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ProjectRepository;
use App\Service\ReportingService;
use App\Service\ReportExportService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reports')]
#[IsGranted('ROLE_USER')]
class ReportsController extends AbstractController
{
    public function __construct(
        private readonly ReportingService $reportingService,
        private readonly ReportExportService $exportService,
        private readonly UserRepository $userRepository,
        private readonly ProjectRepository $projectRepository
    ) {}

    /**
     * Get current user with proper type
     */
    private function getCurrentUser(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('User must be authenticated');
        }

        return $user;
    }

    /**
     * Parse and validate date range from request
     * @return array{0: \DateTime, 1: \DateTime}|JsonResponse
     */
    private function parseDateRange(Request $request): array|JsonResponse
    {
        $startDateStr = $request->query->get('start_date');
        $endDateStr = $request->query->get('end_date');

        if (!is_string($startDateStr) || !is_string($endDateStr)) {
            return $this->json([
                'status' => 'error',
                'message' => 'start_date and end_date are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $startDate = new \DateTime($startDateStr);
            $endDate = new \DateTime($endDateStr);
            return [$startDate, $endDate];
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid date format. Use Y-m-d'
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/user/{userId}', name: 'api_reports_user', requirements: ['userId' => '%uuid_pattern%'], methods: ['GET'])]
    #[OA\Get(
        summary: 'Generate user time tracking report',
        tags: ['Reports']
    )]
    #[OA\Parameter(
        name: 'start_date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'end_date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Response(response: 200, description: 'User report generated')]
    public function getUserReport(string $userId, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $dateRange = $this->parseDateRange($request);
        if ($dateRange instanceof JsonResponse) {
            return $dateRange;
        }
        [$startDate, $endDate] = $dateRange;

        $report = $this->reportingService->generateUserReport($user, $startDate, $endDate);

        return $this->json([
            'status' => 'success',
            'data' => $report
        ]);
    }

    #[Route('/user/{userId}/export', name: 'api_reports_user_export', requirements: ['userId' => '%uuid_pattern%'], methods: ['GET'])]
    #[OA\Get(
        summary: 'Export user report to CSV',
        tags: ['Reports']
    )]
    #[OA\Parameter(
        name: 'start_date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'end_date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'format',
        in: 'query',
        schema: new OA\Schema(type: 'string', enum: ['csv', 'json'], default: 'csv')
    )]
    #[OA\Response(response: 200, description: 'Report file download')]
    public function exportUserReport(string $userId, Request $request): Response
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $dateRange = $this->parseDateRange($request);
        if ($dateRange instanceof JsonResponse) {
            return $dateRange;
        }
        [$startDate, $endDate] = $dateRange;

        $report = $this->reportingService->generateUserReport($user, $startDate, $endDate);
        $format = $request->query->get('format', 'csv');

        if ($format === 'json') {
            return $this->exportService->exportToJSON($report);
        }

        return $this->exportService->exportToCSV($report, 'user');
    }

    #[Route('/team', name: 'api_reports_team', methods: ['GET'])]
    #[OA\Get(
        summary: 'Generate team-wide report',
        tags: ['Reports']
    )]
    #[OA\Parameter(
        name: 'start_date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'end_date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'project_id',
        in: 'query',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Response(response: 200, description: 'Team report generated')]
    public function getTeamReport(Request $request): JsonResponse
    {
        $dateRange = $this->parseDateRange($request);
        if ($dateRange instanceof JsonResponse) {
            return $dateRange;
        }
        [$startDate, $endDate] = $dateRange;

        $project = null;
        $projectId = $request->query->get('project_id');

        if ($projectId) {
            $project = $this->projectRepository->find($projectId);
            if (!$project) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Project not found'
                ], Response::HTTP_NOT_FOUND);
            }
        }

        $report = $this->reportingService->generateTeamReport($startDate, $endDate, $project);

        return $this->json([
            'status' => 'success',
            'data' => $report
        ]);
    }

    #[Route('/team/export', name: 'api_reports_team_export', methods: ['GET'])]
    #[OA\Get(
        summary: 'Export team report',
        tags: ['Reports']
    )]
    #[OA\Parameter(
        name: 'start_date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'end_date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'project_id',
        in: 'query',
        schema: new OA\Schema(type: 'string', format: 'uuid')
    )]
    #[OA\Parameter(
        name: 'format',
        in: 'query',
        schema: new OA\Schema(type: 'string', enum: ['csv', 'json'], default: 'csv')
    )]
    #[OA\Response(response: 200, description: 'Report file download')]
    public function exportTeamReport(Request $request): Response
    {
        $dateRange = $this->parseDateRange($request);
        if ($dateRange instanceof JsonResponse) {
            return $dateRange;
        }
        [$startDate, $endDate] = $dateRange;

        $project = null;
        $projectId = $request->query->get('project_id');

        if ($projectId) {
            $project = $this->projectRepository->find($projectId);
        }

        $report = $this->reportingService->generateTeamReport($startDate, $endDate, $project);
        $format = $request->query->get('format', 'csv');

        if ($format === 'json') {
            return $this->exportService->exportToJSON($report);
        }

        return $this->exportService->exportToCSV($report, 'team');
    }

    #[Route('/project/{projectId}', name: 'api_reports_project', requirements: ['projectId' => '%uuid_pattern%'], methods: ['GET'])]
    #[OA\Get(
        summary: 'Generate project report',
        tags: ['Reports']
    )]
    #[OA\Parameter(
        name: 'start_date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'end_date',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Response(response: 200, description: 'Project report generated')]
    public function getProjectReport(string $projectId, Request $request): JsonResponse
    {
        $project = $this->projectRepository->find($projectId);

        if (!$project) {
            return $this->json([
                'status' => 'error',
                'message' => 'Project not found'
            ], Response::HTTP_NOT_FOUND);
        }

        error_log('Project loaded: ' . $project->getId()->toRfc4122() . ' - ' . $project->getTitle());

        $dateRange = $this->parseDateRange($request);
        if ($dateRange instanceof JsonResponse) {
            return $dateRange;
        }
        [$startDate, $endDate] = $dateRange;

        $report = $this->reportingService->generateProjectReport($project, $startDate, $endDate);

        error_log('Project Report Summary: ' . json_encode($report['summary'] ?? []));
        error_log('Project Report By Task Count: ' . count($report['by_task'] ?? []));
        error_log('Project Report By User Count: ' . count($report['by_user'] ?? []));

        return $this->json([
            'status' => 'success',
            'data' => $report
        ]);
    }

    #[Route('/project/{projectId}/export', name: 'api_reports_project_export', requirements: ['projectId' => '%uuid_pattern%'], methods: ['GET'])]
    #[OA\Get(
        summary: 'Export project report',
        tags: ['Reports']
    )]
    #[OA\Response(response: 200, description: 'Report file download')]
    public function exportProjectReport(string $projectId, Request $request): Response
    {
        $project = $this->projectRepository->find($projectId);

        if (!$project) {
            return $this->json([
                'status' => 'error',
                'message' => 'Project not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $dateRange = $this->parseDateRange($request);
        if ($dateRange instanceof JsonResponse) {
            return $dateRange;
        }
        [$startDate, $endDate] = $dateRange;

        $report = $this->reportingService->generateProjectReport($project, $startDate, $endDate);
        $format = $request->query->get('format', 'csv');

        if ($format === 'json') {
            return $this->exportService->exportToJSON($report);
        }

        return $this->exportService->exportToCSV($report, 'project');
    }

    #[Route('/user/{userId}/comparison', name: 'api_reports_comparison', requirements: ['userId' => '%uuid_pattern%'], methods: ['GET'])]
    #[OA\Get(
        summary: 'Generate comparison report between two periods',
        tags: ['Reports']
    )]
    #[OA\Parameter(
        name: 'period1_start',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'period1_end',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'period2_start',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Parameter(
        name: 'period2_end',
        in: 'query',
        required: true,
        schema: new OA\Schema(type: 'string', format: 'date')
    )]
    #[OA\Response(response: 200, description: 'Comparison report generated')]
    public function getComparisonReport(string $userId, Request $request): JsonResponse
    {
        $user = $this->userRepository->find($userId);

        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'User not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $period1StartStr = $request->query->get('period1_start');
        $period1EndStr = $request->query->get('period1_end');
        $period2StartStr = $request->query->get('period2_start');
        $period2EndStr = $request->query->get('period2_end');

        if (
            !is_string($period1StartStr) || !is_string($period1EndStr) ||
            !is_string($period2StartStr) || !is_string($period2EndStr)
        ) {
            return $this->json([
                'status' => 'error',
                'message' => 'All period dates are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $period1Start = new \DateTime($period1StartStr);
            $period1End = new \DateTime($period1EndStr);
            $period2Start = new \DateTime($period2StartStr);
            $period2End = new \DateTime($period2EndStr);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Invalid date format'
            ], Response::HTTP_BAD_REQUEST);
        }

        $report = $this->reportingService->generateComparisonReport(
            $user,
            $period1Start,
            $period1End,
            $period2Start,
            $period2End
        );

        return $this->json([
            'status' => 'success',
            'data' => $report
        ]);
    }

    #[Route('/dashboard', name: 'api_reports_dashboard', methods: ['GET'])]
    #[OA\Get(
        summary: 'Get dashboard analytics for current user',
        tags: ['Reports']
    )]
    #[OA\Parameter(
        name: 'period',
        in: 'query',
        schema: new OA\Schema(type: 'string', enum: ['week', 'month', 'quarter', 'year'], default: 'week')
    )]
    #[OA\Response(response: 200, description: 'Dashboard data')]
    public function getDashboard(Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $period = $request->query->get('period', 'week');

        $endDate = new \DateTime();
        $startDate = match ($period) {
            'month' => new \DateTime('-1 month'),
            'quarter' => new \DateTime('-3 months'),
            'year' => new \DateTime('-1 year'),
            default => new \DateTime('-1 week')
        };

        $report = $this->reportingService->generateUserReport($user, $startDate, $endDate);

        return $this->json([
            'status' => 'success',
            'data' => [
                'period' => $period,
                'summary' => $report['summary'],
                'by_date' => $report['by_date'],
                'by_day_of_week' => $report['by_day_of_week'],
                'top_tasks' => $report['top_tasks'],
                'by_priority' => $report['by_priority']
            ]
        ]);
    }
}
