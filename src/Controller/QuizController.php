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
use App\Repository\CreatedQuizRepository;
use App\Repository\GroupRepository;
use App\Repository\QuizQuestionRepository;
use App\Repository\StudentRepository;
use App\Repository\SupportedQuizDetailsRepository;
use App\Repository\SupportedQuizRepository;
use App\Repository\TeacherRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class QuizController extends AbstractController
{
    #[Route('/home/assessment/quiz/questions', name: 'app_quiz_questions')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function addQuizQuestions(
        Request $request,
        TeacherRepository $teacherRepository,
        QuizQuestionRepository $questionRepository,
        SluggerInterface $slugger
    ): Response
    {
        $quizQuestions = new QuizQuestion();
        $quizForm = $this->createForm(QuizQuestionsAddFormType::class, $quizQuestions, [
            'edit' => false
        ]);
        $quizForm->handleRequest($request);

        if ($quizForm->isSubmitted() && $quizForm->isValid()) {
            $userId = $this->getUser()->getIdentifierId();
            $teacher = $teacherRepository->getTeacher($userId);
            $quizQuestions->setCreatedAt(new \DateTime());
            $quizQuestions->setIssuedBy($teacher[0]);
            if (!$quizForm->get('choiceD')) {
                $quizQuestions->setChoiceD('');
            }

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
                    $questions = $this->processAikenFormat($questionsFile);
                    if (count($questions) > 0) {
                        $this->saveAikenQuestions($questions, $questionsCategory, $questionRepository, $teacherRepository);
                        $this->addFlash(
                            'success',
                            'Saved successfully!'
                        );
                        return $this->redirectToRoute('app_quiz_questions');
                    } else {
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
                    }

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
                .   'Question content, answer A, answer B, answer C, correct answer(full-text)'
                .   'Questions must be separated by an empty line!'
            );
        }
        foreach ($arrayContent as $key => $questionContent) {
            $arrayQuestionContent = explode("\r\n", $questionContent);
            if (count($arrayQuestionContent) != 5) {
                throw new \Exception('Invalid structure at question no ' . $key
                    . ' Required structure: '
                    . '    On separate lines:'
                    .   'Question content, answer A, answer B, answer C, correct answer(full-text)'
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
                !in_array($arrayQuestionContent[4], array_slice($arrayQuestionContent, 1, 3, true))
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
                    $question->setCorrectAnswer($valueQuestion);
                }
            }
            $question->setChoiceD('');
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function createQuiz(
        Request $request,
        QuizQuestionRepository $questionRepository,
        TeacherRepository $teacherRepository,
        UserRepository $userRepository,
        CreatedQuizRepository $createdQuizRepository,
        QuizQuestionRepository $quizQuestionRepository
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
            $selectedCategory = $form->get('category')->getData();
            $selectedSource = $form->get('questionsSource')->getData();
            if (!$form->get('save')->isClicked()) {
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
                if ($form->get('practiceQuiz')->getData() == 'Yes') {
                    $createdQuiz->setPracticeQuiz(true);
                } else {
                    $createdQuiz->setPracticeQuiz(false);
                }

//                first scenario
//                only for manually selected questions
                $questionsScores = [];
                $postedPointsTotal = 0;
                $questionsWithScore = 0;
                if ($selectedSource == 'Selected Questions') {
                    $selectedQuestionsIds = json_decode($request->request->get('questionsIdsFromSelect'), true);
                    $postedScores = json_decode($request->request->get('questionsScores'), true);
                    $scores = [];
                    if ($postedScores) {
                        foreach ($postedScores as $k => $value) {
                            if (is_array($value)) {
                                foreach ($value as $key => $score) {
                                    $scores[$key] = $score;
                                }
                            }
                        }
                        foreach ($scores as $keyScore => $scoreValue) {
                            if (strlen(trim($scoreValue)) > 0) {
                                $postedPointsTotal += (int)$scoreValue;
                                $questionsWithScore++;
                            }
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

                } elseif (
                    $selectedSource == 'Random from Category'
                    && $selectedCategory
                ) {
                    //        retrieve random questions from Category
                    $questionsNo = $form->get('questionsNo')->getData();
                    $questionsIdsByCategory = $quizQuestionRepository->getQuestionsIdsByCategory($selectedCategory);
                    $randomIds = $this->getRandomIdsFromArray($questionsNo, $questionsIdsByCategory);

                    $remainedPoints = 100;
                    $scorePerQuestion = $remainedPoints / $questionsNo;
                    foreach ($randomIds as $questionsId) {
                            $questionsScores[$questionsId] = $scorePerQuestion;
                    }
                    $createdQuiz->setQuestionsList($questionsScores);
                } elseif ($selectedSource == 'Mixed') {
                    $selectedQuestionsIds = json_decode($request->request->get('questionsIdsFromSelect'), true);
                    if ($selectedQuestionsIds) {
                        $postedScores = json_decode($request->request->get('questionsScores'), true);
                        $scores = [];
                        if ($postedScores) {
                            foreach ($postedScores as $k => $value) {
                                if (is_array($value)) {
                                    foreach ($value as $key => $score) {
                                        $scores[$key] = $score;
                                    }
                                }
                            }
                            foreach ($scores as $keyScore => $scoreValue) {
                                if (strlen(trim($scoreValue)) > 0) {
                                    $postedPointsTotal += (int)$scoreValue;
                                    $questionsWithScore++;
                                }
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
                    } else {
                        $questionsNo = $form->get('questionsNo')->getData();
                        $questionsIds = $quizQuestionRepository->getQuestionsIds($selectedCategory);
                        $randomIds = $this->getRandomIdsFromArray($questionsNo, $questionsIds);

                        $remainedPoints = 100;
                        $scorePerQuestion = $remainedPoints / $questionsNo;
                        foreach ($randomIds as $questionsId) {
                            $questionsScores[$questionsId] = $scorePerQuestion;
                        }
                        $createdQuiz->setQuestionsList($questionsScores);
                    }
                }

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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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
            if (!$groupId) {
                return $this->render('quiz/created_quiz_show.html.twig', [
                    'notAssignedToAGroup' => true
                ]);
            }
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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
        } elseif (
            $supportedQuiz->getObtainedGrade() != null
            && !$requiredQuiz->getPracticeQuiz()
        ) {
            return $this->render('quiz/start_quiz.html.twig', [
                'practiceQuiz' => false
            ]);
        }

//        dd($requiredQuiz);
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
            /**@var User $user*/
            $user = $this->getUser();
            $index = $request->request->get('index');

            if ($form->get('next')->isClicked()) {
//                dd($requiredQuiz);
                $questionAlreadyExist = $detailsRepository->findBy([
                        'quizId' => $requiredQuiz->getId(),
                        'supportedByStudent' => $user,
                        'questionId' => $questions[$index]->getId()
                    ]
                );
                if (
                    $requiredQuiz->getPracticeQuiz() == false
                    && count($questionAlreadyExist) != 0) {
//                    dd($questionAlreadyExist);
                    return $this->render('quiz/start_quiz.html.twig', [
                        'requiredQuiz' => $requiredQuiz,
                        'question' => $questions[$index],
                        'index' => $index,
                        'remainingTime' => $remainingTime,
                        'supportQuiz' => $form->createView()
                    ]);
                }
                $question = new SupportedQuizDetails();
                if ($remainingTime) {
                    $remainingTime = $request->request->get('remainingTime');
                    $secondsSpent = $request->request->get('seconds-spent');
                    if (!is_int($secondsSpent)) {
                        $question->setTimeSpent(1);
                    } else {
                        $question->setTimeSpent($secondsSpent);
                    }
                }
                $index = $request->request->get('index');
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
                $question = new SupportedQuizDetails();
                if ($remainingTime) {
                    $remainingTime = $request->request->get('remainingTime');
                    $secondsSpent = $request->request->get('seconds-spent');
                    $question->setTimeSpent($secondsSpent);
                }
                $index = $request->request->get('index');
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

                $question->setSupportedByStudent($user);
                $detailsRepository->save($question, true);
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
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

    #[Route('/home/assessments/quiz/results/{quiz}/{user}', name: 'app_quiz_view_one_result')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function showResultsQuiz(
        Request $request,
        int $quiz,
        int $user,
        CreatedQuizRepository $createdQuizRepository,
        QuizQuestionRepository $quizQuestionRepository,
        SupportedQuizRepository $supportedQuizRepository,
        SupportedQuizDetailsRepository $detailsRepository,
        UserRepository $userRepository
    ): Response
    {
        $requiredQuiz = $createdQuizRepository->find($quiz);
        $userDetails = $userRepository->find($user);
        $submittedQuiz = $supportedQuizRepository->findBy([
            'supportedBy' => $userDetails,
            'quiz' => $quiz
        ]);

        if ($submittedQuiz != null) {
            $questionsIds = implode(',', array_keys($requiredQuiz->getQuestionsList()));
            $questions = $quizQuestionRepository->getQuestionsByIds($questionsIds);
            $submittedQuestions = $detailsRepository->getTotalObtainedScore(
                $requiredQuiz->getId(),
                $userDetails
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

    #[Route('/home/assessments/quiz/show/results/{quiz}', name: 'app_quiz_all_results')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function showAllQuizResults(
        Request $request,
        int $quiz,
        CreatedQuizRepository $createdQuizRepository,
        SupportedQuizRepository $supportedQuizRepository,
        GroupRepository $groupRepository
    ): Response
    {
        $requiredQuiz = $createdQuizRepository->find($quiz);
        $submittedQuizzes = $supportedQuizRepository->getSupportedQuizzesById($quiz);
        $groups = $groupRepository->findAll();

        if ($submittedQuizzes != null) {
            return $this->render('quiz/show_all_quiz_results.html.twig',[
                'requiredQuiz' => $requiredQuiz,
                'submittedQuizzes' => $submittedQuizzes,
                'groups' => $groups,
            ]);
        } else {
            return $this->render('quiz/show_all_quiz_results.html.twig',[
                'submittedQuiz' => $submittedQuizzes,
            ]);
        }
    }

    #[Route('/home/assessments/quiz/show/students-results/{quiz}/group/{groupNo}', name: 'app_quiz_students_results_per_group')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function showAllQuizResultsByGroup(
        Request $request,
        int $quiz,
        int $groupNo,
        CreatedQuizRepository $createdQuizRepository,
        SupportedQuizRepository $supportedQuizRepository,
        GroupRepository $groupRepository
    ): Response
    {
        $requiredQuiz = $createdQuizRepository->find($quiz);
        $submittedQuizzes = $supportedQuizRepository->getSupportedQuizzesByGroupNo($quiz, $groupNo);
        $groups = $groupRepository->findAll();
//        dd($submittedQuizzes);
        if ($submittedQuizzes != null) {
            return $this->render('quiz/show_all_quiz_results.html.twig',[
                'requiredQuiz' => $requiredQuiz,
                'submittedQuizzes' => $submittedQuizzes,
                'groups' => $groups,
            ]);
        } else {
            return $this->render('quiz/show_all_quiz_results.html.twig',[
                'submittedQuiz' => $submittedQuizzes,
            ]);
        }
    }

    #[Route('/home/assessments/quiz/{quizId}/download/timeSpentReport/', name: 'app_quiz_download_time_spent_report')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_STUDENT')]
    public function downloadTimeSpentQuizReport(
        Request $request,
        int $quizId,
        CreatedQuizRepository $createdQuizRepository,
        QuizQuestionRepository $quizQuestionRepository,
        SupportedQuizDetailsRepository $detailsRepository
    ): Response
    {
        $requiredQuiz = $createdQuizRepository->find($quizId);
        $questionsIds = implode(',', array_keys($requiredQuiz->getQuestionsList()));
        $questions = $quizQuestionRepository->getQuestionsByIds($questionsIds);

        $questionsWithTimeSpent = $detailsRepository->getDetailedSupportedQuizByQuiz($quizId);

        $arrayReport = [];
        foreach ($questions as $key => $question) {
            foreach ($questionsWithTimeSpent as $k => $questionTimeSpent) {
                if ($question->getId() == $questionTimeSpent['questionId']) {
                    $tmpQuestion = [];
                    $tmpQuestion['id'] = $question->getId();
                    $tmpQuestion['questionName'] = $question->getQuestionContent();
                    $tmpQuestion['answerA'] = substr($question->getChoiceA(), 3);
                    $tmpQuestion['answerB'] = substr($question->getChoiceB(), 3);
                    $tmpQuestion['answerC'] = substr($question->getChoiceC(), 3);
                    $tmpQuestion['correctAnswer'] = substr($question->getCorrectAnswer(), 3);
                    $tmpQuestion['timeSpent'] = $questionTimeSpent['averageTime'];

                    $arrayReport[] = $tmpQuestion;
                }
            }
        }

        $jsonReport = json_encode($arrayReport, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $fileName = 'TimeSpentReport.txt';
        $fileContent = $jsonReport;
        $response = new Response($fileContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }

    #[Route('/home/assessments/quiz/{quizId}/download/submitted/group/{groupNo}/report', name: 'app_quiz_download_submitted_quiz_report_group')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_STUDENT')]
    public function downloadSubmittedQuizGroupReport(
        Request $request,
        int $quizId,
        int $groupNo,
        CreatedQuizRepository $createdQuizRepository,
        QuizQuestionRepository $quizQuestionRepository,
        SupportedQuizDetailsRepository $detailsRepository,
        SupportedQuizRepository $supportedQuizRepository,
        GroupRepository $groupRepository
    ): Response
    {
        $requiredQuiz = $createdQuizRepository->find($quizId);
        $submittedQuizzes = $supportedQuizRepository->getSupportedQuizzesByGroupNo($quizId, $groupNo);

        $headers = array(
            'Unique identifier',
            'Student id',
            'Group',
            'Email',
            'Name',
            'Started at',
            'Ended at',
            'Grade',
            'Spent time'
        );

        $stream = fopen('php://memory', 'w');
        fputcsv($stream, $headers);

        foreach ($submittedQuizzes as $key => $submittedQuiz) {
            $name = '';
            if ($submittedQuiz->getSupportedBy() !== null) {
                $userProfile = $submittedQuiz->getSupportedBy()->getUserProfile();

                if ($userProfile !== null && $userProfile->getFirstName() !== null) {
                    $name = $userProfile->getFirstName() . ' ' . $userProfile->getLastName();
                }
            }
            $timeSpent = (intval($submittedQuiz->getTotalTimeSpent() / 60)) . ':'
                . ($submittedQuiz->getTotalTimeSpent() % 60);

            $rowData = array(
                $submittedQuiz->getId(),
                $submittedQuiz->getSupportedBy()->getId(),
                $groupNo,
                $submittedQuiz->getSupportedBy()->getEmail(),
                $name,
                ($submittedQuiz->getStartedAt())->format('Y-m-d h:i:s'),
                ($submittedQuiz->getEndedAt())->format('Y-m-d h:i:s'),
                $submittedQuiz->getObtainedGrade(),
                $timeSpent
            );
            fputcsv($stream, $rowData);
        }

        rewind($stream);

        $fileName = 'Quiz-' . $quizId . '-' . $requiredQuiz->getDescription() . '-report.csv';
        $response = new StreamedResponse();
        $response->setCallback(function () use ($stream) {
            fpassthru($stream);
        });
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '";');

        return $response;
    }

    #[Route('/home/assessments/quiz/question/delete/{questionId}', name: 'app_delete_question')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function deleteQuestion(
        Request $request,
        int $questionId,
        QuizQuestionRepository $quizQuestionRepository
    ): Response
    {
        $question = $quizQuestionRepository->find($questionId);
        $quizQuestionRepository->remove($question, true);

        return $this->redirectToRoute('app_quiz_questions_show');
    }

    #[Route('/home/assessments/quiz/question/edit/{questionId}', name: 'app_edit_question')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function editQuestion(
        Request $request,
        int $questionId,
        QuizQuestionRepository $quizQuestionRepository
    ): Response
    {
        $question = $quizQuestionRepository->find($questionId);
        $form = $this->createForm(QuizQuestionsAddFormType::class, $question, [
            'question_id' => $question->getId(),
            'edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData();

            if ($form->getConfig()->getOption('edit')) {
                $quizQuestionRepository->save($question, true);
            }

            return $this->redirectToRoute('app_quiz_questions_show');
        }

        return $this->render('assessment/quiz_add_questions.html.twig', [
            'quizQuestions' => $form->createView(),
//            'quizQuestionsFromFileForm' => $quizQuestionsFromFileForm->createView()
        ]);
    }

    private function getRandomIdsFromArray(int $questionsNo, array $questionsIdsByCategory): array
    {
        $randomIds = [];
        while (count($randomIds) < $questionsNo) {
            $randomIndex = rand(0, count($questionsIdsByCategory) - 1);
            $randomId = $questionsIdsByCategory[$randomIndex];
            if (!in_array($randomId, $randomIds)) {
                $randomIds[] = $randomId;
            }
        }

        return $randomIds;
    }

    #[Route('/home/assessments/quiz/reports', name: 'app_quiz_reports')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function showQuizReports(
        Request $request,
        QuizQuestionRepository $quizQuestionRepository,
        CreatedQuizRepository $createdQuizRepository,
        StudentRepository $studentRepository,
        TeacherRepository $teacherRepository,
        GroupRepository $groupRepository,
        SupportedQuizRepository $supportedQuizRepository
    ): Response
    {
        $userId = $this->getUser()->getIdentifierId();
        $teacher = $teacherRepository->getGroupByUserId($userId)[0];
        $groupId = $teacher->getAssignedGroups();
        $quizzes = $createdQuizRepository->getCreatedQuizzesByIssuerId($teacher->getId());
        $quizzesIds = [];
        foreach ($quizzes as $item) {
            $quizzesIds[] = $item->getId();
        }

        $submittedQuizzes = $supportedQuizRepository->findAll();
        $quizzesExtraData = $this->processQuizzes($quizzes, $submittedQuizzes);
//        dd($quizzesExtraData);

        return $this->render('quiz/quiz_reports_show_all.html.twig', [
            'quizzesList' => $quizzes,
            'quizzesExtraData' => $quizzesExtraData
        ]);
    }

    #[Route('/home/assessments/quiz/reports/{quiz}/perGroup', name: 'app_quiz_group_report')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function showQuizReportPerGroup(
        Request $request,
        int $quiz,
        CreatedQuizRepository $createdQuizRepository,
        SupportedQuizRepository $supportedQuizRepository
    ): Response
    {
        $requiredQuiz = $createdQuizRepository->find($quiz);
        $assignedGroups = $requiredQuiz->getAssigneeGroup();
        $submittedQuizzes = $supportedQuizRepository->getSupportedQuizzesById($quiz);

        if ($submittedQuizzes != null && $assignedGroups !=  null) {
            $groupData = $this->processSubmittedQuizzes($quiz, $submittedQuizzes, $assignedGroups);
            $groupResultComparative = $this->getComparativeResult($groupData);
            $groupPassPercent = $this->getPassPercent($groupData, $requiredQuiz->getMaxGrade());

            return $this->render('quiz/reports_per_group.html.twig',[
                'requiredQuiz' => $requiredQuiz,
                'submittedQuizzes' => $submittedQuizzes,
                'groupData' => $groupData,
                'groupResultComparative' => $groupResultComparative,
                'groupPassPercent' => $groupPassPercent
            ]);
        } else {
            return $this->render('quiz/reports_per_group.html.twig',[
                'requiredQuiz' => $requiredQuiz,
                'submittedQuizzes' => $submittedQuizzes,
            ]);
        }
    }

    private function processQuizzes(array $quizzes, array $submittedQuizzes): array
    {
        $quizExtraData = [];
        foreach ($quizzes as $key => $quiz) {
            $submittedNo = 0;
            $totalGrades = 0;
            $totalSpentTime = 0;
            foreach ($submittedQuizzes as $k => $submittedQuiz) {
                if ($quiz->getId() == $submittedQuiz->getQuiz()) {
                    $submittedNo++;
                    $totalGrades += $submittedQuiz->getObtainedGrade();
                    $totalSpentTime += $submittedQuiz->getTotalTimeSpent();
                }
            }

            if ($submittedNo > 0) {
                $gradeAverage = (float)$totalGrades / $submittedNo;
                $timeSpentAverage = (float)$totalSpentTime / $submittedNo;
            } else {
                $gradeAverage = 0;
                $timeSpentAverage = 0;
            }

            $quizExtraData[$quiz->getId()] = [
                'submittedNo' => $submittedNo,
                'gradeAverage' => $gradeAverage,
                'timeSpentAverage' => $timeSpentAverage
            ];
        }

        return $quizExtraData;
    }

    private function processSubmittedQuizzes(int $quiz, mixed $submittedQuizzes, array $assignedGroups)
    {
        $quizData = [];
        foreach ($assignedGroups as $k => $group) {
            $submittedNo = 0;
            $totalGrades = 0;
            $totalSpentTime = 0;
            foreach ($submittedQuizzes as $key => $submittedQuiz) {
                $studentGroup = $submittedQuiz->getSupportedBy()->getStudent()->getGroup()->getGroupNo();
                if (
                    $quiz == $submittedQuiz->getQuiz()
                    && $studentGroup == $group
                ) {
                    $submittedNo++;
                    $totalGrades += $submittedQuiz->getObtainedGrade();
                    $totalSpentTime += $submittedQuiz->getTotalTimeSpent();
                }
            }

            if ($submittedNo > 0) {
                $gradeAverage = (float)$totalGrades / $submittedNo;
                $timeSpentAverage = (float)$totalSpentTime / $submittedNo;
            } else {
                $gradeAverage = 0;
                $timeSpentAverage = 0;
            }

            $quizData[$group] = [
                'groupNo' => $group,
                'submittedNo' => $submittedNo,
                'gradeAverage' => round($gradeAverage, 2),
                'timeSpentAverage' => $timeSpentAverage
            ];
        }

        return $quizData;
    }

    private function getComparativeResult(array $groupData)
    {
        if ($groupData != null && count($groupData) > 1) {
            $baseGroup = [];
            $comparativeData = [];
            $differencePoints = 0;
            $percentageDifference = 0;
            $timeDifference = 0;
            foreach ($groupData as $groupNo => $value) {
                if (count($baseGroup) == 0) {
                    $baseGroup = $groupData[$groupNo];
                }
                if ($groupNo != $baseGroup['groupNo']) {
                    $differencePoints = round($baseGroup['gradeAverage'] - $value['gradeAverage'], 2);
                    if ($differencePoints < 0) {
                        $comparativeData[] = 'Group no ' . $groupNo . ' obtained ' . abs($differencePoints)
                            . ' more points than group no ' . $baseGroup['groupNo'] . '.';
                    } else {
                        $comparativeData[] = 'Group no ' . $groupNo . ' obtained ' . abs($differencePoints)
                            . ' less points than group no ' . $baseGroup['groupNo'] . '.';
                    }
                    $percentageDifference = (
                        ($value['gradeAverage'] - $baseGroup['gradeAverage']) / $baseGroup['gradeAverage']
                        ) * 100;
                    $percentageDifference = round($percentageDifference, 2);
                    if ($percentageDifference > 0) {
                        $comparativeData[] = 'Group no ' . $groupNo . ' obtained ' . round($percentageDifference, 2)
                            . '% more than group no ' . $baseGroup['groupNo'] .'.';
                    } else {
                        $comparativeData[] = 'Group no ' . $groupNo . ' obtained ' . round($percentageDifference, 2)
                            . '% less than group no ' . $baseGroup['groupNo'] . '.';
                    }
                    $timeDifference = $baseGroup['timeSpentAverage'] - $value['timeSpentAverage'];
                    $seconds = (strlen(abs($timeDifference%60)) == 1) ? '0' . abs($timeDifference%60) : abs($timeDifference%60);
                    if ($timeDifference < 0) {
                        $comparativeData[] = 'Group no ' . $groupNo . ' spent with ' . abs(intval($timeDifference/60))
                            . ':' . $seconds . ' minutes'
                            . ' more time than group no ' . $baseGroup['groupNo'] . '.';
                    } else {
                        $comparativeData[] = 'Group no ' . $groupNo . ' spent with ' . abs(intval($timeDifference/60))
                            . ':' . $seconds . ' minutes'
                            . ' less time than group no ' . $baseGroup['groupNo'] . '.';
                    }
                }
            }

            return $comparativeData;
        }

        return null;
    }

    private function getPassPercent(array $groupData, int $maxGrade): array
    {
        $passPercentage = [];
        $passGrade = 4.5;

        foreach ($groupData as $key => $value) {
            $passPercent = ($value['gradeAverage'] - $passGrade) / ($maxGrade - $passGrade) * 100;
            $passPercent = round($passPercent, 2);

            $passPercentage[$key] = "Group no " .  $key . ": " . $passPercent . "%";
        }

        return $passPercentage;
    }

    private function processAikenFormat(UploadedFile $file)
    {
        $file_path = $file->getPathname();
        $file = fopen($file_path, "r") or die("Unable to open file!");
        $questions = [];

        $foundChoices = 0;
        $foundCorrectAnswer = 0;
        while(($line = fgets($file)) !== false) {
//            suppose we have the question
            $question = trim($line);

            while (!feof($file) && ($foundChoices < 1 || $foundCorrectAnswer < 1)) {
                $line = fgets($file);
                if (preg_match('/^[A-Z][).]\s/', $line)) {
                    // this is an answer choice
                    $answerChoices[] = trim($line);
                    $foundChoices++;
                } elseif (preg_match('/^ANSWER: [A-Z]/', $line)) {
                    // this is an answer line
                    $correctAnswer = trim($line);
                    $foundCorrectAnswer++;
//
                    if ($foundChoices >= 2) {
                        $correctLetter = explode('ANSWER: ', trim($correctAnswer))[1];
                        $questions[] = [
                            "question" => $question,
                            "answerChoices" => $answerChoices,
                            "correctAnswer" => $correctAnswer,
                        ];
//                        dd($answerChoices);
//                        if (!in_array($correctLetter, $answerChoices)) {
//                            dd($correctLetter);
//                            throw new \Exception("Invalid AIKEN format!");
//                        }
                        $found = false;
                        foreach ($answerChoices as $k => $choice) {
                            if (str_contains($choice, $correctLetter . '.') || str_contains($choice, $correctLetter . ')')) {
                                $found = true;
                                break;
                            } else {
                                $found = false;
                            }
                        }
                        if (!$found) {
                            throw new \Exception("Invalid AIKEN format!");
                        }
                    }
                    $answerChoices = [];
                    $correctAnswer = '';
                    $foundChoices = 0;
                    $foundCorrectAnswer = 0;
                }
            }
            if (count($questions) == 0) {
                throw new \Exception("Invalid AIKEN format!");
            }
        }

        fclose($file);
        return $questions;
    }

    private function saveAikenQuestions(
        array $questions,
        string $category,
        QuizQuestionRepository $questionRepository,
        TeacherRepository $teacherRepository
    )
    {
//        dd($questions);
        foreach ($questions as $key => $questionData) {
            $question = new QuizQuestion();
            $question->setCategory($category);
            $question->setQuestionContent($questionData['question']);
            $choicesNo = count($questionData['answerChoices']);
            $choices = $questionData['answerChoices'];
            $correctAnswer = explode("ANSWER: ", $questionData['correctAnswer'])[1];
            if ($choicesNo == 2) {
                $question->setChoiceA($choices[0]);
                $question->setChoiceB($choices[1]);
                $question->setChoiceC('');
                $question->setChoiceD('');
            } elseif ($choicesNo == 3) {
                $question->setChoiceA($choices[0]);
                $question->setChoiceB($choices[1]);
                $question->setChoiceC($choices[2]);
                $question->setChoiceD('');
            } elseif ($choicesNo == 4) {
                $question->setChoiceA($choices[0]);
                $question->setChoiceB($choices[1]);
                $question->setChoiceC($choices[2]);
                $question->setChoiceD($choices[3]);
            }
            $answerIndex = ord($correctAnswer) - ord('A');
            $correctAnswerChoice = $questionData['answerChoices'][$answerIndex];
            $question->setCorrectAnswer($correctAnswerChoice);
            $userId = $this->getUser()->getIdentifierId();
            $teacher = $teacherRepository->getTeacher($userId);
            $question->setIssuedBy($teacher[0]);
            $question->setCreatedAt(new \DateTime());

            $questionRepository->save($question, true);
        }
    }
}
