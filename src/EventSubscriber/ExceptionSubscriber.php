<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        // Actions que l'on va faire lorsqu'on écoute une de ces exceptions:
        $exception = $event->getThrowable(); // on récupère l'exception liée à l'évènement
        // ça peut être :
        // Symfony\Component\HttpKernel\Exception\HttpException;
        // ou :
        // Symfony\Component\Routing\Exception\RouteNotFoundException
        // Symfony\Component\Security\Core\Exception\AccessDeniedException
        // Symfony\Component\Validator\Exception\ValidatorException ...
 
        if ($exception instanceof HttpException){
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ];
            $event->setResponse(new JsonResponse($data));// on remplace la réponse de l'évènement par du json
        } else { // cas où qq chose s'est mal passé
            $data = [
                'status' => 500, // Le status n'existe pas car ce n'est pas une exception HTTP, donc on met 500 par défaut. (500 genre le serveur a eu un problème)
                'message' => $exception->getMessage()
            ];
            $event->setResponse(new JsonResponse($data));
        }
    }
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException', // ce que nous écoutons
        ];
    }
}