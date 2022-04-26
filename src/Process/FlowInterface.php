<?php

namespace IntoWebDevelopment\WorkflowBundle\Process;

use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

interface FlowInterface
{
    public function moveToNextStep(StepInterface $nextStep = null, StepInterface $currentStep = null): void;

    public function isPossibleToMoveToNextStep(StepInterface $nextStep = null, StepInterface $currentStep = null): bool;

    public function setProcess(ProcessInterface $process): static;

    public function getProcess(): ProcessInterface;

    /**
     * Get all the constraint validation messages.
     *
     * @psalm-return list<\Stringable|string>
     */
    public function getValidationMessages(StepInterface $currentStep = null): array;
}