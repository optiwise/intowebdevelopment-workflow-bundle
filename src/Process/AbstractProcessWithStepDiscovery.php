<?php
/**
 * Enabled automatic step discovery based on the start and end step(s).
 */

namespace IntoWebDevelopment\WorkflowBundle\Process;

use Doctrine\Common\Collections\ArrayCollection;
use IntoWebDevelopment\WorkflowBundle\Exception\InfiniteRecursionStepDiscoveryException;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

abstract class AbstractProcessWithStepDiscovery extends AbstractProcess
{
    private array $processedSteps = [];

    private int $processedStepThreshold = 20;

    public function __construct()
    {
        parent::__construct();
        $this->setSteps($this->getStartStep());
    }

    /**
     * Iterate over all available steps.
     *
     * @throws InfiniteRecursionStepDiscoveryException
     */
    private function iterateDistinctlyOverSteps(StepInterface $currentStep, ArrayCollection &$steps = null): ?ArrayCollection
    {
        /** @psalm-var ArrayCollection<string, StepInterface> */
        $steps ??= new ArrayCollection();

        // Add our first call.
        $steps->set($currentStep->getName(), $currentStep);
        $this->logStep($currentStep->getName());

        foreach ($currentStep->getNextSteps() as $nextStep) {
            // Add our next step.
            /** @psalm-suppress PossiblyNullReference */
            $steps->set($nextStep->getName(), $nextStep);
            $this->logStep($nextStep->getName());

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

                    /** @psalm-suppress PossiblyNullReference */
                    if (!$steps->containsKey($nextSubStep->getName())) {
                        $this->iterateDistinctlyOverSteps($nextSubStep, $steps);
                    }
                }
            }
        }

        return $steps;
    }

    /**
     * Get all the steps.
     * When there are no steps available set the start step.
     *
     * @return ArrayCollection
     */
    public function getSteps(): ArrayCollection
    {
        if (0 === $this->stepCollection->count()) {
            $this->setSteps($this->getStartStep());
        }

        return $this->stepCollection;
    }

    /**
     * Internal function that helps us keep track of the steps and how many times we
     *
     * @internal
     */
    private function logStep(string $stepName): void
    {
        if (!isset($this->processedSteps[$stepName])) {
            $this->processedSteps[$stepName] = 0;
        }

        $this->processedSteps[$stepName]++;
    }

    /**
     * Gives us an indication if the recursion goes too wild.
     *
     * @param   string  $stepName
     * @return  bool
     * @internal
     */
    private function isThresholdReachedForStep(string $stepName): bool
    {
        return isset($this->processedSteps[$stepName]) && $this->processedSteps[$stepName] > $this->processedStepThreshold;
    }

    /**
     * Assign all the steps to the step collection
     */
    private function setSteps(StepInterface $currentStep): void
    {
        $stepCollection = $this->iterateDistinctlyOverSteps($currentStep);

        if ($stepCollection === null) {
            throw new \InvalidArgumentException('No next steps found for the given current step.');
        }

        $this->stepCollection = $stepCollection;
    }
}