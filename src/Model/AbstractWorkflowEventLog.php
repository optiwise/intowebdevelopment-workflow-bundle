<?php

namespace IntoWebDevelopment\WorkflowBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use IntoWebDevelopment\WorkflowBundle\Process\ProcessInterface;
use IntoWebDevelopment\WorkflowBundle\Step\StepInterface;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
abstract class AbstractWorkflowEventLog implements \Serializable
{
    protected mixed $id;

    /**
     * @ORM\Column(name="process", type="string", nullable=false)
     */
    protected string $process;

    /**
     * @ORM\Column(name="step", type="string", nullable=false)
     *
     * @var StepInterface
     */
    protected StepInterface $step;

    /**
     * @ORM\Column(name="event_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected \DateTime $eventDate;

    /**
     * @ORM\OneToOne(targetEntity="IntoWebDevelopment\WorkflowBundle\Model\AbstractWorkflowEventLog")
     * @ORM\JoinColumn(nullable=true)
     */
    protected ?AbstractWorkflowEventLog $previous;

    /**
     * @ORM\Column(name="next_step", type="string", nullable=true)
     *
     * @var StepInterface
     */
    protected StepInterface $nextStep;

    public function __construct()
    {
        $this->eventDate = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    public function serialize(): string
    {
        return serialize(array(
            $this->id,
            $this->process,
            $this->step,
            $this->eventDate
        ));
    }

    public function unserialize(string $data): void
    {
        $decoded = unserialize($data, [ 'allowed_classes' => [ __CLASS__ ] ]);

        [$this->id, $this->process, $this->step, $this->eventDate] = $decoded;
    }

    public function getProcess(): string
    {
        return $this->process;
    }

    public function setProcess(ProcessInterface $process): static
    {
        $this->process = $process->getName();

        return $this;
    }

    public function getEventDate(): \DateTime
    {
        return $this->eventDate;
    }

    public function getNextStep(): StepInterface
    {
        return $this->nextStep;
    }

    public function setNextStep(StepInterface $nextStep): static
    {
        $this->setPrevious($this);
        $this->nextStep = $nextStep;

        return $this;
    }

    public function setPrevious(?AbstractWorkflowEventLog $previous = null): static
    {
        $this->previous = $previous;

        return $this;
    }

    public function getStep(): StepInterface
    {
        return $this->step;
    }

    public function setStep(StepInterface $step): static
    {
        $this->step = $step;

        return $this;
    }
}