<?php

namespace Psr7ControllerBundle\EventListener;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Psr7ControllerBundle\PsrAuthenticatedControllerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use GuzzleHttp\Client;
use Zend\Diactoros\ServerRequest;

class AuthenticationListener
{
    public function __construct(string $bouncer_location)
    {
        $this->bouncer_location = $bouncer_location;
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
            $user = $psr7Request->getHeader('user');
            $key = $psr7Request->getHeader('key');
            $client = new Client();
            $response = $client->get($this->bouncer_location . '/1/checkPermission', [
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
                throw new Exception("Sorry pal, you ain't on the list.");
            }
        }
    }
}
