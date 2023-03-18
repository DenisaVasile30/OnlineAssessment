<?php

namespace App\Controller;

use App\Entity\Assessment;
use App\Entity\Subject;
use App\Form\AssessmentFormType;
use App\Form\SubjectFormType;
use App\Repository\AssessmentRepository;
use App\Repository\GroupRepository;
use App\Repository\StudentRepository;
use App\Repository\SubjectRepository;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        TeacherRepository $teacherRepository,
        SluggerInterface $slugger
    ): Response
    {
        $subject = new Subject();
        $form = $this->createForm(SubjectFormType::class, $subject);
        $form->handleRequest($request);
//        to do: check the file uploading
        if ($form->isSubmitted() && $form->isValid()) {
            $userId = $this->getUser()->getIdentifierId();
            $teacher = $teacherRepository->getTeacher($userId);
            $subject->setCreatedAt(new \DateTime());
            $subject->setIssuedBy($teacher[0]);
            $subjectFile = $form->get('content')->getData();

            if ($subjectFile) {
                $originalFilename = pathinfo($subjectFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$subjectFile->guessExtension();

                $subject->setFileName($newFilename);
                $subject->setContent($subjectFile);
                $entityManager->persist($subject);
                $entityManager->flush();
            }

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

    #[Route('/home/assessments/show', name: 'app_show_assessments')]
    public function showAssessments(
        Request $request,
        EntityManagerInterface $entityManager,
        AssessmentRepository $assessmentRepository,
        StudentRepository $studentRepository,
        TeacherRepository $teacherRepository,
        GroupRepository $groupRepository
    ): Response
    {
        $userId = $this->getUser()->getIdentifierId();
        if (in_array('ROLE_STUDENT', $this->getUser()->getRoles())) {
            $isStudent = true;
            $groupId = ($studentRepository->getGroupByUserId($userId))[0]->getGroupId();
            $groupNo = $groupRepository->getGroupNo($groupId)[0]->getGroupNo();
            $assessments = $assessmentRepository->getAssessmentsByGroupNo($groupNo);
//            dd($assessments);
        } elseif (in_array('ROLE_TEACHER', $this->getUser()->getRoles())) {
            $isStudent = false;
            $teacher = $teacherRepository->getGroupByUserId($userId)[0];
            $groupId = $teacher->getAssignedGroups();
            $assessments = $assessmentRepository->getAssessmentsByIssuerId($teacher->getId());
//            dd($assessments);
//            TO DO implement functionality
        }

        return $this->render('assessment/assessments_list.html.twig', [
            'assessmentsList' => $assessments
        ]);
    }

    #[Route('/home/assessments/startAssessment/{assessment}', name: 'app_start_assessment')]
    public function startAssessment(
        Request $request,
        int $assessment,
        AssessmentRepository $assessmentRepository,
        SubjectRepository $subjectRepository
    ): Response
    {
        $requiredAssessment = $assessmentRepository->findOneBy(['id' => $assessment]);
        $requiredSubject = $subjectRepository->findOneBy(['id' => $requiredAssessment->getSubject()->getId()]);

        return $this->render('assessment/start_assessment.html.twig', [
            'requiredAssessment' => $requiredAssessment,
            'requiredSubject' => $requiredSubject,
            'contentFileExist' => (bool)$requiredSubject->getContent()
        ]);
    }

    #[Route('/home/assessments/startAssessment/download/{subject}', name: 'app_download_subject_content')]
    public function downloadSubjectContent(
        Request $request,
        int $subject,
        SubjectRepository $subjectRepository
    ): Response
    {
        $file = $subjectRepository->findOneBy(
            ['id' => $subject]
        )->getContent();
//        dd($file);
        if (!$file) {
            throw $this->createNotFoundException('The file does not exist.');
        }

        $fileName = 'subject';
        $contentType = '.txt';

        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);
        $response->headers->set('Content-Type', $contentType);
        $response->setAutoEtag();

        return $response;
    }
}
