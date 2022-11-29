<?php

namespace App\Entity\Factory;

use App\Entity\Comment;
use Symfony\Component\Uid\Uuid;

class CommentFactory
{
  private Comment $comment;

  private function __construct()
  {
    $this->comment = (new Comment())
      ->setId(Uuid::v4());
  }

  public static function create(): Comment
  {
    return (new self())->comment;
  }
}
