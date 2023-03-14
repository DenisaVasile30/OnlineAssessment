<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        if ($this->isGranted('ROLE_TEACHER')) {
            return $this->render('home_teacher/index.html.twig', [
                'controller_name' => 'HomeController',
            ]);
        } elseif ($this->isGranted('ROLE_STUDENT')) {
            return $this->render('home_student/index.html.twig', [
                'controller_name' => 'HomeController',
            ]);
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
