<?php
declare(strict_types=1);

namespace App\Customer\Controller;

use App\Customer\Dto\ClientSaveDto;
use App\Customer\Exception\ClientNotFoundException;
use App\Customer\Service\ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
    #[Route(path: '/{id}', name: 'index', methods: ['GET'])]
    public function index(int $id): Response
    {
        $client = $this->clientService->getById($id);

        return $this->render('customer/client/index.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route(path: '/create', name: 'create', methods: ['GET, POST'])]
    public function create(): Response
    {
        $dto = new ClientSaveDto();
        $form = $this->createForm('ХЗ', $dto);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->clientService->create($dto);
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

    #[Route(path: '/edit/{id}', name: 'edit', methods: ['GET, POST'])]
    public function edit(): Response
    {

    }

    #[Route(path: '/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(): Response
    {

    }
}
