<?php

namespace IntoWebDevelopment\WorkflowBundle\Process;

use Doctrine\Common\Collections\ArrayCollection;
use IntoWebDevelopment\WorkflowBundle\Exception\CurrentStepNotFoundInStepCollectionException;
use IntoWebDevelopment\WorkflowBundle\Exception\StepCollectionIsEmptyException;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

interface ProcessInterface
{
    /**
     * Get all available steps for this process. The order in which the steps are added
     * is irrelevant.
     *
     * @return  ArrayCollection
     */
    public function getSteps(): ArrayCollection;

    /**
     * @return  StepInterface
     */
    public function getStartStep(): StepInterface;

    /**
     * @return  StepInterface[]
     */
    public function getEndSteps(): array;

    /**
     * @return  null|StepInterface
     */
    public function getCurrentStep(): ?StepInterface;

    /**
     * @param   string|StepInterface    $currentStepNameOrObject
     * @param   mixed                   $data
     * @throws  CurrentStepNotFoundInStepCollectionException
     * @throws  StepCollectionIsEmptyException
     */
    public function setCurrentStep(string|StepInterface $currentStepNameOrObject, mixed $data = null): void;

    /**
     * @return  string
     */
    public function getName(): string;
}