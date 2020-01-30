<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 */
class Task
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $submitter_id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $processor_id;

    /**
     * @ORM\Column(type="text")
     */
    private $command;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $task_created;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $task_started;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $task_completed;

    public function __construct($submitter_id, $command)
    {
        $this->setSubmitterId($submitter_id);
        $this->setCommand($command);
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function getSubmitterId()
    {
        return $this->submitter_id;
    }

    public function setSubmitterId($submitter_id)
    {
        $this->submitter_id = $submitter_id;

        return $this;
    }

    public function getProcessorId()
    {
        return $this->processor_id;
    }

    public function setProcessorId($processor_id)
    {
        $this->processor_id = $processor_id;

        return $this;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    public function getTaskCreated()
    {
        return $this->task_created;
    }

    public function setTaskCreated($task_created)
    {
        $this->task_created = $task_created;

        return $this;
    }

    public function getTaskStarted()
    {
        return $this->task_started;
    }

    public function setTaskStarted($task_started)
    {
        $this->task_started = $task_started;

        return $this;
    }

    public function getTaskCompleted()
    {
        return $this->task_completed;
    }

    public function setTaskCompleted(?int $task_completed)
    {
        $this->task_completed = $task_completed;

        return $this;
    }
    
    public function toArray() {
        $result = [
            "id"             => $this->getId(),
            "submitter_id"   => $this->getSubmitterId(),
            "processor_id"   => $this->getProcessorId(),
            "command"        => $this->getCommand(),
            "task_created"   => $this->getTaskCreated(),
            "task_started"   => $this->getTaskStarted(),
            "task_completed" => $this->getTaskCompleted(),
        ];
        
        return $result;
    }
}
