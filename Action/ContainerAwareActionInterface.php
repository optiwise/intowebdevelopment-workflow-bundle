<?php

namespace IntoWebDevelopment\WorkflowBundle\Action;


interface ContainerAwareActionInterface extends ActionInterface
{
    public function setContainer($container);
}