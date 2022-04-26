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
use IntoWebDevelopment\WorkflowBundle\Util\StepUtil;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class Flow implements FlowInterface
{
    protected ProcessInterface $process;

    use ContainerAwareTrait;

    public function __construct(protected ValidatorInterface $validator, protected EventDispatcherInterface $eventDispatcher, protected TokenStorageInterface $tokenStorage)
    {
    }

    public function setProcess(ProcessInterface $process): static
    {
        $this->process = $process;
        return $this;
    }

    public function getProcess(): ProcessInterface
    {
        return $this->process;
    }

    /**
     * When no errors are found we are allowed to move to the next step.
     *
     * @param   StepInterface|null          $nextStep
     * @param   StepInterface|null          $currentStep
     * @throws  NotPossibleToMoveToNextStepException|TooManyStepsPossibleException
     */
    public function moveToNextStep(StepInterface $nextStep = null, StepInterface $currentStep = null): void
    {
        $currentStep = $this->getCurrentStepFromProcess($currentStep);

        if (!$this->isPossibleToMoveToNextStep($currentStep)) {
            throw new NotPossibleToMoveToNextStepException("Please check with the 'isPossibleToMoveToNextStep' method whether moving to the next step is possible.");
        }

        // When the next step is not given try to determine the next possible step.
        if (null === $nextStep) {
            if (null === $currentStep) {
                throw new \InvalidArgumentException('The current step must be given when no next step is given.');
            }

            $nextStep = $this->getNextStepFromProcessWhenNull($currentStep);
        }

        if (null === $currentStep) {
            throw new \InvalidArgumentException('The current step cannot be null at this point.');
        }

        $this->eventDispatcher->dispatch(new StepEvent($currentStep, $nextStep, $this->process, $this->getUserIfTokenHasOne()), Events::PROCESS_FLOW_ALLOWED_TO_STEP);

        // Execute the step actions that are assigned to the current step.
        $this->executeStepActions($currentStep, $nextStep);

        if (count($nextStep->getPreActions()) > 0) {
            // Execute the step actions that need to be executed when entering the new step.
            $this->executeStepActions($nextStep, null, ... $nextStep->getPreActions());
        }

        $this->eventDispatcher->dispatch(new StepEvent($currentStep, $nextStep, $this->process, $this->getUserIfTokenHasOne()), Events::PROCESS_FLOW_STEPPING_COMPLETED);

        $automatedNextSteps = StepUtil::filterAutomatedSteps($nextStep->getNextSteps());

        // We can only move to the next step when we only have one next step available.
        if (count($automatedNextSteps) === 1) {
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
    public function isPossibleToMoveToNextStep(StepInterface $nextStep = null, StepInterface $currentStep = null): bool
    {
        $currentStep = $this->getCurrentStepFromProcess($currentStep);

        // When the next step is not given try to determine the next possible step.
        if (null === $nextStep) {
            if (null === $currentStep) {
                throw new \InvalidArgumentException('The current step must be given when no next step is given.');
            }

            $nextStep = $this->getNextStepFromProcessWhenNull($currentStep);
        }

        if ($currentStep === null) {
            throw new \InvalidArgumentException('The current step must be given.');
        }

        // Inherit the data from the current step
        if (null === $nextStep->getData()) {
            $nextStep->setData($currentStep->getData());
        }

        // The process does not contain the current and/or the next step. Return false just to be safe.
        if (false === $this->process->getSteps()->containsKey($currentStep->getName()) || false === $this->process->getSteps()->containsKey($nextStep->getName())) {
            return false;
        }

        $this->eventDispatcher->dispatch(new ValidateStepEvent($currentStep, $this->getUserIfTokenHasOne()), Events::BEFORE_VALIDATE_CURRENT_STEP);
        $this->eventDispatcher->dispatch(new ValidateStepEvent($nextStep, $this->getUserIfTokenHasOne()), Events::BEFORE_VALIDATE_NEXT_STEP);

        return 0 === $currentStep->validate()->count() && 0 === $nextStep->validate()->count();
    }

    /**
     * Get all the constraint validation messages.
     */
    public function getValidationList(StepInterface $currentStep = null): ConstraintViolationListInterface
    {
        $current = $this->getCurrentStepFromProcess($currentStep);

        if ($current === null) {
            throw new \InvalidArgumentException('The current step must be given when there is no current step set.');
        }

        return $current->validate();
    }

    /**
     * Get all the constraint validation messages.
     *
     * @psalm-return list<\Stringable|string>
     */
    public function getValidationMessages(StepInterface $currentStep = null): array
    {
        $errorMessages = [];

        foreach ($this->getValidationList($currentStep) as $validationMessage) {
            $errorMessages[] = $validationMessage->getMessage();
        }

        return $errorMessages;
    }

    public function executeStepActions(StepInterface $step, StepInterface $nextStep = null, ActionInterface... $stepActionArray): void
    {
        $actions = 0 === count($stepActionArray) ? $step->getActions() : $stepActionArray;

        /**
         * @var ActionInterface|ContainerAwareActionInterface $action
         */
        foreach ($actions as $action) {
            /*
             * @TODO This is the off-side of dealing with actions that really depend on services. Perhaps we can make all dependable actions private services, but that's for later.
             */
            if ($action instanceof ContainerAwareActionInterface) {
                // Set the service container
                $action->setContainer($this->container);
            }

            // Dispatch two events, one before the action
            $this->eventDispatcher->dispatch(new RunActionEvent($step, $action, $this->process, $nextStep), Events::PROCESS_FLOW_BEFORE_ACTION);
            // Run action
            $actionResult = $action->run($step);
            // And one after the action has run
            $this->eventDispatcher->dispatch(new RunActionEvent($step, $action, $this->process, $nextStep, $actionResult), Events::PROCESS_FLOW_AFTER_ACTION);
        }
    }

    /**
     * @param StepInterface $currentStep
     * @return StepInterface
     * @throws TooManyStepsPossibleException
     */
    protected function getNextStepFromProcessWhenNull(StepInterface $currentStep): StepInterface
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

    protected function getCurrentStepFromProcess(StepInterface $stepOverride = null): ?StepInterface
    {
        return $stepOverride ?? $this->process->getCurrentStep();
    }

    protected function getUserIfTokenHasOne(): ?UserInterface
    {
        if (($token = $this->tokenStorage->getToken()) && $token instanceof TokenInterface) {
            return $token->getUser();
        }

        return null;
    }
}