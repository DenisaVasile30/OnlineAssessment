<?php

namespace App\Entity;

use App\Repository\SupportedQuizRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupportedQuizRepository::class)]
class SupportedQuiz
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $quiz;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $startedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $endedAt = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $maxGrade;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $obtainedGrade = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $totalTimeSpent = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'supportedQuiz')]
    #[ORM\JoinColumn(nullable: false)]
    private User $supportedBy;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTime $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTime
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTime $endedAt): self
    {
        $this->endedAt = $endedAt;

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

    public function getObtainedGrade(): ?int
    {
        return $this->obtainedGrade;
    }

    public function setObtainedGrade(int $obtainedGrade): self
    {
        $this->obtainedGrade = $obtainedGrade;

        return $this;
    }

    public function getTotalTimeSpent(): ?int
    {
        return $this->totalTimeSpent;
    }

    public function setTotalTimeSpent(int $totalTimeSpent): self
    {
        $this->totalTimeSpent = $totalTimeSpent;

        return $this;
    }

    /**
     * @return User
     */
    public function getSupportedBy(): User
    {
        return $this->supportedBy;
    }

    /**
     * @param User $supportedBy
     */
    public function setSupportedBy(User $supportedBy): void
    {
        $this->supportedBy = $supportedBy;
    }

    public function getSupportedQuizDetails(): ?SupportedQuizDetails
    {
        return $this->supportedQuizDetails;
    }

    public function setSupportedQuizDetails(?SupportedQuizDetails $supportedQuizDetails): self
    {
        $this->supportedQuizDetails = $supportedQuizDetails;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuizId(): int
    {
        return $this->quizId;
    }

    /**
     * @param int $quizId
     */
    public function setQuizId(int $quizId): void
    {
        $this->quizId = $quizId;
    }

    /**
     * @return int
     */
    public function getQuiz(): int
    {
        return $this->quiz;
    }

    /**
     * @param int $quiz
     */
    public function setQuiz(int $quiz): void
    {
        $this->quiz = $quiz;
    }
}
