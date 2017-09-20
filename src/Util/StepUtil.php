<?php

namespace IntoWebDevelopment\WorkflowBundle\Util;

use IntoWebDevelopment\WorkflowBundle\Step\StepFlagInterface;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

class StepUtil
{
    /**
     * Trigger a deprecation notice when initializing this utility.
     */
    public function __construct()
    {
        trigger_error("Please use the static method getAutomatedSteps.", E_USER_DEPRECATED);
    }

    /**
     * Filter out all steps except the automated steps.
     *
     * @param   array   $steps
     * @return  array
     */
    public function getAutomatedSteps(array $steps)
    {
        trigger_error("This method has to be replaced with the static method 'filterAutomatedSteps'", E_USER_DEPRECATED);
        return static::filterAutomatedSteps($steps);
    }

    /**
     * Filter out all steps except the automated steps.
     *
     * @param array $steps
     * @return array
     */
    static public function filterAutomatedSteps(array $steps)
    {
        return array_filter($steps, function(StepInterface $step) {
            return in_array(StepFlagInterface::FLAG_IS_AUTOMATED, $step->getFlags(), true);
        });
    }
}