<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ORM\Table(name: '`groups`')]
#[ORM\UniqueConstraint(
    name: 'group_no_idx',
    columns: ['group_no']
)]
class Group
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer', nullable: false)]
    private $groupNo;

    #[ORM\Column(type: 'date', nullable: false)]
    private $createdDate;

    #[ORM\Column(type: 'string', length: 500, nullable: false)]
    private $faculty;


    #[ORM\OneToMany(mappedBy: 'group', targetEntity: Student::class)]
    private Collection $group;

    public function __construct()
    {
        $this->group = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGroupNo(): int
    {
        return $this->groupNo;
    }

    public function setGroupNo(int $groupNo): self
    {
        $this->groupNo = $groupNo;

        return $this;
    }

    public function getGroupYear(): \DateTimeInterface
    {
        return $this->groupYear;
    }

    public function setGroupYear(\DateTimeInterface $groupYear): self
    {
        $this->groupYear = $groupYear;

        return $this;
    }

    public function getFaculty(): string
    {
        return $this->faculty;
    }

    public function setFaculty(string $faculty): self
    {
        $this->faculty = $faculty;

        return $this;
    }

    public function addGroupNo(Student $groupNo): self
    {
        if (!$this->group_no->contains($groupNo)) {
            $this->group_no->add($groupNo);
            $groupNo->setGroupNo($this);
        }

        return $this;
    }

    public function removeGroupNo(Student $groupNo): self
    {
        if ($this->group_no->removeElement($groupNo)) {
            // set the owning side to null (unless already changed)
            if ($groupNo->getGroupNo() === $this) {
                $groupNo->setGroupNo(null);
            }
        }

        return $this;
    }
}
