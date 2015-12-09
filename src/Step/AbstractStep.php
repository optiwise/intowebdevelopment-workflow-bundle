<?php

namespace IntoWebDevelopment\WorkflowBundle\Step;

use Symfony\Component\Validator\ConstraintViolationList;

abstract class AbstractStep implements StepInterface
{
    protected $data;

    /**
     * @inheritdoc
     */
    public function getActions()
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    public function getPreActions()
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
     * @inheritdoc
     */
    public function hasNextSteps()
    {
        return 0 !== count($this->getNextSteps());
    }

    /**
     * Check if the next step with the given name exists exists.
     *
     * @internal
     * @param   string  $name
     * @return  boolean
     */
    public function nextStepContains($name)
    {
        return count(array_filter($this->getNextSteps(), function($nextStep) use ($name) {
            /**
             * @var StepInterface $nextStep
             */
            return $nextStep->getName() === $name;
        })) > 0;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}