<?php

namespace Psr7ControllerBundle\EventListener;

use Psr7ControllerBundle\Exception\InvalidRequestException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Psr7ControllerBundle\Controller\PsrAuthenticatedControllerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use GuzzleHttp\Client;
use Zend\Diactoros\ServerRequest;

class AuthenticationListener
{
    public function __construct($bouncer_location, $bouncer_version)
    {
        $this->bouncer_location = $bouncer_location;
        $this->bouncer_version = $bouncer_version;
    }
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        if(!is_array($controller)){
            return;
        }
        if($controller[0] instanceof PsrAuthenticatedControllerInterface) {
            $request = $event->getRequest();
            $psr7Factory = new DiactorosFactory();
            /** @var ServerRequest $psr7Request */
            $psr7Request = $psr7Factory->createRequest($request);

            $headers = $psr7Request->getHeaders();

            if(!isset($headers['user'], $headers['key']))
            {
                throw new InvalidRequestException("Authorization required.");
            }

            $user = $headers['user'];
            $key = $headers['key'];
            $client = new Client();
            $response = $client->get($this->bouncer_location . '/'.$this->bouncer_version.'/checkPermission', [
                'headers'=>[
                    'Content-Type'=>'application/json',
                    'User'=>$user,
                    'Key'=>$key
                ]
            ]);
            if(!isset($response)){
                throw new Exception("...Time Passes With No Response. It's getting dark. You may be eaten by a grue.");
            }
            $resp = json_decode($response->getBody());
            if(!isset($resp->status) || $resp->status!=1){
                throw new InvalidRequestException("Sorry pal, you ain't on the list.");
            }
        }
    }
}
