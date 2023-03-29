<?php

namespace App\Controller;

use App\Entity\Assessment;
use App\Entity\AssignedSubjects;
use App\Entity\QuizQuestion;
use App\Entity\Subject;
use App\Form\AssessmentFormType;
use App\Form\AssignedSubjectsFormType;
use App\Form\QuizQuestionsAddFormType;
use App\Form\QuizQuestionsFromFileFormType;
use App\Form\SubjectFormType;
use App\Form\SubmittedCodeFormType;
use App\Helper\CompilerHelper;
use App\Repository\AssessmentRepository;
use App\Repository\AssignedSubjectsRepository;
use App\Repository\GroupRepository;
use App\Repository\QuizQuestionRepository;
use App\Repository\StudentRepository;
use App\Repository\SubjectRepository;
use App\Repository\TeacherRepository;
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
            $assessment->setTimeLimit($form->get('timeLimit')->getData());
            $assessment->setTimeUnit($form->get('timeUnit')->getData());

            if ($form->get('requirementsNo')->getData() != 1) {
//                dd($form);
                $sectionsNo = $form->get('requirementsNo')->getData();
                $subjectsForm = $this->createForm(
                    AssignedSubjectsFormType::class,
                    [
                        'subjects' => $subjects,
                        'sectionsNo' => $sectionsNo
                    ]
                );

                $subjectsForm->handleRequest($request);
                if ($subjectsForm->isSubmitted() && $subjectsForm->isValid()) {
                    for ($i = 1; $i <= $sectionsNo; $i++) {
                        $assignedSubjects = new AssignedSubjects();
                        try {
                            $subjectsOptions = $subjectsForm->get('subjectList' . $i)->getData();
                            $lastAssessment = $assessmentRepository->findOneBy([], ['id' => 'DESC']);
                            if ($lastAssessment) {
                                $assessmentIdToAdd = $lastAssessment->getId() + 1;
                            } else {
                                $assessmentIdToAdd = 1;
                            }
                            $assignedSubjects->setAssessment($assessmentIdToAdd);
                            $assignedSubjects->setRequirementNo($i);
                            $options = [];
                            foreach ($subjectsArray as $key => $value) {
                                foreach ($subjectsOptions as $keySelected => $valueSelected)
                                if ($value->getDescription() == $valueSelected) {
                                    // TO DO: Do not allow subject have id 0
                                    $options[] = $subjectRepository->find($value->getId());
                                }
                            }
                            $assignedSubjects->setSubjectsOptionList($options);
                            $entityManager->persist($assignedSubjects);
                            $entityManager->flush();
                        } catch (\Exception $exception) {
                            dd($exception->getMessage());
                        }
                    }
                } else {

                    return $this->render('assessment/schedule_assessment.html.twig', [
                        'scheduleAssessmentForm' => $form->createView(),
                        'assignedSubjectsForm' => $subjectsForm->createView()
                    ]);
                }
            }
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
        $requirementsNo = $requiredAssessment->getRequirementsNo();
        $assignedSubjects = $assignedSubjectsRepository->findBy(['assessment' => $requiredAssessment->getId()]);

        $requiredSubjects = [];
        $filesContent = [];
        foreach ($assignedSubjects as $assignedSubject) {
            if (count($assignedSubject->getSubjectsOptionList()) > 1) {
                $randomKey = array_rand($assignedSubject->getSubjectsOptionList());
                $randomSubject[] = $assignedSubject->getSubjectsOptionList()[$randomKey];
                $assignedSubject->setSubjectsOptionListFromRand($randomSubject);
            }
            $subjectContent = $subjectRepository->findby(['id' => $assignedSubject->getSubjectsOptionList()[0]['id']]);
            $keyFile = $assignedSubject->getSubjectsOptionList()[0]['id'];

            if ($subjectContent[0]->getContentFile() != null) {
                $filesContent[$keyFile] = stream_get_contents($subjectContent[0]->getContentFile());
            } else {
                $filesContent[$keyFile] = 'Nothing to show';
            }
            $requiredSubjects[] = $subjectRepository->findOneBy(
                ['id' => $assignedSubject->getSubjectsOptionList()[0]['id']]
            );
        }

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
            'requiredSubjects' => $requiredSubjects,
            'filesContent' => $filesContent,
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

    #[Route('/home/assessment/quiz/questions', name: 'app_quiz_questions')]
    public function addQuizQuestions(
        Request $request,
        TeacherRepository $teacherRepository,
        QuizQuestionRepository $questionRepository,
        SluggerInterface $slugger
    ): Response
    {
        $quizQuestions = new QuizQuestion();
        $quizForm = $this->createForm(QuizQuestionsAddFormType::class, $quizQuestions);
        $quizForm->handleRequest($request);

        if ($quizForm->isSubmitted() && $quizForm->isValid()) {
            $userId = $this->getUser()->getIdentifierId();
            $teacher = $teacherRepository->getTeacher($userId);
            $quizQuestions->setCreatedAt(new \DateTime());
            $quizQuestions->setIssuedBy($teacher[0]);

            $questionRepository->save($quizQuestions, true);

            return $this->redirectToRoute('app_assessment');
        }

//        $quizQuestionsFromFile = new QuizQuestion();
        $quizQuestionsFromFileForm = $this->createForm(QuizQuestionsFromFileFormType::class);
        $quizQuestionsFromFileForm->handleRequest($request);

        if ($quizQuestionsFromFileForm->isSubmitted() && $quizQuestionsFromFileForm->isValid()) {
            $questionsCategory = $quizQuestionsFromFileForm['category']->getData();
            /** @var UploadedFile $questionsFile */
            $questionsFile = $quizQuestionsFromFileForm['contentFile']->getData();
            if ($questionsFile) {
                $originalFilename = pathinfo($questionsFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$questionsFile->guessExtension();

                $content = file_get_contents($questionsFile->getPathname());
                try {
                    $arrayContent = explode("\r\n\r\n", $content);
                    if (
                        $this->processFile(
                        $arrayContent, $newFilename, $teacherRepository, $questionsCategory, $questionRepository)
                    ) {
                        $this->addFlash(
                            'success',
                            'Saved successfully!'
                        );
                    };
                } catch (\Exception $exception) {
                    $this->addFlash(
                        'error',
                        $exception->getMessage()
                    );
                    $this->redirectToRoute('app_quiz_questions');
//                    dd($exception->getMessage());
                }
//                dd($content);
//                $questionsFromFile->setContentFile($content);
            }
        }

        return $this->render('assessment/quiz_add_questions.html.twig', [
            'quizQuestions' => $quizForm->createView(),
            'quizQuestionsFromFileForm' => $quizQuestionsFromFileForm->createView()
        ]);
    }

    /**
     * @throws \Exception
     */
    private function isValidContent(array $arrayContent): bool
    {
        if (count($arrayContent) < 1 ) {
            throw new \Exception('Invalid TXT file structure!'
                . 'Required structure: '
                . '    On separate lines:'
                .   'Question content, answer A, answer B, answer C, answer D, correct answer(full-text)'
                .   'Questions must be separated by an empty line!'
            );
        }
        foreach ($arrayContent as $key => $questionContent) {
            $arrayQuestionContent = explode("\r\n", $questionContent);
            if (count($arrayQuestionContent) != 6) {
                throw new \Exception('Invalid structure at question no ' . $key
                    . ' Required structure: '
                    . '    On separate lines:'
                    .   'Question content, answer A, answer B, answer C, answer D, correct answer(full-text)'
                    .   'Questions must be separated by an empty line!'
                );
            }
            foreach ($arrayQuestionContent as $keyQuestion => $valueQuestion) {
                if (strlen(trim($valueQuestion)) == 0) {
                    throw new \Exception('Invalid structure at question no ' . $key
                        . ': empty line found at position no: ' . $keyQuestion
                    );
                }
            }
            if (
                !in_array($arrayQuestionContent[5], array_slice($arrayQuestionContent, 1, 4, true))
            ) {
                throw new \Exception('Invalid value for correct answer at question no ' . $key
                    . '. The correct answer should match a previous given choice! '
                );
            }
        }
        return true;
    }

    /**
     * @throws \Exception
     */
    private function processFile(
        array $arrayContent,
        string $filename,
        TeacherRepository $teacherRepository,
        string $questionsCategory,
        QuizQuestionRepository $questionRepository
    ): bool {
        if ($this->isValidContent($arrayContent)) {
            if (
                $this->saveQuestions($arrayContent, $filename,  $teacherRepository, $questionsCategory, $questionRepository)
            ) {
                return true;
            }
        }

        return false;
    }

    private function saveQuestions(
        array $arrayContent,
        string $filename,
        TeacherRepository $teacherRepository,
        string $questionsCategory,
        QuizQuestionRepository $questionRepository
    ): bool
    {
        foreach ($arrayContent as $key => $questionContent) {
            $arrayQuestionContent = explode("\r\n", $questionContent);
            $question = new QuizQuestion();
            $question->setCategory($questionsCategory);
            foreach ($arrayQuestionContent as $keyQuestion => $valueQuestion) {
                if ($keyQuestion == 0) {
                    $question->setQuestionContent($valueQuestion);
                } elseif ($keyQuestion == 1) {
                    $question->setChoiceA($valueQuestion);
                } elseif ($keyQuestion == 2) {
                    $question->setChoiceB($valueQuestion);
                } elseif ($keyQuestion == 3) {
                    $question->setChoiceC($valueQuestion);
                } elseif ($keyQuestion == 4) {
                    $question->setChoiceD($valueQuestion);
                } elseif ($keyQuestion == 5) {
                    $question->setCorrectAnswer($valueQuestion);
                }
            }
            $userId = $this->getUser()->getIdentifierId();
            $teacher = $teacherRepository->getTeacher($userId);
            $question->setIssuedBy($teacher[0]);
            $question->setCreatedAt(new \DateTime());
            $question->setContentFile(implode("\r\n", $arrayContent));
            $question->setFileName($filename);

            $questionRepository->save($question, true);
        }
        return true;
    }
}
