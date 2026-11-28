<?php

declare(strict_types=1);

namespace App\Common\EventListener;

use App\Common\Exception\AbstractBusinessException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: -64)]
final readonly class ExceptionListener
{
    public function __construct(
        private Environment $twig,
        #[Autowire('%kernel.debug%')]
        private bool $debug,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        $json = $this->wantsJson($event->getRequest());

        if ($this->debug && !$json && !($throwable instanceof AbstractBusinessException)) {
            return;
        }

        $statusCode = $this->statusCode($throwable);
        $message = $this->getMessage($throwable, $statusCode);

        if ($json) {
            $response = $this->jsonResponse($statusCode, $message);
        } else {
            $response = $this->htmlResponse($statusCode, $message);
        }

        if ($throwable instanceof HttpExceptionInterface) {
            $response->headers->add($throwable->getHeaders());
        }

        $event->setResponse($response);
    }

    private function statusCode(\Throwable $throwable): int
    {
        return $throwable instanceof AbstractBusinessException || $throwable instanceof HttpExceptionInterface
                ? $throwable->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function getMessage(\Throwable $throwable, int $statusCode): string
    {
        if ($throwable instanceof AbstractBusinessException) {
            return $throwable->getMessage();
        }

        return match ($statusCode) {
            Response::HTTP_BAD_REQUEST => 'Неверный запрос',
            Response::HTTP_UNAUTHORIZED => 'Требуется вход в систему',
            Response::HTTP_FORBIDDEN => 'Доступ запрещён',
            Response::HTTP_NOT_FOUND => 'Страница не найдена',
            Response::HTTP_METHOD_NOT_ALLOWED => 'Метод не разрешён',
            Response::HTTP_TOO_MANY_REQUESTS => 'Слишком много запросов. Попробуйте позже',
            default => $statusCode >= 500 ? 'Внутренняя ошибка сервера' : 'Не удалось выполнить запрос',
        };
    }

    private function wantsJson(Request $request): bool
    {
        $format = $request->getRequestFormat(null);
        if ($format !== null) {
            return $format === 'json';
        }

        $accept = AcceptHeader::fromString($request->headers->get('accept'));
        $json = $accept->get('application/json')?->getQuality() ?? 0.0;
        $html = $accept->get('text/html')?->getQuality() ?? 0.0;

        return $json > $html;

    }

    private function htmlResponse(int $statusCode, string $message): Response
    {
        $html = $this->twig->render(
            'error/error.html.twig',
            [
                'statusCode' => $statusCode,
                'message' => $message,
            ]
        );

        return new Response($html, $statusCode);
    }

    private function jsonResponse(int $statusCode, string $message): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => false,
                'error' => $message,
            ],
            $statusCode,
        );
    }
}
