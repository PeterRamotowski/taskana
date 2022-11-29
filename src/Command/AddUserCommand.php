<?php

namespace App\Command;

use App\Data\UserAddData;
use App\Manager\UserManager;
use App\Service\ValidatorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:add'
)]
class AddUserCommand extends Command
{
    public function __construct(
        private readonly UserManager $userManager,
        private readonly ValidatorService $validatorService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('email', null, InputOption::VALUE_REQUIRED, 'User email');
        $this->addOption('password', null, InputOption::VALUE_REQUIRED, 'User password');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $email */
        $email = $input->getOption('email');
        /** @var string $password */
        $password = $input->getOption('password');

        $data = new UserAddData();
        $data->email = $email;
        $data->password = $password;
        $data->roles = ['ROLE_ADMIN'];

        $this->validatorService->validate($data);
  
        $this->userManager->createFromData($data);

        $io = new SymfonyStyle($input, $output);
        $io->success('Added user with email: '.$email);

        return 0;
    }
}
