<?php

namespace App\Controller;

use App\Entity\Assessment;
use App\Entity\Subject;
use App\Form\AssessmentFormType;
use App\Form\SubjectFormType;
use App\Repository\AssessmentRepository;
use App\Repository\SubjectRepository;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        TeacherRepository $teacherRepository,
        SubjectRepository $subjectRepository
    ): Response
    {
        $assessment = new Assessment();
        $userId = $this->getUser()->getIdentifierId();
        $teacher = $teacherRepository->getTeacher($userId);
        $teacherAssignedGroups = $teacher[0]->getAssignedGroups();
        $subjectsArray = $subjectRepository->getSubjectsByIssuer($teacher[0]->getId());
        $subjects = [];
        foreach ($subjectsArray as $item) {
            $subjects[$item->getId()] = $item->getDescription();
        }
        $form = $this->createForm(
            AssessmentFormType::class,
            $assessment,
            [
                'teacherAssignedGroups' => $teacherAssignedGroups,
                'subjects' => $subjects
            ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $assessment->setCreatedAt(new \DateTime());
            $assessment->setCreatedBy($teacher[0]);
//            if the teacher selected multiple subjects we will extract one random at launch
            $selectedSubjects = $assessment->getSubjectList();
            $isSingleSubject = count($selectedSubjects) > 1;
            foreach ($subjectsArray as $key => $value) {
//                if ($isSingleSubject) {
                    if ($value->getDescription() == $selectedSubjects[0]) {
                        // TO DO: Do not allow subject have id 0
                        $assessment->setSubject(
                            $subjectRepository->find($key)
                        );
                    }
//                }
            }

            $entityManager->persist($assessment);
            $entityManager->flush();

            return $this->redirectToRoute('app_assessment');
        }

        return $this->render('assessment/schedule_assessment.html.twig', [
            'scheduleAssessmentForm' => $form->createView()
        ]);
    }
}
