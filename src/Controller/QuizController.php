<?php

namespace App\Controller;

use App\Entity\CreatedQuiz;
use App\Entity\QuizQuestion;
use App\Entity\SupportedQuiz;
use App\Entity\SupportedQuizDetails;
use App\Entity\User;
use App\Form\CreateQuizFormType;
use App\Form\QuizQuestionsAddFormType;
use App\Form\QuizQuestionsFromFileFormType;
use App\Form\StartQuizFormType;
use App\Repository\AssessmentRepository;
use App\Repository\AssignedSubjectsRepository;
use App\Repository\CreatedQuizRepository;
use App\Repository\GroupRepository;
use App\Repository\QuizQuestionRepository;
use App\Repository\StudentRepository;
use App\Repository\SubjectRepository;
use App\Repository\SupportedQuizDetailsRepository;
use App\Repository\SupportedQuizRepository;
use App\Repository\TeacherRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class QuizController extends AbstractController
{
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

            return $this->redirectToRoute('app_quiz_questions');
        }

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
                        return $this->redirectToRoute('app_quiz_questions');
                    };
                } catch (\Exception $exception) {
                    $this->addFlash(
                        'error',
                        $exception->getMessage()
                    );
                    $this->redirectToRoute('app_quiz_questions');
                }
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

    #[Route('/home/assessment/quiz/questions/show', name: 'app_quiz_questions_show')]
    public function displayQuizQuestions(
        Request $request,
        TeacherRepository $teacherRepository,
        QuizQuestionRepository $questionRepository,
    ): Response
    {
        $user = $this->getUser()->getIdentifierId();
        $teacher = $teacherRepository->getTeacher($user);
        $questions = $questionRepository->findBy(['issuedBy' => $teacher[0]->getId()]);

        return $this->render('quiz/quiz_questions_show.html.twig', [
            'questions' => $questions
        ]);
    }

    #[Route('/home/assessment/quiz/questions/show/filter', name: 'app_quiz_questions_filter')]
    public function filterQuizQuestions(
        Request $request,
        QuizQuestionRepository $questionRepository,
        TeacherRepository $teacherRepository,
        UserRepository $userRepository
    ): Response
    {
        if ($request->request->get('action') == 'Reset Filters') {
            return $this->redirectToRoute('app_quiz_questions_show');
        }
        $filters = [];
        $filters['id'] = $request->request->get('id') ?? '';
        $filters['category'] = $request->request->get('category') ?? '';
        $filters['optional_description'] = $request->request->get('optionalDescription') ?? '';
        $filters['question_content'] = $request->request->get('questionContent');
        $filters['choice_a'] = $request->request->get('choiceA') ?? '';
        $filters['choice_b'] = $request->request->get('choiceB') ?? '';
        $filters['choice_c'] = $request->request->get('choiceC') ?? '';
        $filters['choice_d'] = $request->request->get('choiceD') ?? '';
        $filters['correct_answer'] = $request->request->get('correctAnswer') ?? '';
        $filters['issued_by'] = $request->request->get('issuedBy') ?? '';
        if ($request->request->get('issuedBy')) {
            $user = $userRepository->findByEmail($request->request->get('issuedBy'));
            if ($user) {
                $teacherId = $teacherRepository->findBy([
                    'user' => $user[0]->getId()
                ]);
                $filters['issued_by'] = $teacherId;
            }
        }
        $filteredQuestions = $questionRepository->filterQuestions($filters);

        return $this->render('quiz/quiz_questions_show.html.twig', [
            'questions' => $filteredQuestions
        ]);
    }

    #[Route('/home/assessment/quiz/create', name: 'app_quiz_create')]
    public function createQuiz(
        Request $request,
        QuizQuestionRepository $questionRepository,
        TeacherRepository $teacherRepository,
        UserRepository $userRepository,
        CreatedQuizRepository $createdQuizRepository
    ): Response
    {
        $createdQuiz = new CreatedQuiz();
        $userId = $this->getUser()->getIdentifierId();
        $teacher = $teacherRepository->getTeacher($userId);
        $teacherAssignedGroups = $teacher[0]->getAssignedGroups();
        $quizQuestions = $questionRepository->findBy(['issuedBy' => $teacher[0]->getId()]);
        $quizCategories = [];

        foreach ($quizQuestions as $question) {
            $category = $question->getCategory();
            if (!in_array($category, $quizCategories)) {
                $quizCategories[] = $category;
            }
        }

        $form = $this->createForm(
            CreateQuizFormType::class,
            $createdQuiz,
            [
                'teacherAssignedGroups' => $teacherAssignedGroups,
                'quizCategories' => $quizCategories
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$form->get('save')->isClicked()) {
                $selectedCategory = $form->get('category')->getData();
                $selectedSource = $form->get('questionsSource')->getData();
                if ($selectedSource == 'Mixed') {
                    $quizQuestions = $questionRepository->findBy(['issuedBy' => $teacher[0]->getId()]);
                } elseif (strlen($selectedCategory) != 0 ) {
                    $quizQuestions = $questionRepository->findBy(['category' => $selectedCategory]);
                }

                return $this->render('quiz/quiz_create.html.twig', [
                    'createQuiz' => $form->createView(),
                    'questions' => $quizQuestions
                ]);
            } else {
                $createdQuiz->setDescription($form->get('description')->getData());
                $createdQuiz->setCreatedAt(new \DateTime());
                $createdQuiz->setStartAt($form->get('startAt')->getData());
                $createdQuiz->setEndAt($form->get('endAt')->getData());
                $createdQuiz->setTimeLimit($form->get('timeLimit')->getData());
                $createdQuiz->setTimeUnit($form->get('timeUnit')->getData());
                $createdQuiz->setAssigneeGroup($form->get('assigneeGroup')->getData());
                $createdQuiz->setQuestionsNo($form->get('questionsNo')->getData());

//                first scenario
//                only for manually selected questions
                $selectedQuestionsIds = json_decode($request->request->get('questionsIdsFromSelect'), true);
                $postedScores = json_decode($request->request->get('questionsScores'), true);
//                dd($postedScores);
                $scores = [];
                foreach ($postedScores as $k => $value) {
                    if (is_array($value)) {
                        foreach ($value as $key => $score) {
                            $scores[$key] = $score;
                        }
                    }
                }

                $questionsScores = [];
                $postedPointsTotal = 0;
                $questionsWithScore = 0;
                $questionsNo = count($selectedQuestionsIds);
                foreach ($scores as $keyScore => $scoreValue) {
                    if (strlen(trim($scoreValue)) > 0) {
                        $postedPointsTotal += (int)$scoreValue;
                        $questionsWithScore++;
                    }
                }
                $remainedPoints = 100 - $postedPointsTotal;
                $scorePerQuestion = $remainedPoints / (count($selectedQuestionsIds) - $questionsWithScore);
                foreach ($selectedQuestionsIds as $questionsId) {
                    $found = false;
                    foreach ($scores as $keyScore => $scoreValue) {
                        if ($questionsId == $keyScore) {
                            $questionsScores[$questionsId] = (int)$scoreValue;
                            $found = true;
                        }
                    }
                    if (!$found) {
                        $questionsScores[$questionsId] = $scorePerQuestion;
                    }
                }

                $createdQuiz->setQuestionsList($questionsScores);
                $createdQuiz->setCategory($form->get('category')->getData());
                $createdQuiz->setCreatedBy($teacher[0]);
                $createdQuiz->setMaxGrade($form->get('maxGrade')->getData());
                $createdQuiz->setMaxPoints(100);
                $createdQuiz->setQuestionsSource($form->get('questionsSource')->getData());

                $createdQuizRepository->save($createdQuiz, true);

                return $this->redirectToRoute('app_assessment');
            }

        } else {

            return $this->render('quiz/quiz_create.html.twig', [
                'createQuiz' => $form->createView(),
                'questions' => $quizQuestions
            ]);
        }
    }

    #[Route('/home/assessments/quizzes/show', name: 'app_show_quizzes')]
    public function showQuizzes(
        Request $request,
        EntityManagerInterface $entityManager,
        QuizQuestionRepository $quizQuestionRepository,
        CreatedQuizRepository $createdQuizRepository,
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

            $quizzes = $createdQuizRepository->getQuizzesByGroupNo($groupNo);
        } elseif (in_array('ROLE_TEACHER', $this->getUser()->getRoles())) {
            $isStudent = false;
            $teacher = $teacherRepository->getGroupByUserId($userId)[0];
            $groupId = $teacher->getAssignedGroups();
            $quizzes = $createdQuizRepository->getCreatedQuizzesByIssuerId($teacher->getId());
        }

        return $this->render('quiz/created_quiz_show.html.twig', [
            'quizzesList' => $quizzes
        ]);
    }

    #[Route('/home/assessments/startQuiz/{quiz}', name: 'app_start_quiz')]
    public function startQuiz(
        Request $request,
        int $quiz,
        CreatedQuizRepository $createdQuizRepository,
        QuizQuestionRepository $quizQuestionRepository,
        SupportedQuizRepository $supportedQuizRepository,
        SupportedQuizDetailsRepository $detailsRepository
    ): Response
    {
        $requiredQuiz = $createdQuizRepository->findOneBy(['id' => $quiz]);
        $supportedQuiz = $supportedQuizRepository->findOneBy(['quiz' => $quiz, 'supportedBy' => $this->getUser()]);
        if ($supportedQuiz == null) {
            $supportedQuiz = new SupportedQuiz();
            $supportedQuiz->setStartedAt(new \DateTime());
            $supportedQuiz->setMaxGrade($requiredQuiz->getMaxGrade());
            $user = $this->getUser();
            $supportedQuiz->setSupportedBy($user);
            $supportedQuiz->setQuiz($quiz);

            $supportedQuizRepository->save($supportedQuiz, true);
        }


        //        get all questionsIds
        $questionsIds = implode(',', array_keys($requiredQuiz->getQuestionsList()));
        $questions = $quizQuestionRepository->getQuestionsByIds($questionsIds);
        $index = 0;
        $remainingTime = $requiredQuiz->getTimeLimit() * 60;
        $totalQuestions = count($questions) - 1;
        $form = $this->createForm(
            StartQuizFormType::class,
            [
                'questions' => $questions
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            dd('here');
            if ($form->get('next')->isClicked()) {
                $remainingTime = $request->request->get('remainingTime');
                $secondsSpent = $request->request->get('seconds-spent');
                $index = $request->request->get('index');
                $question = new SupportedQuizDetails();
                $question->setQuizId($requiredQuiz->getId());
                $question->setQuestionId($questions[$index]->getId());
                $correctAnswer = $questions[$index]->getCorrectAnswer();
                $question->setCorrectAnswer($correctAnswer);
                $postedOption = $request->request->get('answerOption');
                $question->setProvidedAnswer($postedOption);
                $question->setQuestionScore((int)$requiredQuiz->getQuestionsList()[$questions[$index]->getId()]);
                if ($postedOption == $correctAnswer) {
                    $question->setObtainedScore($question->getQuestionScore());
                } else {
                    $question->setObtainedScore(0);
                }
                $question->setTimeSpent($secondsSpent);
                /**@var User $user*/
                $user = $this->getUser();

                $question->setSupportedByStudent($user);
                $detailsRepository->save($question, true);
                $index++;
                if ($index <= $totalQuestions) {
                    return $this->render('quiz/start_quiz.html.twig', [
                        'requiredQuiz' => $requiredQuiz,
                        'question' => $questions[$index],
                        'index' => $index,
                        'remainingTime' => $remainingTime,
                        'supportQuiz' => $form->createView()
                    ]);
                } else {
                    $supportedQuiz = $supportedQuizRepository->findOneBy(['quiz' => $quiz, 'supportedBy' => $this->getUser()]);
                    if ($supportedQuiz != null) {
                        $supportedQuiz->setEndedAt(new \DateTime(''));
                        $startedAt = \DateTime::createFromFormat('Y-m-d H:i:s', $supportedQuiz->getStartedAt()->format('Y-m-d H:i:s'));
                        $endedAt = \DateTime::createFromFormat('Y-m-d H:i:s', $supportedQuiz->getEndedAt()->format('Y-m-d H:i:s'));
                        $timeSpent = $endedAt->diff($startedAt);
                        $seconds = $timeSpent->s + $timeSpent->i * 60 + $timeSpent->h * 3600 + $timeSpent->days * 86400;
                        $supportedQuiz->setTotalTimeSpent($seconds);
                        $submittedQuestions = $detailsRepository->getTotalObtainedScore(
                            $requiredQuiz->getId(),
                            $user
                        );
                        $totalScore = 0;
                        foreach ($submittedQuestions as $key => $submittedQuestion) {
                            $totalScore += $submittedQuestion->getObtainedScore();
                        }
                        $maxGrade = $requiredQuiz->getMaxGrade();
                        $resultedGrade = ($totalScore * $maxGrade) / 100;
                        $supportedQuiz->setObtainedGrade(number_format($resultedGrade, 2));

                        $supportedQuizRepository->save($supportedQuiz, true);

                        return $this->redirectToRoute('app_show_submitted_one', [
                            'quiz' => $requiredQuiz->getId(),
                            'id' => $supportedQuiz->getId()
                        ]);
                    }
                }

            } elseif ($form->get('submit')->isClicked()) {
                $supportedQuiz = $supportedQuizRepository->findOneBy(['quiz' => $quiz, 'supportedBy' => $this->getUser()]);
                if ($supportedQuiz != null) {
                    $supportedQuiz->setEndedAt(new \DateTime(''));
                    $startedAt = \DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        $supportedQuiz->getStartedAt()->format('Y-m-d H:i:s')
                    );
                    $endedAt = \DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        $supportedQuiz->getEndedAt()->format('Y-m-d H:i:s')
                    );
                    $timeSpent = $endedAt->diff($startedAt);
                    $seconds = $timeSpent->s + $timeSpent->i * 60 + $timeSpent->h * 3600 + $timeSpent->days * 86400;
                    $supportedQuiz->setTotalTimeSpent($seconds);
                    $submittedQuestions = $detailsRepository->getTotalObtainedScore(
                        $requiredQuiz->getId(),
                        $user
                    );
                    $totalScore = 0;
                    foreach ($submittedQuestions as $key => $submittedQuestion) {
                        $totalScore += $submittedQuestion->getObtainedScore();
                    }
                    $maxGrade = $requiredQuiz->getMaxGrade();
                    $resultedGrade = ($totalScore * $maxGrade) / 100;
                    $supportedQuiz->setObtainedGrade(number_format($resultedGrade, 2));

                    $supportedQuizRepository->save($supportedQuiz, true);

                    return $this->redirectToRoute('app_show_submitted_one', [
                        'quiz' => $requiredQuiz->getId(),
                        'id' => $supportedQuiz->getId()
                    ]);
                }
            }
        }

        return $this->render('quiz/start_quiz.html.twig', [
            'requiredQuiz' => $requiredQuiz,
            'question' => $questions[$index],
            'index' => $index,
            'remainingTime' => $remainingTime,
            'supportQuiz' => $form->createView()
        ]);
    }

    #[Route('/home/assessments/quiz/{quiz}/submitted{id}', name: 'app_show_submitted_one')]
    public function showSubmittedQuiz(
        Request $request,
        int $quiz,
        int $id,
        CreatedQuizRepository $createdQuizRepository,
        QuizQuestionRepository $quizQuestionRepository,
        SupportedQuizRepository $supportedQuizRepository,
        SupportedQuizDetailsRepository $detailsRepository
    ): Response
    {
        $requiredQuiz = $createdQuizRepository->find($quiz);
        $questionsIds = implode(',', array_keys($requiredQuiz->getQuestionsList()));
        $questions = $quizQuestionRepository->getQuestionsByIds($questionsIds);
        $user = $this->getUser();
        $submittedQuiz = $supportedQuizRepository->find($id);
        $submittedQuestions = $detailsRepository->getTotalObtainedScore(
            $requiredQuiz->getId(),
            $user
        );
        $totalScore = 0;
        foreach ($submittedQuestions as $key => $submittedQuestion) {
            $totalScore += $submittedQuestion->getObtainedScore();
        }

        return $this->render('quiz/show_one_submitted.html.twig',[
            'requiredQuiz' => $requiredQuiz,
            'submittedQuiz' => $submittedQuiz,
            'submittedQuestions' => $submittedQuestions,
            'score' => $totalScore,
            'questions' => $questions
        ]);
    }

    #[Route('/home/assessments/quiz/results/{quiz}', name: 'app_quiz_view_one_result')]
    public function showResultsQuiz(
        Request $request,
        int $quiz,
        CreatedQuizRepository $createdQuizRepository,
        QuizQuestionRepository $quizQuestionRepository,
        SupportedQuizRepository $supportedQuizRepository,
        SupportedQuizDetailsRepository $detailsRepository
    ): Response
    {
        $requiredQuiz = $createdQuizRepository->find($quiz);
        $submittedQuiz = $supportedQuizRepository->findBy([
            'supportedBy' => $this->getUser()->getIdentifierId(),
            'quiz' => $quiz
        ]);

        if ($submittedQuiz != null) {
            $questionsIds = implode(',', array_keys($requiredQuiz->getQuestionsList()));
            $questions = $quizQuestionRepository->getQuestionsByIds($questionsIds);
            $user = $this->getUser();
            $submittedQuestions = $detailsRepository->getTotalObtainedScore(
                $requiredQuiz->getId(),
                $user
            );
            $totalScore = 0;
            foreach ($submittedQuestions as $key => $submittedQuestion) {
                $totalScore += $submittedQuestion->getObtainedScore();
            }

            return $this->render('quiz/show_one_submitted.html.twig',[
                'requiredQuiz' => $requiredQuiz,
                'submittedQuiz' => $submittedQuiz[0],
                'submittedQuestions' => $submittedQuestions,
                'score' => $totalScore,
                'questions' => $questions
            ]);
        } else {
            return $this->render('quiz/show_one_submitted.html.twig',[
                'submittedQuiz' => $submittedQuiz,
            ]);
        }
    }
}
