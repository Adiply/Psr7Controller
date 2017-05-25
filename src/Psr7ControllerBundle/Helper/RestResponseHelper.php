<?php

namespace Psr7ControllerBundle\Helper;

/**
 *
 */
class RestResponseHelper
{
    /** @var array  */
    private $errors = [];

    /** @var array  */
    private $data = [];

    /** @var int  */
    private $status = 200;

    public function addData(string $key, $data)
    {
        $this->data[$key] = $data;
        return $this;
    }

    public function addError($message, int $status = 400)
    {
        $this->errors[] = $message;

        if($this->status == 200){
            $this->setStatus($status);
        }

        return $this;
    }

    public function addUnauthorizedUserError()
    {
        $this->addError('unuathorized user for this action', 403);
        return $this;
    }

    public function addMissingFieldsError(array $fields)
    {
        $this->addError('missing required fields: ' . implode(', ', $fields));
        return $this;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function render()
    {
        $dataSet = [];
        if($this->data){
            $dataSet['data'] = $this->data;
        }
        if($this->errors){
            $dataSet['errors'] = $this->errors;
        }

        return $dataSet;
    }
}
