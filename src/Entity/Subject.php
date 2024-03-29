<?php

namespace App\Entity;

use App\Repository\SubjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubjectRepository::class)]
#[ORM\Table(name: '`subjects`')]
class Subject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false, length: 200)]
    private string $description;

    #[ORM\Column(type: 'string', nullable: false, length: 60)]
    private string $subject;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $lastModified;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $contentFile;

    #[ORM\Column(type: 'string', nullable: true)]
    private $fileName;

    #[ORM\Column(type: 'string', length: 2000, nullable: true)]
    private ?string $subjectContent = null;

    #[ORM\Column(type: 'string', length: 2000, nullable: true)]
    private ?string $subjectRequirements = null;

    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'subjects')]
    #[ORM\JoinColumn(nullable: false)]
    private Teacher $issuedBy;

    #[ORM\OneToMany(mappedBy: 'subject', targetEntity: Assessment::class)]
    private Collection $assessments;

    public function __construct()
    {
        $this->assessments = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastModified(): ?\DateTime
    {
        return $this->lastModified;
    }

    public function setLastModified(?\DateTime $lastModified): self
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    public function getSubjectContent(): ?string
    {
        return $this->subjectContent;
    }

    public function setSubjectContent(?string $subjectContent): self
    {
        $this->subjectContent = $subjectContent;

        return $this;
    }

    public function getIssuedBy(): ?Teacher
    {
        return $this->issuedBy;
    }

    public function setIssuedBy(?Teacher $issuedBy): self
    {
        $this->issuedBy = $issuedBy;

        return $this;
    }

    public function getSubjectRequirements(): ?string
    {
        return $this->subjectRequirements;
    }

    public function setSubjectRequirements(?string $subjectRequirements)
    {
        $this->subjectRequirements = $subjectRequirements;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;

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
            $assessment->setSubject($this);
        }

        return $this;
    }

    public function removeAssessment(Assessment $assessment): self
    {
        if ($this->assessments->removeElement($assessment)) {
            // set the owning side to null (unless already changed)
            if ($assessment->getSubject() === $this) {
                $assessment->setSubject(null);
            }
        }

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
}
