<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\TicketAnswer;
use App\Entity\User;
use App\Form\CreateTicketFormType;
use App\Form\TicketAnswerFormType;
use App\Repository\GroupRepository;
use App\Repository\TicketAnswerRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends AbstractController
{
    #[Route('/support', name: 'app_support')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function loadTickets(
        TicketRepository $ticketRepository,
        GroupRepository $groupRepository
    ): Response
    {
        $user = $this->getUser();
        $ownedTickets = $ticketRepository->getOwnedTickets($user);
        if (in_array('ROLE_STUDENT', $user->getRoles())) {
            $userGroupId = $user->getStudent()->getGroupId();
            if ($userGroupId) {
                $groupNo = $groupRepository->findBy(['id' => $userGroupId])[0]->getGroupNo();
                $ticketsAssignedToGroup = $ticketRepository->getTicketsWithMultipleAssignedTo($groupNo);
                if (count($ownedTickets) > 0) {
                    $ownedTickets = array_merge($ownedTickets, $ticketsAssignedToGroup);
                }
            }
        }

        return $this->render('support/tickets.html.twig', [
            'ownedTickets' => $ownedTickets,
        ]);
    }

    #[Route('/support/{id}', name: 'app_show_one_ticket', requirements: ['id' => '\d+'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function loadOneTicket(
        Request $request,
        int $id,
        TicketRepository $ticketRepository,
        TicketAnswerRepository $answerRepository
    ): Response
    {
        /** @var User $user */
        $user  = $this->getUser();
        $ticket = $ticketRepository->findTicket($id);
        $answer = new TicketAnswer();
        $answerForm = $this->createForm(TicketAnswerFormType::class, $answer);
        $answerForm->handleRequest($request);

        $answersList = $answerRepository->findBy(['ticket' => $ticket]);

        if ($answerForm->isSubmitted() && $answerForm->isValid()) {
            $answer->setTicket($ticket[0]);
            $answer->setAnswerBy($user->getEmail());
            $answer->setAddedAt(new \DateTime());
            $answer->setAnswer($answerForm->get('answer')->getData());

            $answerRepository->save($answer, true);
            return $this->redirectToRoute('app_show_one_ticket', [ 'id' => $id]);
        }
        return $this->render('support/show_one.html.twig', [
            'ticket' => $ticket[0],
            'answersList' => $answersList,
            'answerForm' => $answerForm->createView()
        ]);
    }

    #[Route('/support/new', name: 'app_create_ticket')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createTicket(
        Request $request,
        UserRepository $userRepository,
        TicketRepository $ticketRepository,
        GroupRepository $groupRepository
    ): Response
    {
        /** @var User $user */
        $user  = $this->getUser();
        $ticket = new Ticket();
        $usersList =  $userRepository->getAllEmailAddresses();
        $emails = array_column($usersList, "users_email");

        $groups = $groupRepository->findAll();
        foreach ($groups as $group) {
            $emails[] = 'Group ' . $group->getGroupNo();
        }

        $ticketForm = $this->createForm(CreateTicketFormType::class, $ticket, ['emails' => $emails]);
        $ticketForm->handleRequest($request);
        if ($ticketForm->isSubmitted() && $ticketForm->isValid()) {
            $ticket->setIssuedBy($this->getUser());
            $ticket->setIssuedAt(new \DateTime());
            $assignToEmail = $ticketForm->get('assignedTo')->getData();

            if (str_starts_with($assignToEmail, 'Group')) {
                $groupNo = explode(" ", $assignToEmail)[1];
                $ticket->setMultipleAssignTo(['Group' => $groupNo]);
            } else {
                $ticket->setAssignedTo($userRepository->findOneBy(['email' => $assignToEmail]));
            }
            $ticket->setTicketStatus('Active');
            $ticketRepository->save($ticket, true);

            return $this->redirectToRoute('app_support');
        }

        return $this->render('support/create_new_ticket.html.twig', [
            'newTicketForm' => $ticketForm->createView()
        ]);
    }
}
