<?php

namespace App\ApiBundle\EventSubscribers;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Uid\Uuid;

class ApiEventSubscriber implements EventSubscriberInterface
{
    private readonly  LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
        $request = $event->getRequest();
        if (!$this->isCheckFirewallApi($request)) {
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

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->isCheckFirewallApi($request)) {
            return;
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
        if (!$this->isCheckFirewallApi($request)) {
            return;
        }

        $response = $event->getResponse();
        $transactionId = $request->attributes->get('transactionId', Uuid::v4()->toRfc4122());

        $data = json_decode($response->getContent(), true);
        if (is_array($data)) {
            $data['transactionId'] = $transactionId;
            $response->setContent(json_encode($data));
        }
        if($response->getStatusCode() != Response::HTTP_OK && $response->getStatusCode() != Response::HTTP_CREATED) {
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

    private function isCheckFirewallApi(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/api');
    }
}