<?php

namespace IntoWebDevelopment\WorkflowBundle\Step;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface StepInterface
{
    /**
     * Returns a collection of possible next steps.
     *
     * @return  array[StepInterface]
     */
    public function getNextSteps();

    /**
     * Contains an array with one or more actions.
     *
     * @return  array[ActionInterface]
     */
    public function getActions();

    /**
     * @return  ConstraintViolationListInterface
     */
    public function validate();

    /**
     * @return  array
     */
    public function getFlags();

    /**
     * Contains the friendly name of the step.
     *
     * @return  string
     */
    public function getLabel();

    /**
     * @return  mixed
     */
    public function getData();

    /**
     * @param   mixed   $stepData
     * @return  $this
     */
    public function setData($stepData);

    /**
     * An unique identifier for this workflow step.
     *
     * @return  string
     */
    public function getName();

    /**
     * @return  bool
     */
    public function hasNextSteps();
}