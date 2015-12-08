<?php

namespace IntoWebDevelopment\WorkflowBundle\Validator\Step;

use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

interface ValidateWorkflowStepEvent
{
    public function validate(StepInterface $workflowStep);
}