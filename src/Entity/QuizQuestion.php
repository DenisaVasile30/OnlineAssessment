<?php

namespace App\Entity;

use App\Repository\QuizQuestionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuizQuestionRepository::class)]
#[ORM\Table(name: '`quiz_questions`')]
class QuizQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 300, nullable: false)]
    private string $category;

    #[ORM\Column(type: 'string', length: 700, nullable: true)]
    private ?string $optionalDescription = null;

    #[ORM\Column(type: 'string', length: 1500, nullable: false)]
    private string $questionContent;

    #[ORM\Column(type: 'string', length: 400, nullable: false)]
    private string $choiceA;

    #[ORM\Column(type: 'string', length: 500, nullable: false)]
    private string $choiceB;

    #[ORM\Column(type: 'string', length: 500, nullable: false)]
    private string $choiceC;

    #[ORM\Column(type: 'string', length: 500, nullable: true)]
    private string $choiceD;

    #[ORM\Column(type: 'string', length: 500, nullable: false)]
    private string $correctAnswer;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTime $createdAt;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $contentFile;

    #[ORM\Column(type: 'string', nullable: true)]
    private $fileName;

    #[ORM\ManyToOne(targetEntity: Teacher::class, inversedBy: 'subjects')]
    #[ORM\JoinColumn(nullable: false)]
    private Teacher $issuedBy;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getOptionalDescription(): ?string
    {
        return $this->optionalDescription;
    }

    public function setOptionalDescription(string $optionalDescription): self
    {
        $this->optionalDescription = $optionalDescription;

        return $this;
    }

    public function getQuestionContent(): string
    {
        return $this->questionContent;
    }

    public function setQuestionContent(string $questionContent): self
    {
        $this->questionContent = $questionContent;

        return $this;
    }

    public function getChoiceA(): string
    {
        return $this->choiceA;
    }

    public function setChoiceA(string $choiceA): self
    {
        $this->choiceA = $choiceA;

        return $this;
    }

    public function getChoiceB(): string
    {
        return $this->choiceB;
    }

    public function setChoiceB(string $choiceB): self
    {
        $this->choiceB = $choiceB;

        return $this;
    }

    public function getChoiceC(): string
    {
        return $this->choiceC;
    }

    public function setChoiceC(string $choiceC): self
    {
        $this->choiceC = $choiceC;

        return $this;
    }

    public function getChoiceD(): ?string
    {
        return $this->choiceD;
    }

    public function setChoiceD(?string $choiceD): self
    {
        $this->choiceD = $choiceD;

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

    /**
     * @return Teacher
     */
    public function getIssuedBy(): Teacher
    {
        return $this->issuedBy;
    }

    /**
     * @param Teacher $issuedBy
     */
    public function setIssuedBy(Teacher $issuedBy): void
    {
        $this->issuedBy = $issuedBy;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
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
}
