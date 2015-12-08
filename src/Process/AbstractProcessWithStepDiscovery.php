<?php
/**
 * Enabled automatic step discovery based on the start and end step(s).
 */

namespace IntoWebDevelopment\WorkflowBundle\Process;

use Doctrine\Common\Collections\ArrayCollection;
use IntoWebDevelopment\WorkflowBundle\Exception\InfiniteRecursionStepDiscoveryException;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

abstract class AbstractProcessWithStepDiscovery extends AbstractProcess implements ProcessInterface
{
    private $processedSteps = array();
    // @TODO: Make this configurable.
    private $processedStepThreshold = 20;

    public function __construct()
    {
        parent::__construct();
        $this->setSteps($this->getStartStep());

        return $this;
    }

    /**
     * Get all the steps.
     * When there are no steps available set the start step.
     *
     * @return ArrayCollection
     */
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
        $this->logStep($currentStep->getName());

        /**
         * @var     $nextStep       StepInterface
         * @var     $nextSubStep    StepInterface
         */
        foreach ($currentStep->getNextSteps() as $nextStep) {
            // Add our next step.
            $steps->set($nextStep->getName(), $nextStep);
            $this->logStep($nextStep->getName());

            if (count($nextStep->getNextSteps()) > 0) {
                foreach ($nextStep->getNextSteps() as $nextSubStep) {
                    // Check if we already have an item with the same class key.
                    if (false === $this->stepCollection->containsKey($nextSubStep->getName())) {
                        if ($this->isThresholdReachedForStep($nextSubStep->getName())) {
                            throw new InfiniteRecursionStepDiscoveryException(sprintf(
                                "The threshold of %d was reached while trying to detect an infinite loop. The step that caused this exception: %s, current step: %s",
                                $this->processedStepThreshold,
                                $nextSubStep->getName(),
                                $currentStep->getName()
                            ));
                        }

                        if (!$steps->containsKey($nextSubStep->getName())) {
                            $this->iterateDistinctlyOverSteps($nextSubStep, $steps);
                        }
                    }
                }
            }
        }

        return $steps;
    }

    /**
     * Internal function that helps us keep track of the steps and how many times we
     *
     * @internal
     */
    private function logStep($stepName)
    {
        if (!isset($this->processedSteps[$stepName])) {
            $this->processedSteps[$stepName] = 0;
        }

        $this->processedSteps[$stepName]++;
    }

    /**
     * Gives us an indication if the recursion goes too wild.
     *
     * @internal
     * @param   string  $stepName
     * @return  bool
     */
    private function isThresholdReachedForStep($stepName)
    {
        return isset($this->processedSteps[$stepName]) && $this->processedSteps[$stepName] > $this->processedStepThreshold;
    }

    /**
     * Assign all the steps to the step collection
     *
     * @param   StepInterface $currentStep
     * @return  $this
     */
    private function setSteps(StepInterface $currentStep)
    {
        $this->stepCollection = $this->iterateDistinctlyOverSteps($currentStep);
        return $this;
    }
}