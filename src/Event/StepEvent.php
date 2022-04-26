<?php

namespace IntoWebDevelopment\WorkflowBundle\Event;

use IntoWebDevelopment\WorkflowBundle\Process\ProcessInterface;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class StepEvent extends Event
{
    public function __construct(private StepInterface $currentStep, private StepInterface $nextStep, private ProcessInterface $process, private ?UserInterface $user = null)
    {
    }

    public function getCurrentStep(): StepInterface
    {
        return $this->currentStep;
    }

    public function getNextStep(): StepInterface
    {
        return $this->nextStep;
    }

    public function getProcess(): ProcessInterface
    {
        return $this->process;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }
}