<?php

namespace App\DataFixtures;

use App\Data\CommentAddData;
use App\Entity\Task;
use App\Entity\User;
use App\Manager\AppEntityManager;
use App\Manager\CommentManager;
use App\DataFixtures\WordsProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommentFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly AppEntityManager $aem,
        private readonly CommentManager $commentManager,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $words = WordsProvider::$words;
        for ($taskIndex = 1; $taskIndex <= 50; $taskIndex++) {
            $task = $this->getReference('task_' . $taskIndex, Task::class);

            $commentsCount = rand(2, 10);
            for ($j = 1; $j <= $commentsCount; $j++) {
                $commentAddData = new CommentAddData();

                $wordCount = rand(5, 30);
                shuffle($words);
                $commentAddData->description = ucfirst(implode(' ', array_slice($words, 0, $wordCount))).'.';

                $commentAddData->task = $task->getId();
                $comment = $this->commentManager->createFromData($commentAddData, false);
                $author = $this->getReference('user_'.rand(1, 3), User::class);
                $comment->setAuthor($author);
                $this->aem->persist($comment);
            }
        }

        $this->aem->flush();
    }

    public function getDependencies(): array
    {
        return [
            TaskFixtures::class,
            UserFixtures::class,
        ];
    }
}
