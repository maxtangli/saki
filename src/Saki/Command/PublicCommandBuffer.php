<?php

namespace Saki\Command;

use Saki\Command\PublicCommand\ChowCommand;
use Saki\Command\PublicCommand\KongCommand;
use Saki\Command\PublicCommand\PungCommand;
use Saki\Command\PublicCommand\RonCommand;

/**
 * @package Saki\Command
 */
class PublicCommandBuffer {
    private $priorityMap;
    private $value;

    function __construct() {
        $this->priorityMap = [
            RonCommand::class => 3,
            KongCommand::class => 2,
            PungCommand::class => 2,
            ChowCommand::class => 1,
        ];
        $this->value = null;
    }

    /**
     * @param PublicCommand $publicCommand
     * @return int
     */
    protected function getPriority(PublicCommand $publicCommand) {
        $class = get_class($publicCommand);
        if (!isset($this->priorityMap[$class])) {
            throw new \InvalidArgumentException();
        }
        return $this->priorityMap[$class];
    }

    /**
     * @param PublicCommand $publicCommand1
     * @param PublicCommand $publicCommand2
     * @return bool
     */
    protected function comparePriority(PublicCommand $publicCommand1, PublicCommand $publicCommand2) {
        return $this->getPriority($publicCommand1) <=> $this->getPriority($publicCommand2);
    }

    /**
     * @return bool
     */
    function has() {
        return $this->value !== null;
    }

    /**
     * @return PublicCommand
     */
    function get() {
        if (!$this->has()) {
            throw new \BadMethodCallException();
        }
        return $this->value;
    }

    /**
     * @param PublicCommand $publicCommand
     */
    function set(PublicCommand $publicCommand) {
        if (!$this->setAble($publicCommand)) {
            throw new \InvalidArgumentException();
        }
        $this->value = $publicCommand;
    }

    /**
     * @param PublicCommand $publicCommand
     * @return bool
     */
    function setAble(PublicCommand $publicCommand) {
        return !$this->has()
        || $this->comparePriority($publicCommand, $this->get()) > 0;
    }

    function clear() {
        $this->value = null;
    }
}