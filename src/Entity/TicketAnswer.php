<?php

namespace App\Entity;

use App\Repository\TicketAnswerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketAnswerRepository::class)]
#[ORM\Table(name: '`tickets_answers`')]
class TicketAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $id;

    #[ORM\Column(type: 'string', length: 2500, nullable: false)]
    private string $answer;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTimeInterface $addedAt;

    #[ORM\Column(type: 'string', length: 150, nullable: false)]
    private string $answerBy;

    #[ORM\ManyToOne(targetEntity: Ticket::class, inversedBy: 'tickets')]
    private Ticket $ticket;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): self
    {
        $this->answer = $answer;

        return $this;
    }

    public function getAddedAt(): \DateTimeInterface
    {
        return $this->addedAt;
    }

    public function setAddedAt(\DateTimeInterface $addedAt): self
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function getTicket(): Ticket
    {
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }

    /**
     * @return User
     */
    public function getAnswerBy(): string
    {
        return $this->answerBy;
    }

    /**
     * @param User $answerBy
     */
    public function setAnswerBy(string $answerBy): void
    {
        $this->answerBy = $answerBy;
    }
}
