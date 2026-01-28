<?php

namespace App\Entity;

use App\Entity\Comment;
use App\Entity\Task;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Loggable;
use Gedmo\Mapping\Annotation\Versioned;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: UserRepository::class)]
#[Table(name: 'users')]
#[Loggable]
class User implements EntityInterface, UserInterface, PasswordAuthenticatedUserInterface, \JsonSerializable
{
    #[Id]
    #[Column(type: 'uuid', unique: true)]
    private string $id;

    #[Column(type: 'string', nullable: false, unique: true)]
    #[Versioned]
    private string $email;

    #[Column(type: 'string')]
    #[Versioned]
    private string $password;

    #[Column(type: 'string', nullable: true)]
    #[Versioned]
    private ?string $name = null;

    /**
     * @var array|string[]
     */
    #[Column(type: 'json', nullable: false)]
    #[Versioned]
    private array $roles;

    #[Column(type: 'boolean', nullable: false)]
    #[Versioned]
    private bool $active;

    #[Column(type: 'datetime', nullable: true)]
    #[Versioned]
    private ?\DateTime $lastLoginDate = null;

    /**
     * @var Collection|Task[]
     */
    #[OneToMany(targetEntity: Task::class, mappedBy: 'worker')]
    private Collection $assignedtasks;

    /**
     * @var Collection|Task[]
     */
    #[OneToMany(targetEntity: Task::class, mappedBy: 'creator')]
    private Collection $createdtasks;

    /**
     * @var Collection|Comment[]
     */
    #[OneToMany(targetEntity: Comment::class, mappedBy: 'author')]
    private Collection $comments;

    public function __construct() {
        $this->assignedtasks = new ArrayCollection();
        $this->createdtasks = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return Uuid::fromString($this->id);
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->name ?: $this->email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getName(): string
    {
        return $this->name ?: $this->email;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }
    /**
     * @param array<string> $roles
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(): self
    {
        $this->active = true;
        return $this;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getLastLoginDate(): ?\DateTime
    {
        return $this->lastLoginDate;
    }

    public function setLastLoginDate(?\DateTime $date = new \DateTime()): self
    {
        $this->lastLoginDate = $date;
        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getAssignedTasks(): Collection
    {
      return $this->assignedtasks;
    }

    /**
     * @param Task $task 
     */
    public function addAssignedTask(Task $task): self
    {
      $this->assignedtasks[] = $task;
      return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getCreatedTasks(): Collection
    {
      return $this->createdtasks;
    }

    /**
     * @param Task $task 
     */
    public function addCreatedTask(Task $task): self
    {
      $this->createdtasks[] = $task;
      return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
      return $this->comments;
    }

    /**
     * @param Comment $comment 
     */
    public function addComment(Comment $comment): self
    {
      $this->comments[] = $comment;
      return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function jsonSerialize(): mixed
    {
        return [
          'id' => $this->getId()->toRfc4122(),
          'name' => $this->getName(),
          'username' => $this->getUsername(),
          'roles' => $this->getRoles(),
        ];
    }
}
