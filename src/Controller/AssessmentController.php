<?php

namespace App\Controller;

use App\Entity\Assessment;
use App\Entity\AssignedSubjects;
use App\Entity\CreatedQuiz;
use App\Entity\QuizQuestion;
use App\Entity\Subject;
use App\Entity\SupportedAssessment;
use App\Form\AssessmentFormType;
use App\Form\AssignedSubjectsFormType;
use App\Form\CreateQuizFormType;
use App\Form\QuizQuestionsAddFormType;
use App\Form\QuizQuestionsFromFileFormType;
use App\Form\SubjectFormType;
use App\Form\SubmittedCodeFormType;
use App\Helper\CompilerHelper;
use App\Helper\FormatHelper\CodeCheckCLanguage;
use App\Helper\ProgrammingLanguageHelper;
use App\Helper\SubmittedCodeCHelper\StructuredRequirements;
use App\Repository\AssessmentRepository;
use App\Repository\AssignedSubjectsRepository;
use App\Repository\CreatedQuizRepository;
use App\Repository\GroupRepository;
use App\Repository\QuizQuestionRepository;
use App\Repository\StudentRepository;
use App\Repository\SubjectRepository;
use App\Repository\SupportedAssessmentRepository;
use App\Repository\SupportedQuizDetailsRepository;
use App\Repository\SupportedQuizRepository;
use App\Repository\TeacherRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        return $this->render('assessment/assessment.html.twig', [
            'controller_name' => 'AssessmentController',
        ]);
    }

    #[Route('/home/assessment/add', name: 'app_add_subject')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function addSubject(
        Request $request,
        EntityManagerInterface $entityManager,
        TeacherRepository $teacherRepository,
        SluggerInterface $slugger
    ): Response
    {
        $subject = new Subject();
        $form = $this->createForm(SubjectFormType::class, $subject,[
            'edit' => false
        ]);
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

            return $this->redirectToRoute('app_show_subject');
        }

        return $this->render('assessment/add_subject.html.twig', [
            'addSubjectForm' => $form->createView()
        ]);
    }

    #[Route('/home/assessment/schedule', name: 'app_schedule_assessment')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
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

            return $this->redirectToRoute('app_show_assessments');
        } else {

            return $this->render('assessment/schedule_assessment.html.twig', [
                'scheduleAssessmentForm' => $form->createView(),
            ]);
        }
    }

    #[Route('/home/assessments/show', name: 'app_show_assessments')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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
            if (!$groupId) {
                return $this->render('assessment/assessments_list.html.twig', [
                    'notAssignedToAGroup' => true
                ]);
            }
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
//        dd($assessments);
        return $this->render('assessment/assessments_list.html.twig', [
            'assessmentsList' => $assessments
        ]);
    }

    #[Route('/home/assessments/startAssessment/{assessment}', name: 'app_start_assessment')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_STUDENT')]
    public function startAssessment(
        Request $request,
        int $assessment,
        AssessmentRepository $assessmentRepository,
        SubjectRepository $subjectRepository,
        AssignedSubjectsRepository $assignedSubjectsRepository,
        SupportedAssessmentRepository $supportedAssessmentRepository,
        SluggerInterface $slugger
    ): Response
    {
        $responseMessage = '';
        $requiredAssessment = $assessmentRepository->findOneBy(['id' => $assessment]);
        $assignedSubjects = $assignedSubjectsRepository->findBy(['assessment' => $requiredAssessment->getId()]);
        $subjectContent = $subjectRepository->findOneBy(['id' => ($requiredAssessment->getSubject())->getId()]);
        $reservedWordsList = ProgrammingLanguageHelper::getReservedWords('cpp');
        $filesContent = [];

        $submittedAssessment = $supportedAssessmentRepository->findBy([
            'assessmentId' => $assessment,
            'submittedBy' => $this->getUser()]
        );

        if ($submittedAssessment == null) {
            $submittedAssessment = new SupportedAssessment();
            $submittedAssessment->setSubmittedBy($this->getUser());
            $submittedAssessment->setStartedAt(new \DateTime());
            $submittedAssessment->setAssessmentId($assessment);

            $supportedAssessmentRepository->save($submittedAssessment, true);
        }

        if ($subjectContent->getContentFile() != null) {
            $filesContent[$subjectContent->getId()] = stream_get_contents($subjectContent->getContentFile());
        } else {
            $filesContent[$subjectContent->getId()] = 'Nothing to show';
        }
        $requiredSubject[] = $subjectContent;

        $form = $this->createForm(SubmittedCodeFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('run')->isClicked()) {
                $data = ($form->get('codeArea')->getData());
                $compiler = new CompilerHelper($data);
                [$responseMessage, $compiledSuccessfully] = $compiler->makeApiCall();
            } elseif ($form->get('submit')->isClicked()) {
                $submittedCode = $form->get('codeArea')->getData();

                if ($subjectContent->getContentFile() != null) {
                    $submittedAssessment = $supportedAssessmentRepository->findBy([
                            'assessmentId' => $assessment,
                            'submittedBy' => $this->getUser()]
                    );

                    if ($submittedAssessment != null) {
                        $submittedAssessment[0]->setEndedAt(new \DateTime());
                        $startedAt = \DateTime::createFromFormat(
                            'Y-m-d H:i:s',
                            $submittedAssessment[0]->getStartedAt()->format('Y-m-d H:i:s')
                        );
                        $endedAt = \DateTime::createFromFormat(
                            'Y-m-d H:i:s',
                            $submittedAssessment[0]->getEndedAt()->format('Y-m-d H:i:s')
                        );
                        $timeSpent = $endedAt->diff($startedAt);
                        $seconds = $timeSpent->s + $timeSpent->i * 60 + $timeSpent->h * 3600 + $timeSpent->days * 86400;
                        $submittedAssessment[0]->setTimeSpent($seconds);

                        if ($submittedCode) {
                            $user = $this->getUser();
                            $newFilename = $user->getEmail()  . '-' . $subjectContent->getId()
                                . '.txt';
                            $submittedAssessment[0]->setFileName($newFilename);

                            $filesystem = new Filesystem();
                            $filesystem->dumpFile($newFilename, $submittedCode);
                            $submittedAssessment[0]->setContentFile(file_get_contents($newFilename));

                            $compiler = new CompilerHelper($submittedCode);
                            [$responseMessage, $compiledSuccessfully] = $compiler->makeApiCall();
                            if (!$compiledSuccessfully) {
                                $submittedAssessment[0]->setResultedResponse([
                                    'compiledSuccessfully' => $compiledSuccessfully,
                                    'error'=> 'Program did not compile successfully!',
                                    'errorMessage' => $responseMessage
                                ]);
                            } else {
                                $content = $filesContent[$requiredAssessment->getSubject()->getId()];
                                $structuredRequirements = StructuredRequirements::getStructuredRequirements($content);
                                $requirementsNo = count($structuredRequirements['requirements']);
                                $codeCheckResult = CodeCheckCLanguage::checkSubmittedRequirementsAnswers(
                                    $structuredRequirements['requirements'],
                                    $submittedCode
                                );
                                $submittedAssessment[0]->setResultedResponse([
                                    'compiledSuccessfully' => $compiledSuccessfully,
                                    'error'=> '',
                                    'errorMessage' => $codeCheckResult[0][1][0]['errors']
                                ]);
                                $maxGrade = 0;
                                foreach ($structuredRequirements['requirements'] as $key => $value) {
                                    $maxGrade += $value['score'];
                                }
                                $submittedAssessment[0]->setMaxGrade($maxGrade);
                            }

                            $supportedAssessmentRepository->save($submittedAssessment[0], true);
                        }
                    }

                } else {
//                to do: check for subjectContent string
                }

                return $this->redirectToRoute('app_show_submitted_assessment', [
                    'assessmentId' => $assessment,
//                    'submittedId' => $submittedAssessment[0]->getId()
                    'userId' => $this->getUser()->getIdentifierId()
                ]);
            }
        }
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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
        return $response;
    }

    #[Route('/home/assessments/assessment/{assessmentId}/submitted/{userId}', name: 'app_show_submitted_assessment')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function showSubmittedAssessment(
        Request $request,
        int $assessmentId,
        int $userId,
        SupportedAssessmentRepository $supportedAssessmentRepository,
        SubjectRepository $subjectRepository,
        AssessmentRepository $assessmentRepository,
        UserRepository $userRepository
    ): Response
    {
        $user = $userRepository->find($userId);
        $requiredAssessment = $assessmentRepository->findOneBy(['id' => $assessmentId]);
        $subjectContent = $subjectRepository->findOneBy(['id' => ($requiredAssessment->getSubject())->getId()]);
        $submittedAssessment = $supportedAssessmentRepository->findSubmittedAssessmentDetailed($assessmentId, $user);
        if ($subjectContent->getContentFile() != null) {
            $filesContent[$subjectContent->getId()] = stream_get_contents($subjectContent->getContentFile());
        } else {
            $filesContent[$subjectContent->getId()] = 'Nothing to show';
        }
        $content = $filesContent[$requiredAssessment->getSubject()->getId()];
        $structuredRequirements = StructuredRequirements::getStructuredRequirements($content);
        if (isset($submittedAssessment[0])) {
            $submittedFlag = true;
        } else {
            $submittedFlag = false;
        }

        return $this->render('assessment/show_submitted.html.twig',[
            'requiredAssessment' => $requiredAssessment,
            'structuredRequirements' => $structuredRequirements,
            'submittedAssessment' => $submittedAssessment[0] ?? null,
            'submittedCode' => $submittedFlag ? stream_get_contents($submittedAssessment[0]->getContentFile()) : ''
        ]);
    }

    #[Route('/home/assessments/assessment/show/results/{assessmentId}', name: 'app_assessment_all_results')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function showAllQuizResults(
        Request $request,
        int $assessmentId,
        CreatedQuizRepository $createdQuizRepository,
        AssessmentRepository $assessmentRepository,
        SupportedAssessmentRepository $supportedAssessmentRepository,
        SupportedQuizRepository $supportedQuizRepository,
        GroupRepository $groupRepository
    ): Response
    {
        $requiredAssessment = $assessmentRepository->getDetailedAssessment($assessmentId);
        $submittedAssessments = $supportedAssessmentRepository->getSupportedAssessmentsById($assessmentId);
        $groups = $groupRepository->findAll();

        if ($submittedAssessments != null) {
            return $this->render('assessment/show_all_assessment_results.html.twig',[
                'requiredAssessment' => $requiredAssessment[0],
                'submittedAssessments' => $submittedAssessments,
                'groups' => $groups,
            ]);
        } else {
            return $this->render('assessment/show_all_assessment_results.html.twig',[
                'submittedAssessments' => null,
            ]);
        }
    }

    #[Route('/home/assessment/subjects/show', name: 'app_show_subject')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function displaySubjects(
        Request $request,
        TeacherRepository $teacherRepository,
        SubjectRepository $subjectRepository,
    ): Response
    {
        $user = $this->getUser()->getIdentifierId();
        $teacher = $teacherRepository->getTeacher($user);
        $subjects = $subjectRepository->findBy(['issuedBy' => $teacher[0]->getId()]);

        return $this->render('assessment/subjects_show.html.twig', [
            'subjects' => $subjects
        ]);
    }

    #[Route('/home/assessment/subject/edit/{subjectId}', name: 'app_edit_subject')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function editSubject(
        Request $request,
        EntityManagerInterface $entityManager,
        TeacherRepository $teacherRepository,
        SluggerInterface $slugger,
        int $subjectId,
        SubjectRepository $subjectRepository
    ): Response
    {
        $subject = $subjectRepository->find($subjectId);
        $form = $this->createForm(SubjectFormType::class, $subject, [
            'subject_id' => $subject->getId(),
            'edit' => true,
        ]);
        $form->handleRequest($request);
//        to do: check the file uploading
        if ($form->isSubmitted() && $form->isValid()) {
            $subject = $form->getData();

            if ($form->getConfig()->getOption('edit')) {
                $subject->setDescription($subject->getDescription());
                $subject->setSubject($subject->getSubject());
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
                $subject->setLastModified(new \DateTime());
                $subjectRepository->save($subject, true);

                return $this->redirectToRoute('app_show_subject');
            }
        }

        return $this->render('assessment/add_subject.html.twig', [
            'addSubjectForm' => $form->createView()
        ]);
    }

    #[Route('/home/assessment/subject/delete/{subjectId}', name: 'app_delete_subject')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function deleteSubject(
        Request $request,
        EntityManagerInterface $entityManager,
        TeacherRepository $teacherRepository,
        SluggerInterface $slugger,
        int $subjectId,
        SubjectRepository $subjectRepository
    ): Response
    {
        $subject = $subjectRepository->find($subjectId);
        $subjectRepository->remove($subject, true);

        return $this->redirectToRoute('app_show_subject');
    }

    #[Route('/home/assessment/disable/{assessmentId}', name: 'app_assessment_disable')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function disableAssessment(
        Request $request,
        int $assessmentId,
        AssessmentRepository $assessmentRepository
    ): Response
    {
        $assessment = $assessmentRepository->find($assessmentId);
        $assessment->setStatus('Disabled');

        $assessmentRepository->save($assessment, true);

        return $this->redirectToRoute('app_show_assessments');
    }

    #[Route('/home/assessment/enable/{assessmentId}', name: 'app_assessment_enable')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function enableAssessment(
        Request $request,
        int $assessmentId,
        AssessmentRepository $assessmentRepository
    ): Response
    {
        $assessment = $assessmentRepository->find($assessmentId);
        $assessment->setStatus('Active');

        $assessmentRepository->save($assessment, true);

        return $this->redirectToRoute('app_show_assessments');
    }

}
