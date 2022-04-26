<?php

namespace IntoWebDevelopment\WorkflowBundle\Event;

use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ValidateStepEvent extends Event
{
    public function __construct(private StepInterface $step, private ?UserInterface $user = null)
    {
    }

    public function getStep(): StepInterface
    {
        return $this->step;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }
}