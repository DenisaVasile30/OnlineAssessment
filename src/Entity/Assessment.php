<?php

namespace App\Entity;

use App\Repository\AssessmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssessmentRepository::class)]
#[ORM\Table(name: '`assessments`')]
class Assessment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false, length: 500)]
    private string $description;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'json', nullable: false)]
    private array $assigneeGroup = [];

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $startAt;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $endAt;

    #[ORM\Column(type: 'json', nullable: false)]
    private array $subjectList;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $status;

    #[ORM\ManyToOne(targetEntity: Subject::class, inversedBy: 'assessments')]
    #[ORM\JoinColumn(nullable: false)]
    private $subject;

    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'assessments')]
    #[ORM\JoinColumn(nullable: false)]
    private Teacher $createdBy;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAssigneeGroup(): array
    {
        return $this->assigneeGroup;
    }

    public function setAssigneeGroup(array $assigneeGroup): self
    {
        $this->assigneeGroup = $assigneeGroup;

        return $this;
    }

    public function getStartAt(): ?\DateTime
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTime $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTime
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTime $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getCreatedBy(): Teacher
    {
        return $this->createdBy;
    }

    public function setCreatedBy(Teacher $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getSubjectList(): array
    {
        return $this->subjectList;
    }

    public function setSubjectList(array $subjectList): void
    {
        $this->subjectList = $subjectList;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
