<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: '`tickets`')]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 500, nullable: false)]
    private string $type;

    #[ORM\Column(type: 'string', length: 2500, nullable: false)]
    private string $ticketContent;

    #[ORM\Column(type: 'string', length: 15, nullable: false)]
    private string $ticketStatus;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private ?\DateTimeInterface $issuedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private User $issuedBy;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'assignedTickets')]
    #[ORM\JoinColumn(nullable: true)]
    private User $assignedTo;

    #[ORM\OneToMany(mappedBy: 'tickets', targetEntity: TicketAnswer::class)]
    private Collection $ticketAnswers;

    #[ORM\Column(type: 'json', nullable: true)]
    private $multipleAssignTo = [];

    public function __construct()
    {
        $this->ticketAnswers = new ArrayCollection();
//        $this->multipleAssignTo = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTicketContent(): string
    {
        return $this->ticketContent;
    }

    public function setTicketContent(string $ticketContent): self
    {
        $this->ticketContent = $ticketContent;

        return $this;
    }

    public function getTicketStatus(): string
    {
        return $this->ticketStatus;
    }

    public function setTicketStatus(string $ticketStatus): self
    {
        $this->ticketStatus = $ticketStatus;

        return $this;
    }

    public function getIssuedAt(): ?\DateTimeInterface
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(\DateTimeInterface $issuedAt): self
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    public function getIssuedBy(): ?User
    {
        return $this->issuedBy;
    }

    public function setIssuedBy(?User $issuedBy): self
    {
        $this->issuedBy = $issuedBy;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): self
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    /**
     * @return Collection<int, TicketAnswer>
     */
    public function getTicketAnswers(): Collection
    {
        return $this->ticketAnswers;
    }

    public function addTicketAnswer(TicketAnswer $ticketAnswer): self
    {
        if (!$this->ticketAnswers->contains($ticketAnswer)) {
            $this->ticketAnswers->add($ticketAnswer);
            $ticketAnswer->setAnswerBy($this);
        }

        return $this;
    }

    public function removeTicketAnswer(TicketAnswer $ticketAnswer): self
    {
        if ($this->ticketAnswers->removeElement($ticketAnswer)) {
            // set the owning side to null (unless already changed)
            if ($ticketAnswer->getAnswerBy() === $this) {
                $ticketAnswer->setAnswerBy(null);
            }
        }

        return $this;
    }

    public function getMultipleAssignTo(): ?array
    {
        return $this->multipleAssignTo;
    }

    public function setMultipleAssignTo(array $multipleAssignTo): void
    {
        if (isset($multipleAssignTo['Group'])) {
            $this->multipleAssignTo = [
                'Group' => (int)$multipleAssignTo['Group']
            ];
        }
    }
}
