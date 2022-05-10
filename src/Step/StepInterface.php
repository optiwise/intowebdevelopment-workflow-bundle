<?php

namespace IntoWebDevelopment\WorkflowBundle\Step;

use IntoWebDevelopment\WorkflowBundle\Action\ActionInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface StepInterface
{
    /**
     * Returns a collection of possible next steps.
     *
     * @return  StepInterface[]
     */
    public function getNextSteps(): array;

    /**
     * Contains an array with one or more actions that will be executed when you transition to this step.
     *
     * @return ActionInterface[]
     */
    public function getPreActions(): array;

    /**
     * Contains an array with one or more actions that will be executed when you transition to the next step.
     *
     * @return  array<ActionInterface>
     * @psalm-return list<ActionInterface>
     */
    public function getActions(): array;

    /**
     * @return  ConstraintViolationListInterface
     */
    public function validate(): ConstraintViolationListInterface;

    /**
     * @return  array
     */
    public function getFlags(): array;

    /**
     * Contains the friendly name of the step.
     *
     * @return  string
     */
    public function getLabel(): string;

    /**
     * @return  mixed
     */
    public function getData(): mixed;

    /**
     * @param   mixed   $data
     * @return  $this
     */
    public function setData(mixed $data): static;

    /**
     * An unique identifier for this workflow step.
     *
     * @return  string
     */
    public function getName(): string;

    /**
     * @return  bool
     */
    public function hasNextSteps(): bool;

    /**
     * @return string
     */
    public function __toString(): string;
}