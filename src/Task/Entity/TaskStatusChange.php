<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Identity\Entity\User;
use App\Shift\Entity\Shift;
use App\Task\Enum\TaskStatus;
use App\Task\Repository\TaskStatusChangeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskStatusChangeRepository::class)]
#[ORM\Table(options: ['comment' => 'История переходов задачи'])]
#[ORM\Index(name: 'task_status_change_history', columns: ['task_id', 'created_at'])]
class TaskStatusChange
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //task_status_change_status_check, task_status_change_return_comment_check
    #[ORM\Column(type: 'string', length: 20, nullable: true, enumType: TaskStatus::class,
        options: ['comment' => 'Статус до перехода'])]
    private ?TaskStatus $fromStatus = null;

    #[ORM\Column(type: 'string', length: 20, enumType: TaskStatus::class,
        options: ['comment' => 'Статус после перехода'])]
    private TaskStatus $toStatus;

    #[ORM\Column(type: 'text', nullable: true,
        options: ['comment' => 'Комментарий к переходу. Обязателен при возврате на доработку'])]
    private ?string $comment = null;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE', options: ['comment' => 'ВК Задача'])]
    private Task $task;

    #[ORM\ManyToOne(targetEntity: Shift::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT',
        options: ['comment' => 'ВК Смена на момент перехода. Снимок: показывает, кто держал задачу'])]
    private ?Shift $shift = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT',
        options: ['comment' => 'ВК Пользователь, сделавший переход. Пусто — переход выполнила система'])]
    private ?User $changedBy = null;

    #[ORM\Column(type: 'datetimetz_immutable', options: ['comment' => 'Дата создания'])]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Task $task,
        TaskStatus $toStatus,
        ?TaskStatus $fromStatus,
        ?User $changedBy = null,
        ?string $comment = null,
        ?Shift $shift = null,
    ) {
        $this->task = $task;
        $this->toStatus = $toStatus;
        $this->fromStatus = $fromStatus;
        $this->changedBy = $changedBy;
        $this->comment = $comment;
        $this->shift = $shift;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFromStatus(): ?TaskStatus
    {
        return $this->fromStatus;
    }

    public function getToStatus(): TaskStatus
    {
        return $this->toStatus;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function getShift(): ?Shift
    {
        return $this->shift;
    }

    public function getChangedBy(): ?User
    {
        return $this->changedBy;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
