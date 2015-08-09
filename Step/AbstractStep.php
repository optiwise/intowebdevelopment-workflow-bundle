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

    public function validate()
    {
        return new ConstraintViolationList();
    }

    public function getData()
    {
        return $this->data;
    }

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