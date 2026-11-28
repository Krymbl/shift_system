<?php

declare(strict_types=1);

namespace App\Identity\Controller\Admin;

use App\Identity\Dto\UserDto;
use App\Identity\Exception\UserAlreadyActiveException;
use App\Identity\Exception\UserAlreadyDeactivatedException;
use App\Identity\Exception\UserHasActiveEmploymentException;
use App\Identity\Exception\UserNotFoundException;
use App\Identity\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/admin/user', name: 'admin_user_')]
final class UserController extends AbstractController
{

    public function __construct(
        private readonly UserService $userService
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    #[Route(path: '/{id}', name: 'index', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function index(int $id): Response
    {
        $user = $this->userService->getById($id);

        return $this->render('identity/admin/user/index.html.twig', [
            'user' => UserDto::fromUser($user),
        ]);
    }

    #[Route(path: '/', name: 'list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('identity/admin/user/list.html.twig', [
            'users' => $this->userService->getAll(),
        ]);
    }

    /**
     * @throws UserNotFoundException
     */
    #[Route(
        path: '/{action}/{id}',
        name: 'change_status',
        requirements: ['action' => 'activate|deactivate', 'id' => '\d+'],
        methods: ['POST'],
    )]
    public function changeStatus(int $id, string $action, Request $request): Response
    {
        $tokenId = 'user_'. $action;

        if (!$this->isCsrfTokenValid($tokenId, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Недействительный CSRF-токен');
        }

        $user = $this->userService->getById($id);

        try {
            if ($action === 'activate') {
                $this->userService->activate($user);
                $message = 'Пользователь активирован';
            } else {
                $this->userService->deactivate($user);
                $message = 'Пользователь деактивирован';
            }

            $this->addFlash('success', $message);
        } catch (UserAlreadyActiveException|UserAlreadyDeactivatedException|UserHasActiveEmploymentException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_user_index', ['id' => $id]);
    }
}
