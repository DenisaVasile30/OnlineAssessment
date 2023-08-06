<?php

namespace App\Entity;

use App\Repository\SupportedQuizDetailsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupportedQuizDetailsRepository::class)]
class SupportedQuizDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $quizId;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $questionId;

    #[ORM\Column(type: 'string', length: 1500, nullable: false)]
    private ?string $providedAnswer = null;
//  TO DO: make nullable false
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $timeSpent = null;

    #[ORM\Column(type: 'string', length: 1500, nullable: false)]
    private string $correctAnswer;

    #[ORM\Column(type: 'integer', nullable: false)]
    private int $questionScore;

//  TO DO: make nullable false
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?float $obtainedScore = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'supportedQuizDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private User $supportedByStudent;


    public function __construct()
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuizId(): int
    {
        return $this->quizId;
    }

    public function setQuizId(int $quizId): self
    {
        $this->quizId = $quizId;

        return $this;
    }

    public function getQuestionId(): int
    {
        return $this->questionId;
    }

    public function setQuestionId(int $questionId): self
    {
        $this->questionId = $questionId;

        return $this;
    }

    public function getProvidedAnswer(): string
    {
        return $this->providedAnswer;
    }

    public function setProvidedAnswer(string $providedAnswer): self
    {
        $this->providedAnswer = $providedAnswer;

        return $this;
    }

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(int $timeSpent): self
    {
        $this->timeSpent = $timeSpent;

        return $this;
    }

    public function getCorrectAnswer(): string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(string $correctAnswer): self
    {
        $this->correctAnswer = $correctAnswer;

        return $this;
    }

    public function getQuestionScore(): ?int
    {
        return $this->questionScore;
    }

    public function setQuestionScore(int $questionScore): self
    {
        $this->questionScore = $questionScore;

        return $this;
    }

    public function getObtainedScore(): ?int
    {
        return $this->obtainedScore;
    }

    public function setObtainedScore(float $obtainedScore): self
    {
        $this->obtainedScore = $obtainedScore;

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
     * @return  User
     */
    public function setSupportedBy(User $supportedBy): self
    {
        $this->supportedBy = $supportedBy;

        return $this;
    }

    /**
     * @return Collection<int, SupportedQuiz>
     */
    public function getQuestion(): Collection
    {
        return $this->question;
    }

    public function addQuestion(SupportedQuiz $question): self
    {
        if (!$this->question->contains($question)) {
            $this->question->add($question);
            $question->setSupportedQuizDetails($this);
        }

        return $this;
    }

    public function removeQuestion(SupportedQuiz $question): self
    {
        if ($this->question->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getSupportedQuizDetails() === $this) {
                $question->setSupportedQuizDetails(null);
            }
        }

        return $this;
    }

    /**
     * @return User
     */
    public function getSupportedByStudent(): User
    {
        return $this->supportedByStudent;
    }

    /**
     * @param User $supportedByStudent
     */
    public function setSupportedByStudent(User $supportedByStudent): void
    {
        $this->supportedByStudent = $supportedByStudent;
    }
}
