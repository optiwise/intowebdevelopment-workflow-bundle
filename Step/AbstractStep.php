<?php

namespace IntoWebDevelopment\WorkflowBundle\Step;

use Symfony\Component\Validator\ConstraintViolationList;

abstract class AbstractStep implements StepInterface
{
    protected $data;

    /**
     * Contains an array of actions.
     *
     * @return  array
     */
    public function getActions()
    {
        return array();
    }

    /**
     * @return ConstraintViolationList
     */
    public function validate()
    {
        return new ConstraintViolationList();
    }

    /**
     * Get the data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data for the step
     *
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Check if there are remaining steps left.
     *
     * @return bool
     */
    public function hasNextSteps()
    {
        return 0 !== count($this->getNextSteps());
    }

    public function __toString()
    {
        return $this->getName();
    }
}