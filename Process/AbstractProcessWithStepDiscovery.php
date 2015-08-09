<?php
/**
 * Enabled automatic step discovery based on the start and end step(s).
 */

namespace IntoWebDevelopment\WorkflowBundle\Process;

use Doctrine\Common\Collections\ArrayCollection;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

abstract class AbstractProcessWithStepDiscovery extends AbstractProcess implements ProcessInterface
{
    public function __construct()
    {
        parent::__construct();
        $this->setSteps($this->getStartStep());

        return $this;
    }

    public function getSteps()
    {
        if (0 === $this->stepCollection->count()) {
            $this->setSteps($this->getStartStep());
        }

        return $this->stepCollection;
    }

    /**
     * Iterate over all available steps.
     *
     * @param   StepInterface           $currentStep
     * @param   ArrayCollection|null    $steps
     * @return  ArrayCollection
     */
    private function iterateDistinctlyOverSteps(StepInterface $currentStep, ArrayCollection &$steps = null)
    {
        if (null === $steps) {
            $steps = new ArrayCollection();
        }

        // Add our first call.
        $steps->set($currentStep->getName(), $currentStep);

        /**
         * @var     $nextStep       StepInterface
         * @var     $nextSubStep    StepInterface
         */
        foreach ($currentStep->getNextSteps() as $nextStep) {
            // Add our next step.
            $steps->set($nextStep->getName(), $nextStep);

            if (count($nextStep->getNextSteps()) > 0) {
                foreach ($nextStep->getNextSteps() as $nextSubStep) {
                    // Check if we already have an item with the same class key.
                    if (false === $this->stepCollection->containsKey($nextSubStep->getName())) {
                        $this->iterateDistinctlyOverSteps($nextSubStep, $steps);
                    }
                }
            }
        }

        return $steps;
    }

    private function setSteps(StepInterface $currentStep)
    {
        $this->stepCollection = $this->iterateDistinctlyOverSteps($currentStep);
        return $this;
    }
}