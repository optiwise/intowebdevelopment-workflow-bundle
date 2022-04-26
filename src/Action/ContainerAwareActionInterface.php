<?php

namespace IntoWebDevelopment\WorkflowBundle\Action;

use Psr\Container\ContainerInterface;

interface ContainerAwareActionInterface extends ActionInterface
{
    public function setContainer(ContainerInterface $container): void;
}