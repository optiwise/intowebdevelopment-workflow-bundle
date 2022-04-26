<?php

namespace IntoWebDevelopment\WorkflowBundle\Event;

use IntoWebDevelopment\WorkflowBundle\Action\ActionInterface;
use IntoWebDevelopment\WorkflowBundle\Process\ProcessInterface;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;
use Symfony\Contracts\EventDispatcher\Event;

class RunActionEvent extends Event
{
    public function __construct(
        private StepInterface $currentStep,
        private ActionInterface $action,
        private ProcessInterface $process,
        private ?StepInterface $nextStep,
        private mixed $actionResult = null
    ) {
    }

    public function getNextStep(): ?StepInterface
    {
        return $this->nextStep;
    }

    public function getCurrentStep(): StepInterface
    {
        return $this->currentStep;
    }

    public function getProcess(): ProcessInterface
    {
        return $this->process;
    }

    public function getAction(): ActionInterface
    {
        return $this->action;
    }

    public function getActionResult(): mixed
    {
        return $this->actionResult;
    }

    public function setActionResult(mixed $actionResult): static
    {
        $this->actionResult = $actionResult;
        return $this;
    }
}