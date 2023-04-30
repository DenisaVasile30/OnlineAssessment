<?php

namespace App\Entity;

use App\Repository\CreatedQuizRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CreatedQuizRepository::class)]
class CreatedQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 1000)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $timeLimit = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $timeUnit = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $practiceQuiz = false;

    #[ORM\Column]
    private array $assigneeGroup = [];

    #[ORM\Column]
    private ?int $questionsNo = null;

    #[ORM\Column(nullable: true)]
    private array $questionsList = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;

    #[ORM\ManyToOne(inversedBy: 'createdQuizzes')]
    private ?Teacher $createdBy = null;

    #[ORM\Column]
    private ?int $maxGrade = null;

    #[ORM\Column]
    private ?int $maxPoints = null;

    #[ORM\Column(length: 255)]
    private ?string $questionsSource = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getStartAt(): ?\DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(\DateTimeInterface $startAt): self
    {
        $this->startAt = $startAt;

        return $this;
    }

    public function getEndAt(): ?\DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(\DateTimeInterface $endAt): self
    {
        $this->endAt = $endAt;

        return $this;
    }

    public function getTimeLimit(): ?int
    {
        return $this->timeLimit;
    }

    public function setTimeLimit(?int $timeLimit): self
    {
        $this->timeLimit = $timeLimit;

        return $this;
    }

    public function getTimeUnit(): ?string
    {
        return $this->timeUnit;
    }

    public function setTimeUnit(?string $timeUnit): self
    {
        $this->timeUnit = $timeUnit;

        return $this;
    }

    public function getAssigneeGroup(): array
    {
        return $this->assigneeGroup;
    }

    public function setAssigneeGroup(array $assigneeGroup): self
    {
        $intAssignedGroup = [];
        foreach ($assigneeGroup as $k => $value) {
            $intAssignedGroup[] = (int)$value;
        }
        $this->assigneeGroup = $intAssignedGroup;

        return $this;
    }

    public function getQuestionsNo(): ?int
    {
        return $this->questionsNo;
    }

    public function setQuestionsNo(int $questionsNo): self
    {
        $this->questionsNo = $questionsNo;

        return $this;
    }

    public function getQuestionsList(): array
    {
        return $this->questionsList;
    }

    public function setQuestionsList(?array $questionsList): self
    {
        $this->questionsList = $questionsList;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCreatedBy(): ?Teacher
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Teacher $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getMaxGrade(): ?int
    {
        return $this->maxGrade;
    }

    public function setMaxGrade(int $maxGrade): self
    {
        $this->maxGrade = $maxGrade;

        return $this;
    }

    public function getQuestionsSource(): ?string
    {
        return $this->questionsSource;
    }

    public function setQuestionsSource(string $questionsSource): self
    {
        $this->questionsSource = $questionsSource;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxPoints(): ?int
    {
        return $this->maxPoints;
    }

    /**
     * @param int|null $maxPoints
     */
    public function setMaxPoints(?int $maxPoints): void
    {
        $this->maxPoints = $maxPoints;
    }

    /**
     * @return bool|null
     */
    public function getPracticeQuiz(): ?bool
    {
        return $this->practiceQuiz;
    }

    /**
     * @param bool|null $practiceQuiz
     */
    public function setPracticeQuiz(?bool $practiceQuiz): void
    {
        $this->practiceQuiz = $practiceQuiz;
    }
}
