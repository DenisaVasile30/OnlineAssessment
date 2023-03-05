<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeStudentController extends AbstractController
{
    #[Route('/home/student', name: 'app_home_student')]
    public function index(): Response
    {
        return $this->render('home_student/index.html.twig');
    }
}
