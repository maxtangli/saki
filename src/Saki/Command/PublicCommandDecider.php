<?php

namespace Saki\Command;

use Saki\Command\PublicCommand\ChowCommand;
use Saki\Command\PublicCommand\KongCommand;
use Saki\Command\PublicCommand\PassCommand;
use Saki\Command\PublicCommand\PungCommand;
use Saki\Command\PublicCommand\RonCommand;
use Saki\Game\PlayerType;

/**
 * @package Saki\Command
 */
class PublicCommandDecider {
    // immutable
    private $priorityManager;
    private $commandParser;
    // variable
    private $buffer;
    private $candidate;

    /**
     * @param PlayerType $playerType
     * @param CommandParser $commandParser
     */
    function __construct(PlayerType $playerType, CommandParser $commandParser) {
        $this->priorityManager = new PublicCommandPriorityManager();
        $this->commandParser = $commandParser;
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
     * @return PublicCommand
     */
    function getDecided() {
        if (!$this->decided()) {
            throw new \InvalidArgumentException();
        }
        return $this->candidate ?? $this->createPassAll();
    }

    /**
     * @return PublicCommand
     */
    private function createPassAll() {
        return $this->commandParser->parseLine('passAll');
    }

    /**
     * @param PublicCommand $publicCommand
     * @return bool
     */
    function allowSubmit(PublicCommand $publicCommand) {
        if (!$this->buffer->setAble($publicCommand)) {
            return false;
        }

        if ($publicCommand instanceof PassCommand) {
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

        if ($this->betterCandidate($publicCommand)) {
            $this->buffer->clear();
            $this->candidate = $publicCommand;
        }

        $this->buffer->set($publicCommand);
    }

    /**
     * @param PublicCommand $publicCommand
     * @return bool
     */
    private function betterCandidate(PublicCommand $publicCommand) {
        if ($publicCommand instanceof PassCommand) {
            return false;
        }

        return is_null($this->candidate)
            ? true
            : $this->priorityManager->comparePriority($publicCommand, $this->candidate) == 1;
    }
}

/**
 * inner class of PublicCommandDecider.
 * @package Saki\Command
 */
class PublicCommandPriorityManager {
    private $priorityMap;

    function __construct() {
        $this->priorityMap = [
            RonCommand::class => 4,
            KongCommand::class => 3,
            PungCommand::class => 3,
            ChowCommand::class => 2,
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

/**
 * inner class of PublicCommandDecider.
 * @package Saki\Command
 */
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
        return count($this->buffer) == $this->playerType->getPublicPhaseValue();
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