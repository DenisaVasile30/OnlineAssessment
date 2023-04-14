<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\AddGroupFormType;
use App\Repository\GroupRepository;
use App\Repository\StudentRepository;
use App\Repository\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

;
//        $studentsNoList = [];
//        foreach ($groups as $group) {
//            $studentsNo = count($studentRepository->findBy(['groupId' => $group->getId()]));
//            $studentsNoList[$group->getId()] = $studentsNo;
//        }

        return $this->render('groups/add_group.html.twig',[
            'form' => $form->createView()
        ]);
    }

//    #[Route('/home/groups', name: 'app_groups')]
//    public function index(GroupRepository $groups): Response
//    {
//        $teacherAssignedGroups = $this->getUser()->getTeacher()->getAssignedGroups();
//        $studentsList = [];
//        if (count($teacherAssignedGroups) > 0) {
//            foreach ($teacherAssignedGroups as $key => $groupNo) {
//                $studentsList[$groupNo] = $groups->getStudentsFromGroup((int)$groupNo);
//            }
//        }
//        dd($studentsList);
//        $studentsList = $groups->getStudentsFromGroup();
//        dd($studentsList);
//        return $this->render('groups/index.html.twig', [
//            'studentsList' => $studentsList,
//        ]);
//    }
}
