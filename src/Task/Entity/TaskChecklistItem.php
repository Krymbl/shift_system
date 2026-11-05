<?php

declare(strict_types=1);

namespace App\Task\Entity;

use App\Common\Traits\TimestampableTrait;
use App\Identity\Entity\User;
use App\Task\Repository\TaskChecklistItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskChecklistItemRepository::class)]
#[ORM\Table(options: ['comment' => 'Пункты чек-листа задачи'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Index(name: 'task_checklist_item_by_task_position', columns: ['task_id', 'position'])]
class TaskChecklistItem
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private ?int $id = null;

    //task_checklist_item_position_check
    #[ORM\Column(type: 'integer', options: ['comment' => 'Позиция в списке'])]
    private int $position;

    #[ORM\Column(type: 'string', length: 255, options: ['comment' => 'Текст пункта'])]
    private string $text;

    #[ORM\ManyToOne(targetEntity: Task::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE',
        options: ['comment' => 'ВК Задача. Пункты живут только вместе с задачей'])]
    private Task $task;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'RESTRICT',
        options: ['comment' => 'ВК Пользователь, отметивший пункт'])]
    private ?User $checkedBy = null;

    //task_checklist_item_checked_pair_check
    #[ORM\Column(type: 'datetimetz_immutable', nullable: true, options: ['comment' => 'Момент отметки'])]
    private ?\DateTimeImmutable $checkedAt = null;

    public function __construct(
        int $position,
        string $text,
        Task $task,
    ) {
        $this->position = $position;
        $this->text = $text;
        $this->task = $task;
    }

    public function check(User $checkedBy): void
    {
        $this->checkedBy = $checkedBy;
        $this->checkedAt = new \DateTimeImmutable();
    }

    public function uncheck(): void
    {
        $this->checkedBy = null;
        $this->checkedAt = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function isChecked(): bool
    {
        return $this->checkedAt !== null;
    }

    public function getCheckedAt(): ?\DateTimeImmutable
    {
        return $this->checkedAt;
    }

    public function getCheckedBy(): ?User
    {
        return $this->checkedBy;
    }
}
