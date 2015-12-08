<?php

namespace IntoWebDevelopment\WorkflowBundle\Util;

use IntoWebDevelopment\WorkflowBundle\Step\StepFlagInterface;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

class StepUtil
{
    /**
     * Filter out all steps except the automated steps.
     *
     * @param   array   $steps
     * @return  array
     */
    public function getAutomatedSteps(array $steps)
    {
        return array_filter($steps, function(StepInterface $step) {
            return in_array(StepFlagInterface::FLAG_IS_AUTOMATED, $step->getFlags(), true);
        });
    }
}