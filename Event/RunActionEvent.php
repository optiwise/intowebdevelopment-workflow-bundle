<?php

namespace IntoWebDevelopment\WorkflowBundle\Event;

use IntoWebDevelopment\WorkflowBundle\Action\ActionInterface;
use IntoWebDevelopment\WorkflowBundle\Process\ProcessInterface;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;
use Symfony\Component\EventDispatcher\Event;

class RunActionEvent extends Event
{
    protected $nextStep;
    protected $currentStep;
    protected $action;
    protected $process;
    protected $actionResult;

    public function __construct(StepInterface $currentStep, ActionInterface $action, ProcessInterface $process, StepInterface $nextStep = null, $actionResult = null)
    {
        $this->currentStep = $currentStep;
        $this->nextStep = $nextStep;
        $this->action = $action;
        $this->process = $process;
        $this->actionResult = $actionResult;
    }

    /**
     * @return StepInterface
     */
    public function getNextStep()
    {
        return $this->nextStep;
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

    /**
     * @return null
     */
    public function getActionResult()
    {
        return $this->actionResult;
    }

    /**
     * @param mixed $actionResult
     * @return RunActionEvent
     */
    public function setActionResult($actionResult)
    {
        $this->actionResult = $actionResult;
        return $this;
    }
}