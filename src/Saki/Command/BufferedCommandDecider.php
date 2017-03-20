<?php

namespace Saki\Command;

use Saki\Command\DebugCommand\DoubleRonCommand;
use Saki\Command\DebugCommand\PassAllCommand;
use Saki\Command\PublicCommand\ChowCommand;
use Saki\Command\PublicCommand\KongCommand;
use Saki\Command\PublicCommand\PassCommand;
use Saki\Command\PublicCommand\PublicCommand;
use Saki\Command\PublicCommand\PungCommand;
use Saki\Command\PublicCommand\RonCommand;
use Saki\Game\PlayerType;

/**
 * @package Saki\Command
 */
class BufferedCommandDecider implements CommandDecider {
    // variable
    private $buffer;
    private $candidate;

    /**
     * @param PlayerType $playerType
     * @param CommandParser $commandParser
     */
    function __construct(PlayerType $playerType, CommandParser $commandParser) {
        $this->buffer = new CommandDeciderBuffer($playerType);
        $this->candidate = new CommandDeciderCandidate($commandParser);
    }

    function clear() {
        $this->buffer->clear();
        $this->candidate->clear();
    }

    /**
     * @return bool
     */
    function decided() {
        return $this->buffer->full();
    }

    /**
     * @return Command
     */
    function getDecided() {
        if (!$this->decided()) {
            throw new \InvalidArgumentException();
        }
        return $this->candidate->getCandidateCommand();
    }

    /**
     * @param Command $command
     * @return bool
     */
    function isDecidedCommand(Command $command) {
        return $this->decided()
            && $command == $this->getDecided();
    }

    /**
     * @param Command $command
     * @return bool
     */
    function allowSubmit(Command $command) {
        return $this->buffer->isEmpty($command)
            && ($command instanceof PassCommand || $this->candidate->betterCandidate($command));
    }

    /**
     * @param Command $command
     */
    function submit(Command $command) {
        if (!$this->allowSubmit($command)) {
            throw new \InvalidArgumentException();
        }

        $this->buffer->set($command);

        if ($this->candidate->betterCandidate($command)) {
            $this->candidate->updateCandidate($command);
        }
    }
}

/**
 * inner class.
 * @package Saki\Command
 */
class CommandDeciderCandidate {
    private $commandParser;
    private $priorityMap;
    private $candidate;

    function __construct(CommandParser $commandParser) {
        $this->commandParser = $commandParser;
        $this->priorityMap = [
            DoubleRonCommand::class => 5,
            RonCommand::class => 5,
            KongCommand::class => 4,
            PungCommand::class => 3,
            ChowCommand::class => 2,
            PassAllCommand::class => 1,
            PassCommand::class => 0,
        ];
        $this->clear();
    }

    function clear() {
        $this->candidate = $this->commandParser->parseLine('passAll');
    }

    /**
     * @return Command
     */
    function getCandidateCommand() {
        return $this->candidate;
    }

    /**
     * @param Command $new
     * @return bool
     */
    function betterCandidate(Command $new) {
        return $new instanceof RonCommand
            || $this->comparePriority($new, $this->candidate) == 1;
    }

    /**
     * @param Command $new
     */
    function updateCandidate(Command $new) {
        if (!$this->betterCandidate($new)) {
            throw new \InvalidArgumentException();
        }

        $candidate = $this->candidate;
        if ($new instanceof RonCommand && $candidate instanceof RonCommand) {
            $this->candidate = $this->createDoubleRonCommand($new, $candidate);
        } elseif ($new instanceof RonCommand && $candidate instanceof DoubleRonCommand) {
            $this->candidate = $this->createTripleRonCommand($new, $candidate);
        } elseif ($this->comparePriority($new, $candidate) == 1) {
            $this->candidate = $new;
        } else {
            throw new \LogicException();
        }
    }

    /**
     * @param RonCommand $ron1
     * @param RonCommand $ron2
     * @return Command
     */
    private function createDoubleRonCommand(RonCommand $ron1, RonCommand $ron2) {
        return $this->commandParser->parseLine(
            sprintf('doubleRon %s %s', $ron1->getActor(), $ron2->getActor())
        );
    }

    /**
     * @param RonCommand $ron
     * @param DoubleRonCommand $doubleRon
     * @return Command
     */
    private function createTripleRonCommand(RonCommand $ron, DoubleRonCommand $doubleRon) {
        $doubleRonActorList = $doubleRon->getActorList();
        return $this->commandParser->parseLine(
            sprintf('tripleRon %s %s %s'
                , $ron->getActor(), $doubleRonActorList[0], $doubleRonActorList[1])
        );
    }

    /**
     * @param Command $command
     * @return int
     */
    private function getPriority(Command $command) {
        $class = get_class($command);
        if (!isset($this->priorityMap[$class])) {
            throw new \InvalidArgumentException();
        }
        return $this->priorityMap[$class];
    }

    /**
     * @param Command $command1
     * @param Command $command2
     * @return bool
     */
    private function comparePriority(Command $command1, Command $command2) {
        return $this->getPriority($command1) <=> $this->getPriority($command2);
    }
}

/**
 * inner class.
 * @package Saki\Command
 */
class CommandDeciderBuffer {
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
     * @param Command $command
     * @return bool
     */
    function isEmpty(Command $command) {
        $key = $this->toKey($command);
        return !isset($this->buffer[$key]);
    }

    /**
     * @param Command $command
     */
    function set(Command $command) {
        if (!$this->isEmpty($command)) {
            throw new \InvalidArgumentException();
        }
        $key = $this->toKey($command);
        $this->buffer[$key] = $command;
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
     * @param Command $command
     * @return string
     */
    private function toKey(Command $command) {
        /** @var PublicCommand $publicCommand */
        $publicCommand = $command;
        return $publicCommand->getActor()->__toString();
    }
}