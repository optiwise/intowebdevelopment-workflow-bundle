<?php

namespace IntoWebDevelopment\WorkflowBundle\Action;

interface ContainerAwareActionInterface extends ActionInterface
{
    public function setContainer(\Symfony\Component\DependencyInjection\ContainerInterface $container);
}