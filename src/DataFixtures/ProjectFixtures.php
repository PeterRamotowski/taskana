<?php

namespace App\DataFixtures;

use App\Manager\AppEntityManager;
use App\Manager\ProjectManager;
use App\Data\ProjectAddData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\WordsProvider;

class ProjectFixtures extends Fixture
{
    public function __construct(
        private readonly AppEntityManager $aem,
        private readonly ProjectManager $projectManager,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $words = WordsProvider::$words;
        for ($i = 1; $i <= 9; $i++) {
            $projectAddData = new ProjectAddData();
            $projectAddData->title = "Project $i";

            $wordCount = rand(5, 20);
            shuffle($words);
            $projectAddData->description = ucfirst(implode(' ', array_slice($words, 0, $wordCount))).'.';

            $project = $this->projectManager->createFromData($projectAddData);
            $this->aem->persist($project);

            $this->addReference("project_$i", $project);
        }
        $this->aem->flush();
    }
}
