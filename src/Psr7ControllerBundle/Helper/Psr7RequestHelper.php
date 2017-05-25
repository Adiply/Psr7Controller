<?php

namespace Psr7ControllerBundle\Helper;


use Psr\Http\Message\ServerRequestInterface;
use Psr7ControllerBundle\Exception\InvalidRequestException;

class Psr7RequestHelper
{

    /** @var ServerRequestInterface  */
    private $request;

    /** @var mixed|null|\stdClass */
    private $body = null;

    /** @var array  */
    private $missing = [];

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
        $method = $this->request->getMethod();
        if($method == 'POST' || $method == 'PUT'){
            $body = $this->request->getBody()->getContents();
            $data = json_decode($body);

            if(!$data){
                throw new InvalidRequestException('Invalid JSON: ' . json_last_error_msg(), json_last_error());
            }
            $this->body = $data;
        }

    }

    public function getHeader(string $key)
    {
        return $this->request->getHeader($key);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getDataElement(string $key){
        if($this->body){
            if(isset($this->body->$key)){
                return $this->body->$key;
            }
        }
        return false;
    }

    public function validateDataElements(array $keys)
    {
        $this->missing = [];
        if($this->body){
            foreach($keys as $key){
                if(!isset($this->body->$key)){
                    $this->missing[] = $key;

                }
            }
            if($this->missing){
                return false;
            }
            return true;
        }
        return false;
    }

    public function getMissing()
    {
        return $this->missing;
    }
}
