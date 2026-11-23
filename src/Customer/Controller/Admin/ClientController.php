<?php
declare(strict_types=1);

namespace App\Customer\Controller\Admin;

use App\Customer\Dto\ClientDto;
use App\Customer\Dto\ClientLinkDto;
use App\Customer\Dto\ClientSaveDto;
use App\Customer\Exception\ClientAlreadyExistsException;
use App\Customer\Exception\ClientNotFoundException;
use App\Customer\Form\ClientLinkType;
use App\Customer\Form\ClientSaveType;
use App\Customer\Service\ClientService;
use App\Identity\Dto\UserDto;
use App\Identity\Exception\EmailAlreadyTakenException;
use App\Identity\Exception\PhoneAlreadyTakenException;
use App\Identity\Exception\UserHasRelatedDataException;
use App\Identity\Exception\UserNotFoundException;
use App\Identity\Service\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[Route(path: '/admin/client', name: 'client_')]
final class ClientController extends AbstractController
{
    public function __construct(
        private readonly ClientService $clientService,
        private readonly UserService $userService,
    ) {
    }

    /**
     * @throws ClientNotFoundException
     */
    #[Route(path: '/{id}', name: 'index', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function index(int $id): Response
    {
        $client = $this->clientService->getById($id);

        return $this->render('customer/client/index.html.twig', [
            'client' => ClientDto::fromClient($client),
        ]);
    }

    #[Route(path: '/list', name: 'list', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function list(): Response
    {
        $clients = $this->clientService->getAll();

        return $this->render('customer/client/list.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route(path: '/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $dto = new ClientSaveDto();
        $form = $this->createForm(ClientSaveType::class, $dto, [
            'mode' => 'create',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client = $this->clientService->create($dto);
                $this->addFlash('success', 'Клиент создан');

                return $this->redirectToRoute('client_index', ['id' => $client->getId()]);
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

        return $this->render('customer/client/create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @throws ClientNotFoundException
     * @throws UniqueConstraintViolationException
     */
    #[Route(path: '/edit/{id}', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $client = $this->clientService->getById($id);
        $dto = ClientSaveDto::fromClient($client);

        $form = $this->createForm(ClientSaveType::class, $dto, [
            'mode' => 'edit',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->clientService->update($client, $dto);
                $this->addFlash('success', 'Данные клиента обновлены');

                return $this->redirectToRoute('client_index', ['id' => $client->getId()]);
            } catch (PhoneAlreadyTakenException $e) {
                $form->get('userSaveDto')->get('phone')->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('customer/client/edit.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @throws UserNotFoundException
     * @throws UniqueConstraintViolationException
     */
    #[Route(path: '/link/{userId}', name: 'link', requirements: ['userId' => '\d+'], methods: ['GET', 'POST'])]
    public function link(int $userId, Request $request): Response
    {
        $user = $this->userService->getById($userId);

        $dto = new ClientLinkDto();
        $form = $this->createForm(ClientLinkType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client = $this->clientService->link($dto, $user);
                $this->addFlash('success', 'Клиент создан');

                return $this->redirectToRoute('client_index', ['id' => $client->getId()]);
            } catch (ClientAlreadyExistsException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('customer/client/link.html.twig', [
            'form' => $form,
            'user' => UserDto::fromUser($user),
        ]);
    }

    /**
     * @throws ClientNotFoundException
     */
    #[Route(path: '/delete/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsCsrfTokenValid(id: 'delete_client')] //todo отлавливать исключение в глоабльном хендлере
    public function delete(int $id): Response
    {
        $client = $this->clientService->getById($id);
        try {
            $this->clientService->delete($client);
            $this->addFlash('success', "Успешно удален клиент с id $id");

            return $this->redirectToRoute('client_list');
        } catch (UserHasRelatedDataException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('client_index', ['id' => $client->getId()]);
    }
}
