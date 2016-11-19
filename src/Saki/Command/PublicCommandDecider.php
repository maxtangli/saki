<?php

namespace Saki\Command;

use Saki\Command\Debug\PassAllCommand;
use Saki\Command\PublicCommand\ChowCommand;
use Saki\Command\PublicCommand\KongCommand;
use Saki\Command\PublicCommand\PungCommand;
use Saki\Command\PublicCommand\RonCommand;
use Saki\Game\PlayerType;

/**
 * @package Saki\Command
 */
class PublicCommandDecider {
    private $priorityManager;
    private $buffer;
    private $candidate;

    /**
     * @param PlayerType $playerType
     */
    function __construct(PlayerType $playerType) {
        $this->priorityManager = new PublicCommandPriorityManager();
        $this->buffer = new PublicCommandBuffer($playerType);
        $this->candidate = null;
    }

    function clear() {
        $this->buffer->clear();
        $this->candidate = null;
    }

    /**
     * @return bool
     */
    function decided() {
        return $this->buffer->full();
    }

    /**
     * @return null
     */
    function getDecided() {
        if (!$this->decided()) {
            throw new \InvalidArgumentException();
        }
        return $this->candidate;
    }

    /**
     * @param PublicCommand $publicCommand
     * @return bool
     */
    function allowSubmit(PublicCommand $publicCommand) {
        if (!$this->buffer->setAble($publicCommand)) {
            return false;
        }

        if ($publicCommand instanceof PassAllCommand) {
            return true;
        }

        return $this->betterCandidate($publicCommand);
    }

    /**
     * @param PublicCommand $publicCommand
     */
    function submit(PublicCommand $publicCommand) {
        if (!$this->allowSubmit($publicCommand)) {
            throw new \InvalidArgumentException();
        }

        $this->buffer->set($publicCommand);

        if ($this->betterCandidate($publicCommand)) {
            $this->candidate = $publicCommand;
        }
    }

    /**
     * @param PublicCommand $publicCommand
     * @return bool
     */
    private function betterCandidate(PublicCommand $publicCommand) {
        return is_null($this->candidate)
            ? true
            : $this->priorityManager->comparePriority($publicCommand, $this->candidate) == 1;
    }
}

class PublicCommandPriorityManager {
    private $priorityMap;

    function __construct() {
        $this->priorityMap = [
            RonCommand::class => 4,
            KongCommand::class => 3,
            PungCommand::class => 3,
            ChowCommand::class => 2,
            PassAllCommand::class => 1,
        ];
    }

    /**
     * @param PublicCommand $publicCommand
     * @return int
     */
    function getPriority(PublicCommand $publicCommand) {
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
    function comparePriority(PublicCommand $publicCommand1, PublicCommand $publicCommand2) {
        return $this->getPriority($publicCommand1) <=> $this->getPriority($publicCommand2);
    }
}

class PublicCommandBuffer {
    private $playerType;
    private $buffer;

    /**
     * @param PlayerType $playerType
     */
    function __construct(PlayerType $playerType) {
        $this->playerType = $playerType;
        $this->clear();
    }

    /**
     * @param PublicCommand $publicCommand
     * @return bool
     */
    function setAble(PublicCommand $publicCommand) {
        $key = $this->toKey($publicCommand);
        return !isset($this->buffer[$key]);
    }

    /**
     * @param PublicCommand $publicCommand
     */
    function set(PublicCommand $publicCommand) {
        if (!$this->setAble($publicCommand)) {
            throw new \InvalidArgumentException();
        }
        $key = $this->toKey($publicCommand);
        $this->buffer[$key] = $publicCommand;
    }

    /**
     * @return bool
     */
    function full() {
        return count($this->buffer) == $this->playerType->getValue();
    }

    function clear() {
        $this->buffer = [];
    }

    /**
     * @param PublicCommand $publicCommand
     * @return string
     */
    private function toKey(PublicCommand $publicCommand) {
        return $publicCommand->getActor()->__toString();
    }
}