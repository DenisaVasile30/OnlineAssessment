<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\UserProfileFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileSettingsController extends AbstractController
{
    #[Route('/profile/settings', name: 'app_profile_settings')]
    public function index(
        Request $request,
        UserRepository $userRepository
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userProfile = $user->getUserProfile() ?? new UserProfile();

        $profileForm = $this->createForm(UserProfileFormType::class, $userProfile);
        $profileForm->handleRequest($request);

        if ($profileForm->isSubmitted() && $profileForm->isValid()) {
            $userProfile = $profileForm->getData();
            $user->setUserProfile($userProfile);
            $userRepository->save($user, true);
            $this->addFlash(
                'success',
                'Changes were saved!'
            );

            return $this->redirectToRoute('app_profile_settings');
        }

        return $this->render('profile_settings/profile.html.twig', [
            'profileForm' => $profileForm->createView(),
        ]);
    }
}
