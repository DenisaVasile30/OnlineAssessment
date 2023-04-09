<?php

namespace App\Controller;

use App\Entity\Assessment;
use App\Entity\AssignedSubjects;
use App\Entity\CreatedQuiz;
use App\Entity\QuizQuestion;
use App\Entity\Subject;
use App\Form\AssessmentFormType;
use App\Form\AssignedSubjectsFormType;
use App\Form\CreateQuizFormType;
use App\Form\QuizQuestionsAddFormType;
use App\Form\QuizQuestionsFromFileFormType;
use App\Form\SubjectFormType;
use App\Form\SubmittedCodeFormType;
use App\Helper\CompilerHelper;
use App\Helper\ProgrammingLanguageHelper;
use App\Repository\AssessmentRepository;
use App\Repository\AssignedSubjectsRepository;
use App\Repository\CreatedQuizRepository;
use App\Repository\GroupRepository;
use App\Repository\QuizQuestionRepository;
use App\Repository\StudentRepository;
use App\Repository\SubjectRepository;
use App\Repository\TeacherRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

            /** @var UploadedFile $subjectFile */
            $subjectFile = $form['contentFile']->getData();
            if ($subjectFile) {
                $originalFilename = pathinfo($subjectFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$subjectFile->guessExtension();
                $subject->setFileName($newFilename);

                $content = file_get_contents($subjectFile->getPathname());
                $subject->setContentFile($content);
            }
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
        SubjectRepository $subjectRepository,
        AssessmentRepository $assessmentRepository
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
            $assessment->setDescription($form->get('description')->getData());
            $assessment->setCreatedAt(new \DateTime());
            $assessment->setAssigneeGroup($form->get('assigneeGroup')->getData());
            $assessment->setStartAt($form->get('startAt')->getData());
            $assessment->setEndAt($form->get('endAt')->getData());
            $assessment->setCreatedBy($teacher[0]);
            $assessment->setStatus('Active');
            $assessment->setSubjectList($form->get('subjectList')->getData());
            $assessment->setTimeLimit($form->get('timeLimit')->getData());
            $assessment->setTimeUnit($form->get('timeUnit')->getData());

            $selectedSubjects = $assessment->getSubjectList();
            foreach ($subjectsArray as $key => $value) {
                    if ($value->getDescription() == $selectedSubjects[0]) {
                        // TO DO: Do not allow subject have id 0
                        $assessment->setSubject(
                            ($subjectRepository->findBy(['description' => $selectedSubjects[0]])[0])
                        );
                    }
//                }
            }
            $entityManager->persist($assessment);
            $entityManager->flush();

            return $this->redirectToRoute('app_assessment');
        } else {

            return $this->render('assessment/schedule_assessment.html.twig', [
                'scheduleAssessmentForm' => $form->createView(),
            ]);
        }
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
        SubjectRepository $subjectRepository,
        AssignedSubjectsRepository $assignedSubjectsRepository
    ): Response
    {
        $responseMessage = '';
        $requiredAssessment = $assessmentRepository->findOneBy(['id' => $assessment]);
        $assignedSubjects = $assignedSubjectsRepository->findBy(['assessment' => $requiredAssessment->getId()]);
//        dd(($requiredAssessment->getSubject())->getId());
        $subjectContent = $subjectRepository->findOneBy(['id' => ($requiredAssessment->getSubject())->getId()]);
        $reservedWordsList = ProgrammingLanguageHelper::getReservedWords('cpp');
//        dd($reservedWordsList);
        $requiredSubjects = [];
        $filesContent = [];
//        $subjectContent = $subjectRepository->findby(['id' => $assignedSubject->getSubjectsOptionList()[0]['id']]);
//        $keyFile = $assignedSubject->getSubjectsOptionList()[0]['id'];

        if ($subjectContent->getContentFile() != null) {
            $filesContent[$subjectContent->getId()] = stream_get_contents($subjectContent->getContentFile());
        } else {
            $filesContent[$subjectContent->getId()] = 'Nothing to show';
        }
        $requiredSubject[] = $subjectContent;
//        foreach ($assignedSubjects as $assignedSubject) {
//            if (count($assignedSubject->getSubjectsOptionList()) > 1) {
//                $randomKey = array_rand($assignedSubject->getSubjectsOptionList());
//                $randomSubject[] = $assignedSubject->getSubjectsOptionList()[$randomKey];
//                $assignedSubject->setSubjectsOptionListFromRand($randomSubject);
//            }
//            $subjectContent = $subjectRepository->findby(['id' => $assignedSubject->getSubjectsOptionList()[0]['id']]);
//            $keyFile = $assignedSubject->getSubjectsOptionList()[0]['id'];
//
//            if ($subjectContent[0]->getContentFile() != null) {
//                $filesContent[$keyFile] = stream_get_contents($subjectContent[0]->getContentFile());
//            } else {
//                $filesContent[$keyFile] = 'Nothing to show';
//            }
//            $requiredSubjects[] = $subjectRepository->findOneBy(
//                ['id' => $assignedSubject->getSubjectsOptionList()[0]['id']]
//            );
//        }

        $form = $this->createForm(SubmittedCodeFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('run')->isClicked()) {
                $data = ($form->get('codeArea')->getData());
                $compiler = new CompilerHelper($data);
                [$responseMessage, $compiledSuccessfully] = $compiler->makeApiCall();
            } elseif ($form->get('submit')->isClicked()) {
                dd('on submit');
            }
        }
//        dd($requiredSubjects);
        return $this->render('assessment/start_assessment.html.twig', [
            'requiredAssessment' => $requiredAssessment,
//            'requiredSubject' => $requiredSubject,
            'requiredSubjects' => $requiredSubject,
            'filesContent' => $filesContent,
            'reservedWordsList' => $reservedWordsList,
//            'contentFileExist' => (bool)$requiredSubject->getContentFile(),
            'submittedCode' => $form->createView(),
            'responseMessage' => $responseMessage
        ]);
    }

    #[Route('/home/assessments/startAssessment/download/{subject}', name: 'app_download_subject_content')]
    public function downloadSubjectContent(
        Request $request,
        int $subject,
        SubjectRepository $subjectRepository
    ): Response
    {
        $subjectContent = $subjectRepository->find($subject);
        $content = stream_get_contents($subjectContent->getContentFile());
        $response = new Response($content);
        $fileName = $subjectContent->getFileName();
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
