<?php

namespace Adiply\EventListener;

use Adiply\PsrAuthenticatedControllerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Adiply\Exception\InvalidContentTypeException;
use Adiply\Exception\InvalidRequestTypeException;

class PsrListener
{
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if(!is_array($controller)){
            return;
        }
        if($controller[0] instanceof PsrAuthenticatedControllerInterface) {
            $request = $event->getRequest();
            $psr7Factory = new DiactorosFactory();
            /** @var ServerRequest $psrRequest */
            $psrRequest = $psr7Factory->createRequest($request);
            if(!($psrRequest instanceof ServerRequestInterface)){
                throw new InvalidRequestTypeException('a PSR7 compliant request must be sent');
            }
            $type = $psrRequest->getHeader('Content-Type');
            if('application/json' != array_shift($type)){
                throw new InvalidContentTypeException('content type json required');
            }
            //In things other than bouncer this is going to do some kind of authentication also I think
        }
    }
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if($exception instanceof InvalidRequestTypeException || $exception instanceof InvalidContentTypeException){
            $response = new Response('php://memory', 400, ['Content-Type' => 'application/json']);
            $response->getBody()->write(json_encode(['status' => 0, 'message' => $exception->getMessage()]));
            $httpFoundationFactory = new HttpFoundationFactory();
            $event->setResponse($httpFoundationFactory->createResponse($response));
        }
        return;
    }
}
