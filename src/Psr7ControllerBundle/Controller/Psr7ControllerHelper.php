<?php

namespace Psr7ControllerBundle\Controller;

use Psr7ControllerBundle\Helper\RestResponseHelper;
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

    protected function psr7Rest(RestResponseHelper $helper)
    {
        return $this->psr7Json($helper->render(), $helper->getStatus());
    }
}
