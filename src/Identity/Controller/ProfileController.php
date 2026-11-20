<?php

declare(strict_types=1);

namespace App\Identity\Controller;

use App\Identity\Dto\UserDto;
use App\Identity\Dto\UserSaveDto;
use App\Identity\Dto\UserUpdateEmailDto;
use App\Identity\Entity\User;
use App\Identity\Exception\EmailAlreadyTakenException;
use App\Identity\Exception\PhoneAlreadyTakenException;
use App\Identity\Form\UserSaveType;
use App\Identity\Form\UserUpdateEmailType;
use App\Identity\Service\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/profile', name: 'profile', methods: ['GET', 'POST'])]
final class ProfileController extends AbstractController
{

    public function __construct(
        private UserService $userService
    ) {
    }

    #[Route(path: '/', name: 'index', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): Response
    {
        $dto = UserDto::fromUser($user);

        return $this->render('identity/user/profile/index.html.twig', [
            'user' => $dto,
        ]);
    }

    /**
     * @throws UniqueConstraintViolationException
     */
    #[Route(path: '/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(#[CurrentUser] User $user, Request $request): Response
    {
        $dto = UserSaveDto::fromUser($user);
        $form = $this->createForm(UserSaveType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userService->update($user, $dto);
                $this->addFlash('success', 'Данные учетной записи обновлены');

                return $this->redirectToRoute('profile_index');
            } catch (PhoneAlreadyTakenException $e) {
                $form->get('phone')->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('identity/user/profile/edit.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @throws UniqueConstraintViolationException
     */
    #[Route(path: '/', name: 'profile', methods: ['GET', 'POST'])]
    public function passwordChange(#[CurrentUser] User $user, Request $request): Response
    {
        $dto = UserUpdateEmailDto::fromUser($user);
        $form = $this->createForm(UserUpdateEmailType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->userService->updateEmail($user, $dto);
                $this->addFlash('success', 'Почта успешно обновлена');

                return $this->redirectToRoute('profile_index');
            } catch (EmailAlreadyTakenException $e) {
                $form->get('email')->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('identity/user/profile/edit.html.twig', [
            'form' => $form,
        ]);
    }

    public function emailChange(): Response
    {
    }
}
