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

    public function __construct(StepInterface $currentStep)
    {
        $this->step = $currentStep;
    }

    public function getStep()
    {
        return $this->step;
    }
}