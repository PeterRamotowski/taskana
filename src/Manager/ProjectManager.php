<?php

namespace App\Manager;

use App\Data\ProjectAddData;
use App\Data\ProjectUpdateData;
use App\Entity\Project;
use App\Entity\Factory\ProjectFactory;
use App\Manager\AppEntityManager;

class ProjectManager
{
    public function __construct(
        private readonly AppEntityManager $aem,
    ) {
    }

    public function createFromData(
        ProjectAddData $data,
        bool $save = true,
    ): Project
    {
        $project = ProjectFactory::create();
        $this->buildFromData($project, $data);

        if ($save) {
            $this->aem->save($project);
        }

        return $project;
    }

    public function updateFromData(Project $project, ProjectUpdateData $data): Project
    {
        $this->buildFromData($project, $data);
        $this->aem->flush();
        return $project;
    }

    private function buildFromData(Project $project, ProjectAddData|ProjectUpdateData $data): void
    {
        $project
            ->setTitle($data->title)
            ->setDescription($data->description);
    }

}
