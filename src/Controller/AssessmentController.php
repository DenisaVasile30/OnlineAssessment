<?php

namespace App\Controller;

use App\Entity\Assessment;
use App\Entity\Subject;
use App\Entity\Teacher;
use App\Entity\User;
use App\Form\AssessmentFormType;
use App\Form\RegistrationFormType;
use App\Form\SubjectFormType;
use App\Repository\AssessmentRepository;
use App\Repository\TeacherRepository;
use Doctrine\DBAL\Types\DateType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

class AssessmentController extends AbstractController
{
    #[Route('/home/assessment', name: 'app_assessment')]
    public function index(): Response
    {
        return $this->render('assessment/assessment.html.twig', [
            'controller_name' => 'AssessmentController',
        ]);
    }

    #[Route('/home/assessment/add', name: 'app_add_subject')]
    public function addSubject(
        Request $request,
        EntityManagerInterface $entityManager,
        TeacherRepository $teacherRepository
    ): Response
    {
        $subject = new Subject();
        $form = $this->createForm(SubjectFormType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userId = $this->getUser()->getIdentifierId();
            $teacher = $teacherRepository->getTeacher($userId);
            $subject->setCreatedAt(new \DateTime());
            $subject->setIssuedBy($teacher[0]);


            $entityManager->persist($subject);
            $entityManager->flush();

            return $this->redirectToRoute('app_assessment');
        }

        return $this->render('assessment/add_subject.html.twig', [
            'addSubjectForm' => $form->createView()
        ]);
    }

    #[Route('/home/assessment/schedule', name: 'app_schedule_assessment')]
    public function scheduleAssessment(
        Request $request,
        EntityManagerInterface $entityManager,
        AssessmentRepository $assessmentRepository
    ): Response
    {
        $assessment = new Assessment();
        $form = $this->createForm(AssessmentFormType::class, $assessment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            $userId = $this->getUser()->getIdentifierId();
//            $teacher = $assessmentRepository->getTeacher($userId);
//            $subject->setCreatedAt(new \DateTime());
//            $subject->setIssuedBy($teacher[0]);
//
//
//            $entityManager->persist($subject);
//            $entityManager->flush();

//            ->add('description')
//                ->add('createdAt')
//                ->add('assigneeGroup')
//                ->add('startAt')
//                ->add('endAt')
//                ->add('subject') + issuedBy

            return $this->redirectToRoute('app_assessment');
        }

        return $this->render('assessment/schedule_assessment.html.twig', [
            'scheduleAssessmentForm' => $form->createView()
        ]);
    }
}
