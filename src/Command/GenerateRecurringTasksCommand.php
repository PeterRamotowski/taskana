<?php

namespace App\Command;

use App\Service\RecurringTaskService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recurring-tasks:generate',
    description: 'Generate recurring task instances that are due'
)]
class GenerateRecurringTasksCommand extends Command
{
    public function __construct(
        private RecurringTaskService $recurringTaskService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'days-ahead',
                'd',
                InputOption::VALUE_OPTIONAL,
                'How many days ahead to generate tasks',
                7
            )
            ->setHelp(
                <<<'HELP'
This command generates recurring task instances for the upcoming period.
It should be run daily via cron job.

Example cron entry (daily at 1 AM):
0 1 * * * cd /var/www/html && php bin/console app:recurring-tasks:generate

Usage:
  php bin/console app:recurring-tasks:generate
  php bin/console app:recurring-tasks:generate --days-ahead=14
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $daysAhead = (int) $input->getOption('days-ahead');
        $upToDate = new \DateTime("+{$daysAhead} days");

        $io->title('Generating Recurring Task Instances');
        $io->info(sprintf('Generating tasks up to: %s', $upToDate->format('Y-m-d')));

        try {
            $tasksCreated = $this->recurringTaskService->generateDueRecurringTasks($upToDate);

            if ($tasksCreated > 0) {
                $io->success(sprintf('Successfully generated %d recurring task instance(s)', $tasksCreated));
            } else {
                $io->info('No recurring tasks needed to be generated');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to generate recurring tasks: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
