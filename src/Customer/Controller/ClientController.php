<?php
declare(strict_types=1);

namespace App\Customer\Controller;

use App\Customer\Dto\ClientSaveDto;
use App\Customer\Exception\ClientNotFoundException;
use App\Customer\Form\ClientSaveType;
use App\Customer\Service\ClientService;
use App\Identity\Exception\EmailAlreadyTakenException;
use App\Identity\Exception\PhoneAlreadyTakenException;
use App\Identity\Exception\UserHasRelatedDataException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;

#[Route(path: '/client', name: 'client_')]
final class ClientController extends AbstractController
{
    public function __construct(
        private readonly ClientService $clientService,
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
            'client' => $client,
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
            'is_create' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $client = $this->clientService->create($dto);
                $this->addFlash('success', 'Клиент создан');

                return $this->redirectToRoute('client_index', ['id' => $client->getId()]);
            } catch (EmailAlreadyTakenException $e) {
                $form->get('userSaveDto')->get('email')->addError(new FormError($e->getMessage()));
            } catch (PhoneAlreadyTakenException $e) {
                $form->get('userSaveDto')->get('phone')->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('customer/client/create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @throws ClientNotFoundException
     */
    #[Route(path: '/edit/{id}', name: 'edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $client = $this->clientService->getById($id);
        $dto = ClientSaveDto::fromClient($client);
        $form = $this->createForm(ClientSaveType::class, $dto);
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

    #[Route(path: '/deactivate/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deactivate(int $id): Response
    {

    }
}
