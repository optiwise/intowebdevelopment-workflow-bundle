<?php

namespace IntoWebDevelopment\WorkflowBundle\Process;

use Doctrine\Common\Collections\ArrayCollection;
use IntoWebDevelopment\WorkflowBundle\Exception\CurrentStepNotFoundInStepCollectionException;
use IntoWebDevelopment\WorkflowBundle\Exception\StepCollectionIsEmptyException;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

abstract class AbstractProcess implements ProcessInterface
{
    /**
     * @var ArrayCollection
     */
    protected $stepCollection;

    /**
     * @var StepInterface
     */
    protected $currentStep;

    public function __construct()
    {
        $this->stepCollection = new ArrayCollection();
        return $this;
    }

    /**
     * @param   string|StepInterface    $currentStepNameOrObject
     * @param   mixed                   $data
     * @throws  CurrentStepNotFoundInStepCollectionException
     * @throws  StepCollectionIsEmptyException
     */
    public function setCurrentStep($currentStepNameOrObject, $data = null)
    {
        if (0 === $this->stepCollection->count()) {
            throw new StepCollectionIsEmptyException("We did not find any steps. Please make sure the stepCollection is filled.");
        }

        if (is_object($currentStepNameOrObject) && $currentStepNameOrObject instanceof StepInterface) {
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
        $this->currentStep->setData($data);
    }

    /**
     * @return  null|StepInterface
     */
    public function getCurrentStep()
    {
        if (null === $this->currentStep) {
            return $this->getStartStep();
        }

        return $this->currentStep;
    }

    private function throwCurrentStepNotFoundException($currentStepName)
    {
        return new CurrentStepNotFoundInStepCollectionException(sprintf("The given current step '%s' is not known in the current step collection.", $currentStepName));
    }
}