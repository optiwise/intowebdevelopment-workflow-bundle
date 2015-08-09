<?php

namespace IntoWebDevelopment\WorkflowBundle\Process;

use IntoWebDevelopment\WorkflowBundle\Action\ActionInterface;
use IntoWebDevelopment\WorkflowBundle\Event\RunActionEvent;
use IntoWebDevelopment\WorkflowBundle\Event\StepEvent;
use IntoWebDevelopment\WorkflowBundle\Event\ValidateStepEvent;
use IntoWebDevelopment\WorkflowBundle\Events;
use IntoWebDevelopment\WorkflowBundle\Exception\NotPossibleToMoveToNextStepException;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Flow implements FlowInterface
{
    /**
     * @var ProcessInterface
     */
    protected $process;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(ValidatorInterface $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * @param ProcessInterface $process
     * @return $this
     */
    public function setProcess(ProcessInterface $process)
    {
        $this->process = $process;
        return $this;
    }

    /**
     * When no errors are found we are allowed to move to the next step.
     *
     * @param   StepInterface        $nextStep
     * @param   StepInterface|null   $currentStep
     * @throws  NotPossibleToMoveToNextStepException
     */
    public function moveToNextStep(StepInterface $nextStep, StepInterface $currentStep = null)
    {
        $currentStep = $this->getCurrentStepFromProcess($currentStep);

        if (!$this->isPossibleToMoveToNextStep($currentStep)) {
            throw new NotPossibleToMoveToNextStepException("Please check with the 'isPossibleToMoveToNextStep' method whether moving to the next step is possible.");
        }

        $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_ALLOWED_TO_STEP, new StepEvent($currentStep, $nextStep, $this->process));

        /**
         * @var ActionInterface $action
         */
        foreach ($currentStep->getActions() as $action) {
            // Dispatch two events, one before the action
            $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_BEFORE_ACTION, new RunActionEvent($currentStep, $action, $this->process));
            // Run action
            $action->run($currentStep);
            // And one after the action has ran
            $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_AFTER_ACTION, new RunActionEvent($currentStep, $action, $this->process));
        }

        $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_STEPPING_COMPLETED, new StepEvent($currentStep, $nextStep, $this->process));
    }

    /**
     * @param   StepInterface        $nextStep
     * @param   StepInterface|null   $currentStep
     * @return  bool
     */
    public function isPossibleToMoveToNextStep(StepInterface $nextStep, StepInterface $currentStep = null)
    {
        $currentStep = $this->getCurrentStepFromProcess($currentStep);

        if (false === $this->process->getSteps()->containsKey($currentStep->getName()) || false === $this->process->getSteps()->containsKey($nextStep->getName())) {
            return false;
        }

        $this->eventDispatcher->dispatch(Events::BEFORE_VALIDATE_STEP, new ValidateStepEvent($currentStep));

        return 0 === $currentStep->validate()->count();
    }

    /**
     * Get all the constraint validation messages.
     *
     * @param   StepInterface   $currentStep
     * @return  \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    public function getValidationMessages(StepInterface $currentStep = null)
    {
        return $this->getCurrentStepFromProcess($currentStep)->validate();
    }

    private function getCurrentStepFromProcess(StepInterface $stepOverride = null)
    {
        if ($stepOverride === null) {
            return $this->process->getCurrentStep();
        }

        return $stepOverride;
    }
}