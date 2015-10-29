<?php

namespace IntoWebDevelopment\WorkflowBundle\Event;

use IntoWebDevelopment\WorkflowBundle\Process\ProcessInterface;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;
use Symfony\Component\EventDispatcher\Event;

class StepEvent extends Event
{
    protected $currentStep;
    protected $nextStep;
    protected $process;

    public function __construct(StepInterface $currentStep, StepInterface $nextStep, ProcessInterface $process)
    {
        $this->currentStep = $currentStep;
        $this->nextStep = $nextStep;
        $this->process = $process;
    }

    /**
     * @return StepInterface
     */
    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    /**
     * @return StepInterface
     */
    public function getNextStep()
    {
        return $this->nextStep;
    }

    /**
     * @return ProcessInterface
     */
    public function getProcess()
    {
        return $this->process;
    }
}