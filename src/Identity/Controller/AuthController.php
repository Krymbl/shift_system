<?php

declare(strict_types=1);

namespace App\Identity\Controller;

use App\Identity\Dto\UserSaveDto;
use App\Identity\Exception\EmailAlreadyTakenException;
use App\Identity\Exception\PhoneAlreadyTakenException;
use App\Identity\Form\UserSaveType;
use App\Identity\Service\UserService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class AuthController extends AbstractController
{
    public function __construct(
    ) {
    }

    #[Route(path: '/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            $this->addFlash('error', 'Вы уже вошли');

            return $this->redirectToRoute('ХЗ');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('identity/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

//    //TODO потом сделать доступ только для админов
//    /**
//     * @throws UniqueConstraintViolationException
//     */
//    #[Route(path: '/userCreate', name: 'register', methods: ['GET', 'POST'])]
//    public function register(Request $request, Security $security): Response
//    {
//
//        $dto = new UserSaveDto();
//        $form = $this->createForm(UserSaveType::class, $dto);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            try {
//                $user = $this->authService->create($dto);
//
//                $security->login($user);
//                $this->addFlash(
//                    'success',
//                    sprintf('Успешно создан пользователь %s %s', $dto->firstName, $dto->lastName)
//                );
//
//                return $this->redirectToRoute('ХЗ');
//            } catch (EmailAlreadyTakenException $e) {
//                $form->get('email')->addError(new FormError($e->getMessage()));
//            } catch (PhoneAlreadyTakenException $e) {
//                $form->get('phone')->addError(new FormError($e->getMessage()));
//            }
//        }
//
//        return $this->render('auth/register.html.twig', [
//            'form' => $form,
//        ]);
//    }

    #[Route(path: '/logout', name: 'logout', methods: ['POST'])]
    public function logout(): never
    {
        throw new \LogicException('Перехватывается и не выполняется');
    }
}
