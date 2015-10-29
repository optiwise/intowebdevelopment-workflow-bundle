<?php

namespace IntoWebDevelopment\WorkflowBundle\Exception;

class TransitionFailedException extends \Exception
{
    protected $validationMessages;

    public function setValidationMessages(array $validationMessages)
    {
        $this->validationMessages = $validationMessages;
        return $this;
    }

    public function getValidationMessages()
    {
        return $this->validationMessages;
    }
}