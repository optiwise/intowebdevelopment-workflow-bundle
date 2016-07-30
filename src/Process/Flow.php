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
use IntoWebDevelopment\WorkflowBundle\Step\StepFlagInterface;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;
use IntoWebDevelopment\WorkflowBundle\Util\StepUtil;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    use ContainerAwareTrait;

    public function __construct(ValidatorInterface $validator, EventDispatcherInterface $eventDispatcher, TokenStorageInterface $tokenStorage)
    {
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
        $this->tokenStorage = $tokenStorage;

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

        $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_ALLOWED_TO_STEP, new StepEvent($currentStep, $nextStep, $this->process, $this->getUserIfTokenHasOne()));

        // Execute the step actions that are assigned to the current step.
        $this->executeStepActions($currentStep, $nextStep);
        // Execute the step actions that need to be executed when entering the new step.
        $this->executeStepActions($nextStep, null, $nextStep->getPreActions());

        $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_STEPPING_COMPLETED, new StepEvent($currentStep, $nextStep, $this->process, $this->getUserIfTokenHasOne()));

        $automatedNextSteps = (new StepUtil())->getAutomatedSteps($nextStep->getNextSteps());

        // We can only move to the next step when we only have one next step available.
        if ($nextStep->hasNextSteps() && in_array(StepFlagInterface::FLAG_IS_AUTOMATED, $nextStep->getFlags()) && 1 === count($automatedNextSteps)) {
            $automatedNextStep = $automatedNextSteps[0];

            if ($this->isPossibleToMoveToNextStep($automatedNextStep)) {
                // See if it's possible to transition to the next step.
                $this->moveToNextStep($automatedNextStep, $nextStep);
            }
        }
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

        $this->eventDispatcher->dispatch(Events::BEFORE_VALIDATE_CURRENT_STEP, new ValidateStepEvent($currentStep, $this->getUserIfTokenHasOne()));
        $this->eventDispatcher->dispatch(Events::BEFORE_VALIDATE_NEXT_STEP, new ValidateStepEvent($nextStep, $this->getUserIfTokenHasOne()));

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
     * @param   StepInterface           $step
     * @param   StepInterface|null      $nextStep
     * @param   array[ActionInterface]  $stepActionArray
     * @return  void
     */
    public function executeStepActions(StepInterface $step, StepInterface $nextStep = null, array $stepActionArray = array())
    {
        if (0 === count($stepActionArray)) {
            $stepActionArray = $step->getActions();
        }

        /**
         * @var ActionInterface|ContainerAwareActionInterface $action
         */
        foreach ($stepActionArray as $action) {
            /*
             * @TODO This is the off-side of dealing with actions that really depend on services. Perhaps we can make all dependable actions private services, but that's for later.
             */
            if ($action instanceof ContainerAwareActionInterface) {
                // Set the service container
                $action->setContainer($this->container);
            }

            // Dispatch two events, one before the action
            $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_BEFORE_ACTION, new RunActionEvent($step, $action, $this->process, $nextStep));
            // Run action
            $actionResult = $action->run($step);
            // And one after the action has ran
            $this->eventDispatcher->dispatch(Events::PROCESS_FLOW_AFTER_ACTION, new RunActionEvent($step, $action, $this->process, $nextStep, $actionResult));
        }
    }

    /**
     * @param   StepInterface $currentStep
     * @return  StepInterface
     * @throws  TooManyStepsPossibleException
     */
    protected function getNextStepFromProcessWhenNull(StepInterface $currentStep)
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
    protected function getCurrentStepFromProcess(StepInterface $stepOverride = null)
    {
        if ($stepOverride === null) {
            return $this->process->getCurrentStep();
        }

        return $stepOverride;
    }

    /**
     * @return mixed
     */
    protected function getUserIfTokenHasOne()
    {
        if (($token = $this->tokenStorage->getToken()) && $token instanceof TokenInterface) {
            return $token->getUser();
        }

        return null;
    }
}