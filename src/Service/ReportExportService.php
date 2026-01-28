<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Export report data to CSV format
     * @param array<string, mixed> $reportData
     */
    public function exportToCSV(array $reportData, string $reportType): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->setCallback(function () use ($reportData, $reportType) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            match ($reportType) {
                'user' => $this->exportUserReportToCSV($handle, $reportData),
                'team' => $this->exportTeamReportToCSV($handle, $reportData),
                'project' => $this->exportProjectReportToCSV($handle, $reportData),
                default => $this->exportGenericToCSV($handle, $reportData)
            };

            fclose($handle);
        });

        $filename = sprintf('report_%s_%s.csv', $reportType, date('Y-m-d_His'));

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));

        return $response;
    }

    /**
     * Export user report to CSV
     * @param resource $handle
     * @param array<string, mixed> $reportData
     */
    private function exportUserReportToCSV(mixed $handle, array $reportData): void
    {
        if (!is_resource($handle)) {
            return;
        }

        fputcsv($handle, ['User Report']);
        fputcsv($handle, ['User:', (string)($reportData['user']['name'] ?? 'Unknown')]);
        fputcsv($handle, ['Period:', ($reportData['period']['start'] ?? '') . ' to ' . ($reportData['period']['end'] ?? '')]);
        fputcsv($handle, ['Generated:', date('Y-m-d H:i:s')]);
        fputcsv($handle, []);

        fputcsv($handle, ['Summary']);
        fputcsv($handle, ['Total Hours', (string)($reportData['summary']['total_hours'] ?? 0)]);
        fputcsv($handle, ['Total Entries', (string)($reportData['summary']['total_entries'] ?? 0)]);
        fputcsv($handle, ['Average Hours/Day', (string)($reportData['summary']['average_hours_per_day'] ?? 0)]);
        fputcsv($handle, []);

        fputcsv($handle, ['Time by Task']);
        fputcsv($handle, ['Task ID', 'Task Title', 'Status', 'Priority', 'Estimated Hours', 'Actual Hours', 'Entries']);

        if (isset($reportData['by_task']) && is_array($reportData['by_task'])) {
            foreach ($reportData['by_task'] as $task) {
                if (!is_array($task)) continue;
                fputcsv($handle, [
                    (string)($task['task_id'] ?? ''),
                    (string)($task['task_title'] ?? ''),
                    (string)($task['task_status'] ?? ''),
                    (string)($task['task_priority'] ?? ''),
                    (string)($task['estimated_hours'] ?? 'N/A'),
                    (string)($task['total_hours'] ?? 0),
                    (string)($task['entries_count'] ?? 0)
                ]);
            }
        }
        fputcsv($handle, []);

        if (isset($reportData['by_project']) && is_array($reportData['by_project']) && !empty($reportData['by_project'])) {
            fputcsv($handle, ['Time by Project']);
            fputcsv($handle, ['Project ID', 'Project Title', 'Total Hours', 'Tasks Count', 'Entries']);

            foreach ($reportData['by_project'] as $project) {
                if (!is_array($project)) continue;
                fputcsv($handle, [
                    (string)($project['project_id'] ?? ''),
                    (string)($project['project_title'] ?? ''),
                    (string)($project['total_hours'] ?? 0),
                    (string)($project['tasks_count'] ?? 0),
                    (string)($project['entries_count'] ?? 0)
                ]);
            }
            fputcsv($handle, []);
        }

        fputcsv($handle, ['Time by Date']);
        fputcsv($handle, ['Date', 'Hours']);

        if (isset($reportData['by_date']) && is_array($reportData['by_date'])) {
            foreach ($reportData['by_date'] as $dateData) {
                if (!is_array($dateData)) continue;
                fputcsv($handle, [
                    (string)($dateData['date'] ?? ''),
                    (string)($dateData['hours'] ?? 0)
                ]);
            }
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Time by Day of Week']);
        fputcsv($handle, ['Day', 'Hours']);

        if (isset($reportData['by_day_of_week']) && is_array($reportData['by_day_of_week'])) {
            foreach ($reportData['by_day_of_week'] as $day => $hours) {
                fputcsv($handle, [(string)$day, (string)round((float)$hours, 2)]);
            }
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Time by Priority']);
        fputcsv($handle, ['Priority', 'Hours']);

        if (isset($reportData['by_priority']) && is_array($reportData['by_priority'])) {
            foreach ($reportData['by_priority'] as $priority => $hours) {
                fputcsv($handle, [(string)$priority, (string)round((float)$hours, 2)]);
            }
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Detailed Time Entries']);
        fputcsv($handle, ['Entry ID', 'Task', 'Project', 'Start Time', 'End Time', 'Duration (hours)', 'Description']);

        if (isset($reportData['entries']) && is_array($reportData['entries'])) {
            foreach ($reportData['entries'] as $entry) {
                if (!is_array($entry)) continue;
                fputcsv($handle, [
                    (string)($entry['id'] ?? ''),
                    (string)($entry['task_title'] ?? ''),
                    (string)($entry['project_title'] ?? 'N/A'),
                    (string)($entry['start_time'] ?? ''),
                    (string)($entry['end_time'] ?? 'N/A'),
                    (string)($entry['duration_hours'] ?? 0),
                    (string)($entry['description'] ?? '')
                ]);
            }
        }
    }

    /**
     * Export team report to CSV
     * @param resource $handle
     * @param array<string, mixed> $reportData
     */
    private function exportTeamReportToCSV(mixed $handle, array $reportData): void
    {
        if (!is_resource($handle)) {
            return;
        }

        fputcsv($handle, ['Team Report']);
        fputcsv($handle, ['Period:', ($reportData['period']['start'] ?? '') . ' to ' . ($reportData['period']['end'] ?? '')]);

        if (isset($reportData['project']) && is_array($reportData['project'])) {
            fputcsv($handle, ['Project:', (string)($reportData['project']['title'] ?? '')]);
        }

        fputcsv($handle, ['Generated:', date('Y-m-d H:i:s')]);
        fputcsv($handle, []);

        fputcsv($handle, ['Summary']);
        fputcsv($handle, ['Total Hours', (string)($reportData['summary']['total_hours'] ?? 0)]);
        fputcsv($handle, ['Total Users', (string)($reportData['summary']['total_users'] ?? 0)]);
        fputcsv($handle, ['Total Tasks', (string)($reportData['summary']['total_tasks'] ?? 0)]);
        fputcsv($handle, ['Total Entries', (string)($reportData['summary']['total_entries'] ?? 0)]);
        fputcsv($handle, ['Average Hours/User', (string)($reportData['summary']['average_hours_per_user'] ?? 0)]);
        fputcsv($handle, []);

        fputcsv($handle, ['Time by User']);
        fputcsv($handle, ['User ID', 'User Name', 'Total Hours', 'Entries', 'Tasks']);

        if (isset($reportData['by_user']) && is_array($reportData['by_user'])) {
            foreach ($reportData['by_user'] as $user) {
                if (!is_array($user)) continue;
                fputcsv($handle, [
                    (string)($user['user_id'] ?? ''),
                    (string)($user['user_name'] ?? ''),
                    (string)round((float)($user['total_hours'] ?? 0), 2),
                    (string)($user['entries_count'] ?? 0),
                    (string)($user['tasks_count'] ?? 0)
                ]);
            }
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Time by Task']);
        fputcsv($handle, ['Task ID', 'Task Title', 'Total Hours', 'Contributors']);

        if (isset($reportData['by_task']) && is_array($reportData['by_task'])) {
            foreach ($reportData['by_task'] as $task) {
                if (!is_array($task)) continue;
                fputcsv($handle, [
                    (string)($task['task_id'] ?? ''),
                    (string)($task['task_title'] ?? ''),
                    (string)round((float)($task['total_hours'] ?? 0), 2),
                    (string)($task['contributors_count'] ?? 0)
                ]);
            }
        }
        fputcsv($handle, []);

        if (isset($reportData['productivity_trends']) && is_array($reportData['productivity_trends']) && !empty($reportData['productivity_trends'])) {
            fputcsv($handle, ['Productivity Trends']);
            fputcsv($handle, ['Date', 'Day of Week', 'Hours']);

            foreach ($reportData['productivity_trends'] as $trend) {
                if (!is_array($trend)) continue;
                fputcsv($handle, [
                    (string)($trend['date'] ?? ''),
                    (string)($trend['day_of_week'] ?? ''),
                    (string)($trend['hours'] ?? 0)
                ]);
            }
        }
    }

    /**
     * Export project report to CSV
     * @param resource $handle
     * @param array<string, mixed> $reportData
     */
    private function exportProjectReportToCSV(mixed $handle, array $reportData): void
    {
        if (!is_resource($handle)) {
            return;
        }

        fputcsv($handle, ['Project Report']);
        fputcsv($handle, ['Project:', (string)($reportData['project']['title'] ?? 'Unknown')]);
        fputcsv($handle, ['Period:', ($reportData['period']['start'] ?? '') . ' to ' . ($reportData['period']['end'] ?? '')]);
        fputcsv($handle, ['Generated:', date('Y-m-d H:i:s')]);
        fputcsv($handle, []);

        fputcsv($handle, ['Summary']);
        fputcsv($handle, ['Total Tasks', (string)($reportData['summary']['total_tasks'] ?? 0)]);
        fputcsv($handle, ['Completed Tasks', (string)($reportData['summary']['completed_tasks'] ?? 0)]);
        fputcsv($handle, ['In Progress', (string)($reportData['summary']['in_progress_tasks'] ?? 0)]);
        fputcsv($handle, ['Pending', (string)($reportData['summary']['pending_tasks'] ?? 0)]);
        fputcsv($handle, ['Estimated Hours', (string)($reportData['summary']['estimated_hours'] ?? 0)]);
        fputcsv($handle, ['Actual Hours', (string)($reportData['summary']['total_hours'] ?? 0)]);
        fputcsv($handle, ['Remaining Hours', (string)($reportData['summary']['remaining_hours'] ?? 0)]);
        fputcsv($handle, ['Completion %', (string)($reportData['summary']['completion_percentage'] ?? 0)]);
        fputcsv($handle, []);

        fputcsv($handle, ['Time by User']);
        fputcsv($handle, ['User ID', 'User Name', 'Total Hours', 'Tasks Worked On']);

        if (isset($reportData['by_user']) && is_array($reportData['by_user'])) {
            foreach ($reportData['by_user'] as $user) {
                if (!is_array($user)) continue;
                fputcsv($handle, [
                    (string)($user['user_id'] ?? ''),
                    (string)($user['user_name'] ?? ''),
                    (string)round((float)($user['total_hours'] ?? 0), 2),
                    (string)($user['tasks_count'] ?? 0)
                ]);
            }
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Task Details']);
        fputcsv($handle, ['Task ID', 'Task Title', 'Status', 'Priority', 'Assigned To', 'Estimated Hours', 'Actual Hours', 'Variance']);

        if (isset($reportData['by_task']) && is_array($reportData['by_task'])) {
            foreach ($reportData['by_task'] as $task) {
                if (!is_array($task)) continue;
                fputcsv($handle, [
                    (string)($task['task_id'] ?? ''),
                    (string)($task['task_title'] ?? ''),
                    (string)($task['status'] ?? ''),
                    (string)($task['priority'] ?? ''),
                    (string)($task['assigned_to'] ?? ''),
                    (string)($task['estimated_hours'] ?? 0),
                    (string)($task['actual_hours'] ?? 0),
                    (string)($task['variance'] ?? 0)
                ]);
            }
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Time by Status']);
        fputcsv($handle, ['Status', 'Task Count', 'Total Hours']);

        if (isset($reportData['by_status']) && is_array($reportData['by_status'])) {
            foreach ($reportData['by_status'] as $status => $data) {
                if (!is_array($data)) continue;
                fputcsv($handle, [
                    (string)$status,
                    (string)($data['count'] ?? 0),
                    (string)round((float)($data['hours'] ?? 0), 2)
                ]);
            }
        }
    }

    /**
     * Generic CSV export fallback
     * @param resource $handle
     * @param array<string, mixed> $reportData
     */
    private function exportGenericToCSV(mixed $handle, array $reportData): void
    {
        if (!is_resource($handle)) {
            return;
        }

        fputcsv($handle, ['Report Data']);
        fputcsv($handle, ['Generated:', date('Y-m-d H:i:s')]);
        fputcsv($handle, []);

        $flatten = function ($data, $prefix = '') use (&$flatten, $handle) {
            foreach ($data as $key => $value) {
                $fullKey = $prefix ? $prefix . '.' . $key : $key;

                if (is_array($value)) {
                    if (empty($value) || !isset($value[0])) {
                        $flatten($value, $fullKey);
                    } else {
                        fputcsv($handle, [$fullKey]);
                        foreach ($value as $item) {
                            if (is_array($item)) {
                                fputcsv($handle, array_values($item));
                            } else {
                                fputcsv($handle, [$item]);
                            }
                        }
                        fputcsv($handle, []);
                    }
                } else {
                    fputcsv($handle, [$fullKey, $value]);
                }
            }
        };

        $flatten($reportData);
    }

    /**
     * Export report data to JSON format
     * @param array<string, mixed> $reportData
     */
    public function exportToJSON(array $reportData): Response
    {
        $json = json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new \RuntimeException('Failed to encode report data to JSON');
        }

        $response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Content-Disposition', sprintf(
            'attachment; filename="report_%s.json"',
            date('Y-m-d_His')
        ));

        return $response;
    }
}
