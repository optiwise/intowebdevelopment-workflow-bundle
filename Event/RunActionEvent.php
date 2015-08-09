<?php

namespace IntoWebDevelopment\WorkflowBundle\Event;

use IntoWebDevelopment\WorkflowBundle\Action\ActionInterface;
use IntoWebDevelopment\WorkflowBundle\Process\ProcessInterface;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;
use Symfony\Component\EventDispatcher\Event;

class RunActionEvent extends Event
{
    protected $currentStep;
    protected $action;
    protected $process;

    public function __construct(StepInterface $currentStep, ActionInterface $action, ProcessInterface $process)
    {
        $this->currentStep = $currentStep;
        $this->action = $action;
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
     * @return ProcessInterface
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return ActionInterface
     */
    public function getAction()
    {
        return $this->action;
    }
}