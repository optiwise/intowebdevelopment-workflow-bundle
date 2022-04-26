<?php

namespace IntoWebDevelopment\WorkflowBundle\Process;

use Doctrine\Common\Collections\ArrayCollection;
use IntoWebDevelopment\WorkflowBundle\Exception\StepNotFoundInStepCollectionException;
use IntoWebDevelopment\WorkflowBundle\Exception\CurrentStepNotFoundInStepCollectionException;
use IntoWebDevelopment\WorkflowBundle\Exception\StepCollectionIsEmptyException;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

abstract class AbstractProcess implements ProcessInterface
{
    protected ArrayCollection $stepCollection;

    protected ?StepInterface $currentStep;

    public function __construct()
    {
        $this->stepCollection = new ArrayCollection();
    }

    /**
     * @param   string|StepInterface    $currentStepNameOrObject
     * @param   mixed                   $data
     * @throws  CurrentStepNotFoundInStepCollectionException
     * @throws  StepCollectionIsEmptyException
     */
    public function setCurrentStep(string|StepInterface $currentStepNameOrObject, mixed $data = null): void
    {
        if (0 === $this->stepCollection->count()) {
            throw new StepCollectionIsEmptyException("We did not find any steps. Please make sure the stepCollection is filled.");
        }

        if ($currentStepNameOrObject instanceof StepInterface) {
            if (false === $this->stepCollection->containsKey($currentStepNameOrObject->getName())) {
                throw $this->throwCurrentStepNotFoundException($currentStepNameOrObject->getName());
            }

            $this->currentStep = $currentStepNameOrObject;
            return;
        }

        if (false === $this->stepCollection->containsKey($currentStepNameOrObject)) {
            throw $this->throwCurrentStepNotFoundException($currentStepNameOrObject);
        }

        $this->currentStep = $this->stepCollection->get($currentStepNameOrObject);
        if ($this->currentStep === null) {
            throw new \InvalidArgumentException('The current step is null.');
        }

        $this->currentStep->setData($data);
    }

    /**
     * @return  StepInterface|null
     */
    public function getCurrentStep(): ?StepInterface
    {
        return $this->currentStep ?? $this->getStartStep();
    }

    /**
     * @param   string $name
     * @throws  StepCollectionIsEmptyException
     * @throws  StepNotFoundInStepCollectionException
     * @return  StepInterface
     */
    public function getStepInstanceByName(string $name): StepInterface
    {
        if (0 === $this->stepCollection->count()) {
            throw new StepCollectionIsEmptyException("We did not find any steps. Please make sure the stepCollection is filled.");
        }

        if ($this->stepCollection->containsKey($name)) {
            if ($step = $this->stepCollection->get($name)) {
                return $step;
            }

            throw new \InvalidArgumentException(sprintf('The step with the name %s is null.', $name));
        }

        throw new StepNotFoundInStepCollectionException(sprintf("The given step '%s' is not known in the step collection.", $name));
    }

    private function throwCurrentStepNotFoundException(string $currentStepName): CurrentStepNotFoundInStepCollectionException
    {
        return new CurrentStepNotFoundInStepCollectionException(sprintf("The given current step '%s' is not known in the current step collection.", $currentStepName));
    }
}