<?php

namespace IntoWebDevelopment\WorkflowBundle\Event;

use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;
use Symfony\Component\EventDispatcher\Event;

class ValidateStepEvent extends Event
{
    /**
     * @var StepInterface
     */
    protected $step;

    /**
     * @var mixed
     */
    protected $user;

    public function __construct(StepInterface $currentStep, $user = null)
    {
        $this->step = $currentStep;
        $this->user = $user;
    }

    public function getStep()
    {
        return $this->step;
    }

    public function getUser()
    {
        return $this->user;
    }
}