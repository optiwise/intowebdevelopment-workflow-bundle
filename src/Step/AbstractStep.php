<?php

namespace IntoWebDevelopment\WorkflowBundle\Step;

use Symfony\Component\Validator\ConstraintViolationList;

abstract class AbstractStep implements StepInterface, \Stringable
{
    protected mixed $data = null;

    /**
     * @inheritdoc
     */
    public function getActions(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPreActions(): array
    {
        return [];
    }

    /**
     * @return ConstraintViolationList
     */
    public function validate(): ConstraintViolationList
    {
        return new ConstraintViolationList();
    }

    /**
     * Get the data.
     *
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Sets the data for the step
     *
     * @param mixed $data
     * @return $this
     */
    public function setData(mixed $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasNextSteps(): bool
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
    public function nextStepContains(string $name): bool
    {
        return count(array_filter($this->getNextSteps(), static fn(StepInterface $nextStep) => $nextStep->getName() === $name)) > 0;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}