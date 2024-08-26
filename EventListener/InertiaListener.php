<?php

namespace Paw\SimpleSymfonyInertiaBundle\EventListener;

use Paw\SimpleSymfonyInertiaBundle\Service\InertiaInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class InertiaListener
{
    public function __construct(private readonly InertiaInterface $inertia, private readonly bool $debug)
    {
    }

    final public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->headers->get('X-Inertia')) {
            return;
        }

        if ('GET' === $request->getMethod()
            && $request->headers->get('X-Inertia-Version') !== $this->inertia->getVersion()
        ) {
            $response = new Response('', Response::HTTP_CONFLICT, ['X-Inertia-Location' => $request->getUri()]);
            $event->setResponse($response);
        }
    }

    final public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->getRequest()->headers->get('X-Inertia')) {
            return;
        }

        if ($this->debug && $event->getRequest()->isXmlHttpRequest()) {
            $event->getResponse()->headers->set('Symfony-Debug-Toolbar-Replace', 1);
        }

        if ($event->getResponse()->isRedirect()
            && Response::HTTP_FOUND === $event->getResponse()->getStatusCode()
            && in_array($event->getRequest()->getMethod(), ['PUT', 'PATCH', 'DELETE'])
        ) {
            $event->getResponse()->setStatusCode(Response::HTTP_FORBIDDEN);
        }
    }
}
