<?php

namespace App\Controller;

use App\Entity\CreatedQuiz;
use App\Entity\SupportedQuiz;
use App\Entity\SupportedQuizDetails;
use App\Form\CreateQuizFormType;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuizController extends AbstractController
{
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
                $scores = [];
                foreach ($postedScores as $childArray) {
                    $scores = array_merge($scores, array_values($childArray));
                }

                $questionsScores = [];

                foreach ($selectedQuestionsIds as $questionsId) {
                    foreach ($scores as $keyScore => $scoreValue) {
                        if ($questionsId == $keyScore) {
                            $questionsScores[$questionsId] = $scoreValue;
                        }
                    }
                }

                $createdQuiz->setQuestionsList($questionsScores);
                $createdQuiz->setCategory($form->get('category')->getData());
                $createdQuiz->setCreatedBy($teacher[0]);
                $createdQuiz->setMaxGrade($form->get('maxGrade')->getData());
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
            $quizzes = $createdQuizRepository->getAssessmentsByIssuerId($teacher->getId());
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

            $supportedQuizRepository->save($supportedQuiz, true);
        }


        //        get all questionsIds
        $questionsIds = implode(',', array_keys($requiredQuiz->getQuestionsList()));
        $questions = $quizQuestionRepository->getQuestionsByIds($questionsIds);
        $index = 0;
        $totalQuestions = count($questions) - 1;
        $form = $this->createForm(
            StartQuizFormType::class,
            [
                'questions' => $questions
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('next')->isClicked()) {
                $index = $request->request->get('index');
                $isCorrectAnswer = false;
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
                $user = $this->getUser();
                $question->setSupportedBy($user);
                $detailsRepository->save($question, true);
                $index++;

                if (!$index > $totalQuestions) {
                    return $this->render('quiz/start_quiz.html.twig', [
                        'requiredQuiz' => $requiredQuiz,
                        'question' => $questions[$index],
                        'index' => $index,
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

                        $supportedQuizRepository->save($supportedQuiz, true);
                    }
                }

//                to do: redirect to a submitted page ( show preview )
                return $this->redirectToRoute('app_home');

            }
        }

//        to do
//      add ended_at, obtained_grade, total_time_Spent for $supportedQuiz
        return $this->render('quiz/start_quiz.html.twig', [
            'requiredQuiz' => $requiredQuiz,
            'question' => $questions[$index],
            'index' => $index,
            'supportQuiz' => $form->createView()
        ]);
    }
}
