<?php

namespace App\Controller;

use App\Repository\GroupRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    #[Route('/home/groups', name: 'app_groups')]
    public function index(GroupRepository $groups): Response
    {
        $studentsList = $groups->getStudentsFromGroup();
//        dd($studentsList);
        return $this->render('groups/assessment.html.twig', [
            'studentsList' => $studentsList,
        ]);
    }
}
