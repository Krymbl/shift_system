<?php

declare(strict_types=1);

namespace App\Identity\Controller\Admin;

use App\Identity\Exception\UserAlreadyActiveException;
use App\Identity\Exception\UserAlreadyDeactivatedException;
use App\Identity\Exception\UserNotFoundException;
use App\Identity\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[Route(path: '/admin/user', name: 'admin_user', methods: ['GET', 'POST'])]
final class UserController extends AbstractController
{

    public function __construct(
        private readonly UserService $userService
    )
    {
    }

    /**
     * @throws UserNotFoundException
     */
    #[Route(path: '/index/{id}', name: 'index', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function index(int $id): Response
    {
        $userDto = $this->userService->getById($id);

        return $this->render('identity/admin/user/index.html.twig', [
            'user' => $userDto,
        ]);
    }

    #[Route(path: '/list', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('identity/admin/user/list.html.twig', [
            'users' => $this->userService->getAll(),
        ]);
    }
    /**
     * @throws UserNotFoundException
     */
    #[Route(path: '/deactivate/{id}', name: 'deactivate', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid(id: 'user_deactivate')] //todo отлавливать исключение в глоабльном хендлере
    public function deactivate(int $id): Response
    {
        $user = $this->userService->getById($id);
        try {
            $this->userService->deactivate($user);
            $this->addFlash('success', 'Пользователь был деактивирован');
        } catch (UserAlreadyDeactivatedException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_user_index', ['id' => $id]);
    }

    #[Route(path: '/activate/{id}', name: 'activate', methods: ['GET'])]
    #[IsCsrfTokenValid(id: 'user_activate')] //todo отлавливать исключение в глоабльном хендлере
    public function activate(int $id): Response
    {
        $user = $this->userService->getById($id);
        try {
            $this->userService->activate($user);
            $this->addFlash('success', 'Пользователь активирован');
        } catch (UserAlreadyActiveException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_user_index', ['id' => $id]);
    }
}
