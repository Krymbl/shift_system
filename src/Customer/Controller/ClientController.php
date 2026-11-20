<?php
declare(strict_types=1);

namespace App\Customer\Controller;

use App\Customer\Service\ClientService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/client', name: 'client')]
final class ClientController extends AbstractController
{
    public function __construct(
        private readonly ClientService $clientService,
    ) {

    }

    #[Route(path: '/create', name: 'create', methods: ['GET, POST'])]
    public function create(): Response
    {

    }

    #[Route(path: '/edit', name: 'edit', methods: ['GET, POST'])]
    public function edit(): Response
    {

    }

    #[Route(path: '/delete', name: 'delete', methods: ['POST'])]
    public function delete(): Response
    {

    }
}
