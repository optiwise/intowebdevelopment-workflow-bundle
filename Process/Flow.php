<?php

namespace IntoWebDevelopment\WorkflowBundle\Process;

use IntoWebDevelopment\WorkflowBundle\Action\ActionInterface;
use IntoWebDevelopment\WorkflowBundle\Action\ContainerAwareActionInterface;
use IntoWebDevelopment\WorkflowBundle\Event\RunActionEvent;
use IntoWebDevelopment\WorkflowBundle\Event\StepEvent;
use IntoWebDevelopment\WorkflowBundle\Event\ValidateStepEvent;
use IntoWebDevelopment\WorkflowBundle\Events;
use IntoWebDevelopment\WorkflowBundle\Exception\NotPossibleToMoveToNextStepException;
use IntoWebDevelopment\WorkflowBundle\Exception\TooManyStepsPossibleException;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Flow extends ContainerAware implements FlowInterface
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
     * @return ProcessInterface
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * When no errors are found we are allowed to move to the next step.
     *
     * @param   StepInterface|null          $nextStep
     * @param   StepInterface|null          $currentStep
     * @throws  NotPossibleToMoveToNextStepException
     */
    public function moveToNextStep(StepInterface $nextStep = null, StepInterface $currentStep = null)
    {
        $currentStep = $this->getCurrentStepFromProcess($currentStep);

        if (!$this->isPossibleToMoveToNextStep($currentStep)) {
            throw new NotPossibleToMoveToNextStepException("Please check with the 'isPossibleToMoveToNextStep' method whether moving to the next step is possible.");
        }

        // When the next step is not given try to determine the next possible step.
        if (null === $nextStep) {
            $nextStep = $this->getNextStepFromProcessWhenNull($currentStep);
        }

        $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_ALLOWED_TO_STEP, new StepEvent($currentStep, $nextStep, $this->process));

        /**
         * @var ActionInterface|ContainerAwareActionInterface $action
         */
        foreach ($currentStep->getActions() as $action) {
            /*
             * @TODO This is the off-side of dealing with actions that really depend on services. Perhaps we can make all dependable actions private services, but that's for later.
             */
            $hasContainerAwareActionInterface = count(array_filter((new \ReflectionClass($action))->getInterfaceNames(), function($value) {
                    return false !== strpos($value, "IntoWebDevelopment") && false !== strpos($value, "ContainerAwareActionInterface");
                })) === 1;

            if ($hasContainerAwareActionInterface) {
                // Set the service container
                $action->setContainer($this->container);
            }

            // Dispatch two events, one before the action
            $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_BEFORE_ACTION, new RunActionEvent($currentStep, $action, $this->process, $nextStep));
            // Run action
            $action->run($currentStep);
            // And one after the action has ran
            $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_AFTER_ACTION, new RunActionEvent($currentStep, $action, $this->process, $nextStep));
        }

        $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_STEPPING_COMPLETED, new StepEvent($currentStep, $nextStep, $this->process));
    }

    /**
     * @param   StepInterface|null   $nextStep
     * @param   StepInterface|null   $currentStep
     * @throws  TooManyStepsPossibleException
     * @return  bool
     */
    public function isPossibleToMoveToNextStep(StepInterface $nextStep = null, StepInterface $currentStep = null)
    {
        $currentStep = $this->getCurrentStepFromProcess($currentStep);

        // When the next step is not given try to determine the next possible step.
        if (null === $nextStep) {
            $nextStep = $this->getNextStepFromProcessWhenNull($currentStep);
        }

        // Inherit the data from the current step
        // @TODO: Perhaps make an option out of this so we can decide per step.
        if (null === $nextStep->getData()) {
            $nextStep->setData($currentStep->getData());
        }

        // The process does not contain the current and/or the next step. Return false just to be safe.
        if (false === $this->process->getSteps()->containsKey($currentStep->getName()) || false === $this->process->getSteps()->containsKey($nextStep->getName())) {
            return false;
        }

        $this->eventDispatcher->dispatch(Events::BEFORE_VALIDATE_CURRENT_STEP, new ValidateStepEvent($currentStep));
        $this->eventDispatcher->dispatch(Events::BEFORE_VALIDATE_NEXT_STEP, new ValidateStepEvent($nextStep));

        return 0 === $currentStep->validate()->count() && 0 === $nextStep->validate()->count();
    }

    /**
     * Get all the constraint validation messages.
     *
     * @param   StepInterface   $currentStep
     * @return  \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    public function getValidationList(StepInterface $currentStep = null)
    {
        return $this->getCurrentStepFromProcess($currentStep)->validate();
    }

    /**
     * Get all the constraint validation messages.
     *
     * @param   StepInterface   $currentStep
     * @return  array[string]
     */
    public function getValidationMessages(StepInterface $currentStep = null)
    {
        $errorMessages = array();

        foreach ($this->getValidationList($currentStep) as $validationMessage) {
            $errorMessages[] = $validationMessage->getMessage();
        }

        return $errorMessages;
    }

    /**
     * @param   StepInterface $currentStep
     * @return  StepInterface
     * @throws  TooManyStepsPossibleException
     */
    private function getNextStepFromProcessWhenNull(StepInterface $currentStep)
    {
        if ($currentStep->hasNextSteps() && count($currentStep->getNextSteps()) === 1) {
            // Get the first item of the next steps.
            return $currentStep->getNextSteps()[0];
        }

        throw new TooManyStepsPossibleException(sprintf(
            "It's not possible to automatically determine the next step because there is more than one option to choose from. Possible options are: %s",
            implode(",", $currentStep->getNextSteps())
        ));
    }

    /**
     * @param   StepInterface|null $stepOverride
     * @return  StepInterface|null
     */
    private function getCurrentStepFromProcess(StepInterface $stepOverride = null)
    {
        if ($stepOverride === null) {
            return $this->process->getCurrentStep();
        }

        return $stepOverride;
    }
}