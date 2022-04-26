<?php

namespace IntoWebDevelopment\WorkflowBundle\Exception;

class TransitionFailedException extends \Exception
{
    protected array $validationMessages;

    public function setValidationMessages(array $validationMessages): static
    {
        $this->validationMessages = $validationMessages;
        return $this;
    }

    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }
}