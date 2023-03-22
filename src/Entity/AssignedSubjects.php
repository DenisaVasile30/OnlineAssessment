<?php

namespace App\Entity;

use App\Repository\AssignedSubjectsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[ORM\Entity(repositoryClass: AssignedSubjectsRepository::class)]
class AssignedSubjects
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private array $subjectsOptionList = [];

    #[ORM\Column]
    private ?int $requirementNo = null;

    #[ORM\ManyToOne(inversedBy: 'assignedSubjects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Assessment $assessment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubjectsOptionList(): array
    {
        return $this->subjectsOptionList;
    }

    public function setSubjectsOptionList(array $subjectsOptionList): self
    {
        $this->subjectsOptionList = $subjectsOptionList;

        return $this;
    }

    public function getRequirementNo(): ?int
    {
        return $this->requirementNo;
    }

    public function setRequirementNo(int $requirementNo): self
    {
        $this->requirementNo = $requirementNo;

        return $this;
    }

    public function getAssessment(): ?Assessment
    {
        return $this->assessment;
    }

    public function setAssessment(?Assessment $assessment): self
    {
        $this->assessment = $assessment;

        return $this;
    }
}
