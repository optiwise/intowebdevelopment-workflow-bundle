<?php

namespace IntoWebDevelopment\WorkflowBundle\Process;

use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

interface FlowInterface
{
    public function moveToNextStep(StepInterface $nextStep, StepInterface $currentStep = null);

    public function isPossibleToMoveToNextStep(StepInterface $nextStep, StepInterface $currentStep = null);
}