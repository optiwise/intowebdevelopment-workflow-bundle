<?php

namespace IntoWebDevelopment\WorkflowBundle\Process;

use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

interface ProcessInterface
{
    /**
     * Get all available steps for this process. The order in which the steps are added
     * is irrelevant.
     *
     * @return  \Doctrine\Common\Collections\ArrayCollection[StepInterface]
     */
    public function getSteps();

    /**
     * @return  StepInterface
     */
    public function getStartStep();

    /**
     * @return  array[StepInterface]
     */
    public function getEndSteps();

    /**
     * @return  null|StepInterface
     */
    public function getCurrentStep();

    /**
     * @param   string|StepInterface    $currentStepNameOrObject
     * @param   mixed                   $data
     * @throws  \IntoWebDevelopment\WorkflowBundle\Exception\CurrentStepNotFoundInStepCollectionException
     * @throws  \IntoWebDevelopment\WorkflowBundle\Exception\StepCollectionIsEmptyException
     */
    public function setCurrentStep($currentStepNameOrObject, $data = null);

    /**
     * @return  string
     */
    public function getName();
}