<?php

namespace App\Entity;

use App\Repository\SupportedAssessmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SupportedAssessmentRepository::class)]
class SupportedAssessment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $assessmentId = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(type: 'datetime')]
    private $endedAt = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxGrade = null;

    #[ORM\Column(nullable: true)]
    private ?float $calculatedGrade = null;

    #[ORM\Column(nullable: true)]
    private ?int $timeSpent = null;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $contentFile;

    #[ORM\Column(type: 'string', nullable: true)]
    private $fileName;

    #[ORM\ManyToOne(inversedBy: 'supportedAssessments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $submittedBy = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private $resultedResponse;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssessmentId(): ?int
    {
        return $this->assessmentId;
    }

    public function setAssessmentId(int $assessmentId): self
    {
        $this->assessmentId = $assessmentId;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(\DateTimeInterface $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getMaxGrade(): ?int
    {
        return $this->maxGrade;
    }

    public function setMaxGrade(?int $maxGrade): self
    {
        $this->maxGrade = $maxGrade;

        return $this;
    }

    public function getCalculatedGrade(): ?float
    {
        return $this->calculatedGrade;
    }

    public function setCalculatedGrade(?float $calculatedGrade): self
    {
        $this->calculatedGrade = $calculatedGrade;

        return $this;
    }

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(?int $timeSpent): self
    {
        $this->timeSpent = $timeSpent;

        return $this;
    }

    public function getSubmittedBy(): User
    {
        return $this->submittedBy;
    }

    public function setSubmittedBy(User $submittedBy): self
    {
        $this->submittedBy = $submittedBy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName(string $fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContentFile()
    {
        return $this->contentFile;
    }

    /**
     * @param mixed $contentFile
     */
    public function setContentFile($contentFile)
    {
        $this->contentFile = $contentFile;

        return $this;
    }

    public function getResultedResponse(): ?array
    {
        return $this->resultedResponse;
    }

    public function setResultedResponse(?array $resultedResponse): self
    {
        $this->resultedResponse = $resultedResponse;

        return $this;
    }
}
