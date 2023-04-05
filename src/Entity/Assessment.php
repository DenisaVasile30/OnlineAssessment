<?php

namespace App\Entity;

use App\Repository\AssessmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(type: 'string', nullable: true)]
    private string $status;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeLimit = 0;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $timeUnit = '';

    #[ORM\ManyToOne(targetEntity: Subject::class, inversedBy: 'assessments')]
    #[ORM\JoinColumn(nullable: false)]
    private $subject;

    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'assessments')]
    #[ORM\JoinColumn(nullable: false)]
    private Teacher $createdBy;

//    #[ORM\OneToMany(mappedBy: 'assessment', targetEntity: AssignedSubjects::class)]
//    private Collection $assignedSubjects;

    public function __construct()
    {
       // $this->assignedSubjects = new ArrayCollection();
    }

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

    /**
     * @return string
     */
    public function getRequirementsNo(): string
    {
        return $this->requirementsNo;
    }

    /**
     * @param string $requirementsNo
     */
    public function setRequirementsNo(string $requirementsNo): void
    {
        $this->requirementsNo = $requirementsNo;
    }

    /**
     * @return Collection<int, AssignedSubjects>
     */
    public function getAssignedSubjects(): Collection
    {
        return $this->assignedSubjects;
    }

    public function addAssignedSubject(AssignedSubjects $assignedSubject): self
    {
        if (!$this->assignedSubjects->contains($assignedSubject)) {
            $this->assignedSubjects->add($assignedSubject);
            $assignedSubject->setAssessment($this);
        }

        return $this;
    }

    public function removeAssignedSubject(AssignedSubjects $assignedSubject): self
    {
        if ($this->assignedSubjects->removeElement($assignedSubject)) {
            // set the owning side to null (unless already changed)
            if ($assignedSubject->getAssessment() === $this) {
                $assignedSubject->setAssessment(null);
            }
        }

        return $this;
    }

    public function getTimeLimit(): ?int
    {
        return $this->timeLimit;
    }

    public function setTimeLimit(?int $timeLimit): void
    {
        if ($timeLimit == null) {
            $this->timeLimit = 0;
        }
        $this->timeLimit = $timeLimit;
    }

    public function getTimeUnit(): ?string
    {
        return $this->timeUnit;
    }

    public function setTimeUnit(?string $timeUnit): void
    {
        $this->timeUnit = $timeUnit;
    }
}
