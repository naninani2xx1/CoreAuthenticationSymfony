<?php

namespace App\ApiBundle\EventSubscribers;

use App\ApiBundle\Traits\SecurityTrait;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Uid\Uuid;

final class ApiEventSubscriber implements EventSubscriberInterface
{
    use SecurityTrait;

    private readonly LoggerInterface $logger;
    private readonly ?JWTTokenManagerInterface $tokenManager;
    private readonly ?EntityManagerInterface $manager;

    private readonly RequestStack $requestStack;

    public function __construct(
        LoggerInterface        $logger, JWTTokenManagerInterface $tokenManager,
        EntityManagerInterface $manager,  RequestStack $requestStack,
    )
    {
        $this->logger = $logger;
        $this->tokenManager = $tokenManager;
        $this->manager = $manager;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return array(
            ResponseEvent::class => 'onResponse',
            RequestEvent::class => 'onKernelRequest',
            ExceptionEvent::class => 'onKernelException',
        );
    }

    function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->isCheckFirewallApi()) {
            return;
        }

        $throwable = $event->getThrowable();
        $statusCode = $throwable instanceof HttpExceptionInterface ? $throwable->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;
        $response = $event->getResponse() ?? new JsonResponse();
        $data = [
            'error' => $throwable->getMessage(),
        ];
        $response->setContent(json_encode($data));
        $response->setStatusCode($statusCode);
        $response->headers->set('Content-Type', ['application/json', 'application/Id+json']);

        $event->setResponse($response);
    }

    /**
     * @throws JWTDecodeFailureException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isCheckFirewallApi()) {
            return;
        }
        if (!$this->isCheckPathRefreshToken()) {
            $token = $this->getTokenFromHeader();
            $this->checkTokenInDB($token);
        }


        $transactionId = Uuid::v4()->toRfc4122();
        $request->attributes->set('transactionId', $transactionId);

        // Log Transaction ID
        $this->logger->info('Transaction ID: ' . $transactionId, [
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
        ]);
    }

    function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $request = $event->getRequest();
        if (!$this->isCheckFirewallApi()) {
            return;
        }

        $response = $event->getResponse();
        $transactionId = $request->attributes->get('transactionId', Uuid::v4()->toRfc4122());

        $data = json_decode($response->getContent(), true);
        if (is_array($data)) {
            $data['transactionId'] = $transactionId;
            $response->setContent(json_encode($data));
        }
        if ($response->getStatusCode() != Response::HTTP_OK && $response->getStatusCode() != Response::HTTP_CREATED) {
            $this->logger->error('Transaction failed', [
                'transId' => $transactionId,
                'status' => $response->getStatusCode(),
                'error_message' => $response->getContent(),
                'path' => $request->getPathInfo(),
            ]);
            return;
        }
        $this->logger->info('Transaction ID: ' . $transactionId, [
            'path' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'data' => $response->getContent()
        ]);
    }
}