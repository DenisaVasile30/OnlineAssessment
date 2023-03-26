<?php

namespace App\Controller;

use App\Entity\TicketAnswer;
use App\Entity\User;
use App\Form\TicketAnswerFormType;
use App\Repository\TicketAnswerRepository;
use App\Repository\TicketRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SupportController extends AbstractController
{
    #[Route('/support', name: 'app_support')]
    public function loadTickets(
        TicketRepository $ticketRepository
    ): Response
    {
        $user = $this->getUser();
        $ownedTickets = $ticketRepository->getOwnedTickets($user);

        return $this->render('support/tickets.html.twig', [
            'ownedTickets' => $ownedTickets,
        ]);
    }

    #[Route('/support/{id}', name: 'app_show_one_ticket')]
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
}
