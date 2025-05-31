<?php

namespace App\DataFixtures;

use App\Data\TaskAddData;
use App\Entity\Enum\TaskPriority;
use App\Entity\Project;
use App\Entity\User;
use App\Manager\AppEntityManager;
use App\Manager\TaskManager;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\WordsProvider;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly AppEntityManager $aem,
        private readonly TaskManager $taskManager,
        private readonly ProjectRepository $projectRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $words = WordsProvider::$words;
        for ($i = 1; $i <= 50; $i++) {
            $taskAddData = new TaskAddData();
            $taskAddData->title = "Task $i";

            $wordCount = rand(5, 20);
            shuffle($words);
            $taskAddData->description = ucfirst(implode(' ', array_slice($words, 0, $wordCount))).'.';

            $taskAddData->priority = [TaskPriority::LOW, TaskPriority::MEDIUM, TaskPriority::HIGH][$i % 3];

            // Ensure worker and creator are not the same
            $workerIndex = rand(1, 3);
            do {
                $creatorIndex = rand(1, 3);
            } while ($creatorIndex === $workerIndex);

            $taskAddData->project = $this->getReference('project_'.rand(1, 9), Project::class)->getId();
            $taskAddData->worker = $this->getReference('user_'.$workerIndex, User::class)->getId();

            $task = $this->taskManager->createFromData($taskAddData, false);
            $task->setCreator($this->getReference('user_'.$creatorIndex, User::class));
            $this->aem->persist($task);

            $this->addReference("task_$i", $task);
        }

        $this->aem->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixtures::class,
            UserFixtures::class,
        ];
    }
}
