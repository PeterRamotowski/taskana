<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping\Column;
use Gedmo\Mapping\Annotation as Gedmo;

trait EntityTimestampableTrait
{
    #[Gedmo\Timestampable(on: 'create')]
    #[Column(type: 'datetime', nullable: true)]
    private ?\DateTime $createdDate;

    #[Gedmo\Timestampable(on: 'update')]
    #[Column(type: 'datetime', nullable: true)]
    private ?\DateTime $updatedDate;

    public function getCreatedDate(): ?\DateTime
    {
        return $this->createdDate;
    }

    public function getUpdatedDate(): ?\DateTime
    {
        return $this->updatedDate;
    }
}