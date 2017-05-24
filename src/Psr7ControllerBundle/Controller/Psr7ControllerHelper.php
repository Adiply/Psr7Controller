<?php

namespace Psr7ControllerBundle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Zend\Diactoros\Response;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

abstract class Psr7ControllerHelper extends Controller implements PsrAuthenticatedControllerInterface
{
    protected function psr7Json($body, $status = '200')
    {
        $response = new Response('php://memory', $status, ['Content-Type' => 'application/json']);
        $response->getBody()->write(json_encode($body));
        $httpFoundationFactory = new HttpFoundationFactory();
        return $httpFoundationFactory->createResponse($response);
    }

    protected function psr7Rest($data)
    {
        return $this->psr7Json(['data' => $data]);
    }

    protected function psr7RestError($messages)
    {
        return $this->psr7Json(['messages' => $messages], 400);
    }
}
