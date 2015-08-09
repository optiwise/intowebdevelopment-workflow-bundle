<?php

namespace IntoWebDevelopment\WorkflowBundle\Action;

use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

interface ActionInterface
{
    public function run(StepInterface $step);
}