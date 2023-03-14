<?php

namespace App\Entity;

use App\Repository\SubjectRepository;
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

    #[ORM\Column(type: 'string', nullable: false, length: 60)]
    private string $subject;

    #[ORM\Column(type: 'date', nullable: false)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $lastModified;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $content;

    #[ORM\Column(type: 'string', length: 2000, nullable: true)]
    private string $subjectContent;

    #[ORM\Column(type: 'string', length: 2000, nullable: true)]
    private string $subjectRequirements;

    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'subjects')]
    #[ORM\JoinColumn(nullable: false)]
    private Teacher $issuedBy;

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

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastModified(): ?\DateTimeInterface
    {
        return $this->lastModified;
    }

    public function setLastModified(?\DateTimeInterface $lastModified): self
    {
        $this->lastModified = $lastModified;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content): self
    {
        $this->content = $content;

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
}
