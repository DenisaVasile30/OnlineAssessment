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
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'json', nullable: false)]
    /**
     * @Groups({"read", "write"})
     */
    private $subjectsOptionList = [];

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $requirementNo = null;

//    #[ORM\ManyToOne(targetEntity: Assessment::class, inversedBy: 'assignedSubjects')]
//    #[ORM\JoinColumn(nullable: false)]
    #[ORM\Column(type: 'integer', nullable: false)]
    private $assessment = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubjectsOptionList(): array
    {
        return $this->subjectsOptionList;
    }

    public function setSubjectsOptionList(array $subjectsOptionList): self
    {
        foreach ($subjectsOptionList as $key => $value) {
            $this->subjectsOptionList[$key] = [
                'id' => $value->getId(),
                'description' => $value->getDescription()
            ];
        }

        return $this;
    }

    public function setSubjectsOptionListFromRand(array $subjectsOptionList): self
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

    public function getAssessment()
    {
        return $this->assessment;
    }

    public function setAssessment( $assessment): self
    {
        $this->assessment = $assessment;

        return $this;
    }
}
