<?php

declare(strict_types=1);

namespace App\Organization\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[Route(path: '/admin/employee', name: 'admin_employee_', methods: ['GET', 'POST'])]
final class EmployeeController extends AbstractController
{
    #[Route(path: '/{id}', name: 'index', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function index(int $id): Response
    {
    }

    #[Route(path: '/list', name: 'list', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function list(): Response
    {
    }

    #[Route(path: '/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
    }

    #[Route(path: '/edit/{id}', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
    }

    #[Route(path: '/link/{userId}', name: 'link', requirements: ['userId' => '\d+'], methods: ['GET', 'POST'])]
    public function link(int $userId, Request $request): Response
    {
    }

    #[Route(path: '/delete/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid(id: 'delete_employee')] //todo отлавливать исключение в глоабльном хендлере
    public function delete(int $id): Response
    {
    }
}
