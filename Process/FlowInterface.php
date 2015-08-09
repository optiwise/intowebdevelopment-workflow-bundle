<?php

namespace IntoWebDevelopment\WorkflowBundle\Process;

use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

interface FlowInterface
{
    public function moveToNextStep(StepInterface $nextStep = null, StepInterface $currentStep = null);

    public function isPossibleToMoveToNextStep(StepInterface $nextStep = null, StepInterface $currentStep = null);

    /**
     * @param ProcessInterface $process
     * @return $this
     */
    public function setProcess(ProcessInterface $process);

    /**
     * @return ProcessInterface
     */
    public function getProcess();

    /**
     * Get all the constraint validation messages.
     *
     * @param   StepInterface   $currentStep
     * @return  \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    public function getValidationMessages(StepInterface $currentStep = null);
}