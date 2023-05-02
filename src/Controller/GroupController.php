<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Student;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\AddGroupFormType;
use App\Repository\GroupRepository;
use App\Repository\StudentRepository;
use App\Repository\TeacherRepository;
use App\Repository\UserProfileRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    #[Route('/home/groups', name: 'app_show_groups')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function index(
        GroupRepository $groupRepository,
        StudentRepository $studentRepository
    ): Response
    {
        $teacherAssignedGroups = $this->getUser()->getTeacher()->getAssignedGroups();
//        $studentAssigned = $studentRepository->getStudentAssignedByTeacherId($this->getUser(), $this->getUser()->getTeacher());
//        dd($studentAssigned);
        $groups = $groupRepository->findAll();
        $studentsNoList = [];
        foreach ($groups as $group) {
            $studentsNo = count($studentRepository->findBy(['groupId' => $group->getId()]));
            $studentsNoList[$group->getId()] = $studentsNo;
        }

        return $this->render('groups/show_groups.html.twig', [
            'groups' => $groups,
            'studentsNoList' => $studentsNoList,
            'teacherAssignedGroups' => $teacherAssignedGroups,
        ]);
    }

    #[Route('/home/groups/assign/{groupNo}', name: 'app_assign_group')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function assignGroup(
        GroupRepository $groupRepository,
        int $groupNo,
        TeacherRepository $teacherRepository
    ): Response
    {
        $teacher = $teacherRepository->findOneBy(['user' => $this->getUser()->getIdentifierId()]);
        if ($teacher->getAssignedGroups() != null) {
            $existingGroups = $teacher->getAssignedGroups();
            $existingGroups[] = (int)$groupNo;

            $teacher->setAssignedGroups($existingGroups);

            $teacherRepository->save($teacher, true);

            return $this->redirectToRoute('app_show_groups');
        }
    }

    #[Route('/home/groups/unassign/{groupNo}', name: 'app_unassign_group')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function unassignGroup(
        GroupRepository $groupRepository,
        int $groupNo,
        TeacherRepository $teacherRepository
    ): Response
    {
        $teacher = $teacherRepository->findOneBy(['user' => $this->getUser()->getIdentifierId()]);
        if ($teacher->getAssignedGroups() != null) {
            $existingGroups = $teacher->getAssignedGroups();

            $key = array_search((int)$groupNo, $existingGroups);
            if ($key !== false) {
                unset($existingGroups[$key]);
            }

            $teacher->setAssignedGroups($existingGroups);
            $teacherRepository->save($teacher, true);

            return $this->redirectToRoute('app_show_groups');
        }
    }

    #[Route('/home/groups/new', name: 'app_new_group')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function createGroup(
        Request $request,
        GroupRepository $groupRepository
    ): Response
    {
        $group = new Group();
        $form = $this->createForm(AddGroupFormType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $group->setCreatedDate(new \DateTime());
            $group->setFaculty($form->get('faculty')->getData());
            $groupNo = (int)$form->get('groupNo')->getData();
            $group->setGroupNo($groupNo);

            $groupRepository->save($group);

            $this->redirectToRoute('app_show_groups');
        }

        return $this->render('groups/add_group.html.twig',[
            'form' => $form->createView()
        ]);
    }

    #[Route('/home/groups/students/show/{groupId}', name: 'app_group_show_students')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function showStudentsFromGroup(
        Request $request,
        int $groupId,
        GroupRepository $groupRepository,
        StudentRepository $studentRepository
    ): Response
    {
        $group = $groupRepository->find($groupId);
        $studentsList = $studentRepository->getStudentsFromGroup($group);
        $allStudents = $studentRepository->findAll();

        return $this->render('groups/show_students_from_group.html.twig', [
            'group' => $group,
            'studentsList' => $studentsList,
            'allStudents' => $allStudents,
        ]);
    }

    #[Route('/home/groups/{groupId}/students/remove/{studentId}', name: 'app_group_remove_student')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function removeStudentFromGroup(
        Request $request,
        int $groupId,
        int $studentId,
        GroupRepository $groupRepository,
        StudentRepository $studentRepository
    ): Response
    {
        $group = $groupRepository->find($groupId);
        $targetStudent = $studentRepository->findBy([
            'group' => $group,
            'id' => $studentId
        ]);
        $targetStudent[0]->setGroup(null);
        $studentRepository->save($targetStudent[0], true);
        $studentsList = $studentRepository->getStudentsFromGroup($group);
        $allStudents = $studentRepository->findAll();

        return $this->render('groups/show_students_from_group.html.twig', [
            'group' => $group,
            'studentsList' => $studentsList,
            'allStudents' => $allStudents,
        ]);
    }

    #[Route('/home/{groupId}s/groups/students/add/existing/{userId}', name: 'app_group_add_existing_student')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function addGroupForExistingStudent(
        Request $request,
        int $groupId,
        string $userId,
        GroupRepository $groupRepository,
        StudentRepository $studentRepository,
        UserRepository $userRepository
    ): Response
    {
        $user = $userRepository->find($userId);
        $group = $groupRepository->find($groupId);
        $student = $studentRepository->findBy(['user' => $user]);
        $student[0]->setGroup($group);
        $studentRepository->save($student[0], true);

        $group = $groupRepository->find($groupId);
//        $studentsList = $studentRepository->getStudentsFromGroup($group);

        return $this->redirectToRoute('app_group_show_students', [
            'groupId' => $groupId
        ]);
    }

    #[Route('/home/groups/{groupId}/students/create/account', name: 'app_create_student_account')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[IsGranted('ROLE_TEACHER')]
    public function createStudentAccount(
        Request $request,
        int $groupId,
        GroupRepository $groupRepository,
        StudentRepository $studentRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
        UserProfileRepository $profileRepository
    ): Response
    {
        $group = $groupRepository->find($groupId);
        $postedData = $request->request->all();
        $emailsToSet = $postedData['emails'];
        $passwordsToSet = $postedData['passwords'];

        foreach ($emailsToSet as $k => $email) {
            foreach ($passwordsToSet as $key => $password) {
                if ($k == $key) {
                    if ($email && $password) {
                        $user = new User();
                        $user->setEmail($email);
                        $user->setRoles(['ROLE_STUDENT']);
                        $user->setPassword(
                            $userPasswordHasher->hashPassword(
                                $user,
                                $password
                            )
                        );

                        $userRepository->save($user, true);

                        $student = new Student();
                        $student->setUser($user);
                        $student->setEnrollmentDate(new \DateTime());
                        $student->setGroup($group);

                        $studentRepository->save($student, true);

                        $atPosition = strpos($email, "@");
                        $parts = explode(".", substr($email, 0, $atPosition));
                        if (isset($parts) && count($parts) == 2) {
                            $firstName = $parts[0];
                            $lastName = $parts[1];

                            $userProfile = new UserProfile();
                            $userProfile->setUser($user);
                            $userProfile->setFirstName(ucfirst($firstName));
                            $userProfile->setLastName(ucfirst($lastName));

                            $profileRepository->save($userProfile, true);
                        }
                    }
                }
            }
        }

        return $this->redirectToRoute('app_group_show_students', [
            'groupId' => $groupId
        ]);
    }
}
