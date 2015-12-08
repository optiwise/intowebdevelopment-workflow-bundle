<?php

namespace IntoWebDevelopment\WorkflowBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use IntoWebDevelopment\WorkflowBundle\Process\ProcessInterface;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
abstract class AbstractWorkflowEventLog
{
    protected $id;

    /**
     * @ORM\Column(name="process", type="string", nullable=false)
     *
     * @var ProcessInterface
     */
    protected $process;

    /**
     * @ORM\Column(name="step", type="string", nullable=false)
     *
     * @var StepInterface
     */
    protected $step;

    /**
     * @ORM\Column(name="event_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $eventDate;

    /**
     * @ORM\OneToOne(targetEntity="IntoWebDevelopment\WorkflowBundle\Model\AbstractWorkflowEventLog")
     * @ORM\JoinColumn(nullable=true)
     *
     * @var StepInterface
     */
    protected $previous;

    /**
     * @ORM\Column(name="next_step", type="string", nullable=true)
     *
     * @var StepInterface
     */
    protected $nextStep;

    public function __construct()
    {
        $this->eventDate = new \DateTime();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->process,
            $this->step,
            $this->eventDate
        ));
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        list(
            $this->id,
            $this->process,
            $this->step,
            $this->eventDate
        ) = $data;
    }

    /**
     * @return string
     */
    public function getProcess()
    {
        return $this->process->getName();
    }

    /**
     * @param ProcessInterface $process
     * @return $this
     */
    public function setProcess($process)
    {
        $this->process = $process->getName();
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * @return StepInterface
     */
    public function getNextStep()
    {
        return $this->nextStep;
    }

    /**
     * @param StepInterface $nextStep
     * @return $this
     */
    public function setNextStep(StepInterface $nextStep)
    {
        $this->setPrevious($this);
        $this->nextStep = $nextStep;

        return $this;
    }

    /**
     * @param AbstractWorkflowEventLog $previous
     * @return $this
     */
    public function setPrevious(AbstractWorkflowEventLog $previous = null)
    {
        $this->previous = $previous;
        return $this;
    }

    /**
     * @return StepInterface
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param StepInterface $step
     * @return $this
     */
    public function setStep($step)
    {
        $this->step = $step;

        return $this;
    }
}