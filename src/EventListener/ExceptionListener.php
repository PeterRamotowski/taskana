<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    public function __construct(
        private KernelInterface $kernel
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->kernel->getEnvironment() == 'prod') {
            $response = (new Response())
                ->setContent(' ')
                ->setStatusCode(Response::HTTP_OK);

            $event->setResponse($response);
        }
    }
}
