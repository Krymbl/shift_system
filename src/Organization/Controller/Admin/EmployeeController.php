<?php

declare(strict_types=1);

namespace App\Organization\Controller\Admin;

use App\Customer\Service\ClientService;
use App\Identity\Dto\UserDto;
use App\Identity\Exception\EmailAlreadyTakenException;
use App\Identity\Exception\PhoneAlreadyTakenException;
use App\Identity\Exception\UserHasRelatedDataException;
use App\Identity\Service\UserService;
use App\Organization\Dto\EmployeeDto;
use App\Organization\Dto\EmployeeLinkDto;
use App\Organization\Dto\EmployeeSaveDto;
use App\Organization\Entity\Employee;
use App\Organization\Exception\EmployeeAlreadyExistsException;
use App\Organization\Exception\EmployeeNotFoundException;
use App\Organization\Form\EmployeeLinkType;
use App\Organization\Form\EmployeeSaveType;
use App\Organization\Service\EmployeeService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[Route(path: '/admin/employee', name: 'admin_employee_', methods: ['GET', 'POST'])]
final class EmployeeController extends AbstractController
{
    public function __construct(
        private readonly EmployeeService $employeeService,
        private readonly UserService $userService,
    ) {
    }

    /**
     * @throws EmployeeNotFoundException
     */
    #[Route(path: '/{id}', name: 'index', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function index(int $id): Response
    {
        $employee = $this->employeeService->getById($id);

        return $this->render('organization/admin/employee/index.html.twig', [
            'employee' => EmployeeDto::fromEmployee($employee),
        ]);
    }

    #[Route(path: '/list', name: 'list', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function list(): Response
    {
        $employee = $this->employeeService->getAll();

        return $this->render('organization/admin/employee/list.html.twig', [
            'employee' => $employee,
        ]);
    }

    /**
     * @throws UniqueConstraintViolationException
     */
    #[Route(path: '/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $dto = new EmployeeSaveDto();
        $form = $this->createForm(EmployeeSaveType::class, $dto, [
            'mode' => 'create',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $employee = $this->employeeService->create($dto);
                $this->addFlash('success', 'Клиент создан');

                return $this->redirectToRoute('employee_index', ['id' => $employee->getId()]);
            } catch (PhoneAlreadyTakenException $e) {
                $form->get('userSaveDto')->get('phone')->addError(new FormError($e->getMessage()));
            } catch (EmailAlreadyTakenException $e) {
                $this->addFlash(
                    'notice',
                    'Аккаунт с такой почтой уже существует, привязать клиента к этому аккаунту?'
                );
                //todo редирект или что-то для того, чтобы акк привязать и подумать над привязкой
            }
        }

        return $this->render('organization/admin/employee/create.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $employee = $this->employeeService->getById($id);
        $dto = EmployeeSaveDto::fromEmployee($employee);

        $form = $this->createForm(EmployeeSaveType::class, $dto, [
            'mode' => 'edit',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->employeeService->update($employee, $dto);
                $this->addFlash('success', 'Данные клиента обновлены');

                return $this->redirectToRoute('employee_index', ['id' => $employee->getId()]);
            } catch (PhoneAlreadyTakenException $e) {
                $form->get('userSaveDto')->get('phone')->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('organization/admin/employee/edit.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/link/{userId}', name: 'link', requirements: ['userId' => '\d+'], methods: ['GET', 'POST'])]
    public function link(int $userId, Request $request): Response
    {
        $user = $this->userService->getById($userId);

        $dto = new EmployeeLinkDto();
        $form = $this->createForm(EmployeeLinkType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $employee = $this->employeeService->link($user);
                $this->addFlash('success', 'Клиент создан');

                return $this->redirectToRoute('employee_index', ['id' => $employee->getId()]);
            } catch (EmployeeAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('customer/admin/employee/link.html.twig', [
            'form' => $form,
            'user' => UserDto::fromUser($user),
        ]);
    }

    #[Route(path: '/delete/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid(id: 'delete_employee')] //todo отлавливать исключение в глоабльном хендлере
    public function delete(int $id): Response
    {
        $client = $this->employeeService->getById($id);
        try {
            $this->employeeService->delete($client);
            $this->addFlash('success', "Успешно удален сотрудник с id $id");

            return $this->redirectToRoute('client_list');
        } catch (UserHasRelatedDataException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('employee_index', ['id' => $client->getId()]);
    }
}
