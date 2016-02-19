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
    protected $user;

    public function __construct(StepInterface $currentStep, StepInterface $nextStep, ProcessInterface $process, $token = null)
    {
        $this->currentStep = $currentStep;
        $this->nextStep = $nextStep;
        $this->process = $process;
        $this->user = $token;
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

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}