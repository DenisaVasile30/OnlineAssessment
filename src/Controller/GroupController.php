<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\Student;
use App\Entity\User;
use App\Form\AddGroupFormType;
use App\Repository\GroupRepository;
use App\Repository\StudentRepository;
use App\Repository\TeacherRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class GroupController extends AbstractController
{
    #[Route('/home/groups', name: 'app_show_groups')]
    public function index(
        GroupRepository $groupRepository,
        StudentRepository $studentRepository
    ): Response
    {
        $teacherAssignedGroups = $this->getUser()->getTeacher()->getAssignedGroups();
//        $groups = $groupRepository->getGroups($teacherAssignedGroups);
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

        return $this->render('groups/show_students_from_group.html.twig', [
            'group' => $group,
            'studentsList' => $studentsList,
        ]);
    }

    #[Route('/home/{groupId}s/groups/students/add/existing/{userId}', name: 'app_group_add_existing_student')]
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
    public function createStudentAccount(
        Request $request,
        int $groupId,
        GroupRepository $groupRepository,
        StudentRepository $studentRepository,
        UserRepository $userRepository,
        UserPasswordHasherInterface $userPasswordHasher,
    ): Response
    {
        $group = $groupRepository->find($groupId);
        $emailToSet = $request->request->get('email');
        $passwordToSet = $request->request->get('password');

        if ($emailToSet && $passwordToSet) {
            $user = new User();
            $user->setEmail($emailToSet);
            $user->setRoles(['ROLE_STUDENT']);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                $user,
                $passwordToSet
            )
            );

            $userRepository->save($user, true);

            $student = new Student();
            $student->setUser($user);
            $student->setEnrollmentDate(new \DateTime());
            $student->setGroup($group);

            $studentRepository->save($student, true);
        }

        return $this->redirectToRoute('app_group_show_students', [
            'groupId' => $groupId
        ]);
    }
}
