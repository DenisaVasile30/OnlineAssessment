<?php

namespace App\Entity;

use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ORM\Table(name: '`teachers`')]
class Teacher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'date', nullable: false)]
    private $enrollmentDate;

    #[ORM\Column(type: 'json', nullable: true)]
    private $assignedGroups = [];

    #[ORM\OneToOne(inversedBy: 'teacher', targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    public $user;

    #[ORM\OneToMany(mappedBy: 'issuedBy', targetEntity: Subject::class)]
    private Collection $subjects;

    #[ORM\OneToMany(mappedBy: 'issuedBy', targetEntity: QuizQuestion::class)]
    private Collection $quizQuestions;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: Assessment::class)]
    private Collection $assessments;

    #[ORM\OneToMany(mappedBy: 'createdBy', targetEntity: CreatedQuiz::class)]
    private Collection $createdQuizzes;

    public function __construct()
    {
        $this->subjects = new ArrayCollection();
        $this->assessments = new ArrayCollection();
        $this->quizQuestions = new ArrayCollection();
        $this->createdQuizzes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEnrollmentDate(): \DateTimeInterface
    {
        return $this->enrollmentDate;
    }

    public function setEnrollmentDate(\DateTimeInterface $enrollmentDate): self
    {
        $this->enrollmentDate = $enrollmentDate;

        return $this;
    }

    public function getAssignedGroups(): ?array
    {
        return $this->assignedGroups;
    }

    public function setAssignedGroups(array $assignedGroups): self
    {
        $this->assignedGroups = $assignedGroups;

        return $this;
    }

    public function getUserId(): User
    {
        return $this->user_id;
    }

    public function setUserId(User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * @return Collection<int, Subject>
     */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject): self
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects->add($subject);
            $subject->setIssuedBy($this);
        }

        return $this;
    }

    public function removeSubject(Subject $subject): self
    {
        if ($this->subjects->removeElement($subject)) {
            // set the owning side to null (unless already changed)
            if ($subject->getIssuedBy() === $this) {
                $subject->setIssuedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Assessment>
     */
    public function getAssessments(): Collection
    {
        return $this->assessments;
    }

    public function addAssessment(Assessment $assessment): self
    {
        if (!$this->assessments->contains($assessment)) {
            $this->assessments->add($assessment);
            $assessment->setTeacher($this);
        }

        return $this;
    }

    public function removeAssessment(Assessment $assessment): self
    {
        if ($this->assessments->removeElement($assessment)) {
            // set the owning side to null (unless already changed)
            if ($assessment->getTeacher() === $this) {
                $assessment->setTeacher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getQuizQuestions(): Collection
    {
        return $this->quizQuestions;
    }

    /**
     * @param Collection $quizQuestions
     */
    public function setQuizQuestions(Collection $quizQuestions): void
    {
        $this->quizQuestions = $quizQuestions;
    }

    /**
     * @return Collection<int, CreatedQuiz>
     */
    public function getCreatedQuizzes(): Collection
    {
        return $this->createdQuizzes;
    }

    public function addCreatedQuiz(CreatedQuiz $createdQuiz): self
    {
        if (!$this->createdQuizzes->contains($createdQuiz)) {
            $this->createdQuizzes->add($createdQuiz);
            $createdQuiz->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedQuiz(CreatedQuiz $createdQuiz): self
    {
        if ($this->createdQuizzes->removeElement($createdQuiz)) {
            // set the owning side to null (unless already changed)
            if ($createdQuiz->getCreatedBy() === $this) {
                $createdQuiz->setCreatedBy(null);
            }
        }

        return $this;
    }
}
