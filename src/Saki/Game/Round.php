<?php
namespace Saki\Game;

use Saki\Command\CommandContext;
use Saki\Command\CommandParser;
use Saki\Command\CommandProcessor;
use Saki\Command\CommandSet;
use Saki\Phase\NullPhaseState;
use Saki\Phase\OverPhaseState;
use Saki\Phase\PhaseState;
use Saki\Phase\PrivatePhaseState;
use Saki\Phase\PublicPhaseState;

/**
 * @package Saki\Game
 */
class Round {
    // immutable
    private $processor;
    // round variable
    private $areas;
    /** @var PhaseState */
    private $phaseState;

    function __construct() {
        // immutable
        $gameData = new GameData();
        $this->processor = new CommandProcessor(
            new CommandParser(new CommandContext($this), CommandSet::createStandard())
        );

        // round variable
        $playerList = new PlayerList($gameData->getPlayerType(), $gameData->getScoreStrategy()->getPointSetting()->getInitialPoint());
        $this->areas = new Areas($gameData, $playerList);
        $this->phaseState = new NullPhaseState();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    /**
     * @param bool $keepDealer
     * @param bool $isWin
     */
    function roll(bool $keepDealer, bool $isWin = false) {
        // round variable
        $this->areas->roll($keepDealer, $isWin);
        $this->phaseState = new NullPhaseState();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    /**
     * @param PrevailingStatus $prevailingStatus
     */
    function debugInit(PrevailingStatus $prevailingStatus) {
        $this->areas->debugInit($prevailingStatus);
        $this->phaseState = new NullPhaseState();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    /**
     * @return CommandProcessor
     */
    function getProcessor() {
        return $this->processor;
    }

    /**
     * @param array ...$scripts
     */
    function process(... $scripts) {
        $this->getProcessor()->process(... $scripts);
    }

    /**
     * @return Areas
     */
    function getAreas() {
        return $this->areas;
    }

    /**
     * @param PhaseState|null $customPhaseState
     */
    function toNextPhase(PhaseState $customPhaseState = null) {
        if ($customPhaseState !== null) {
            $this->phaseState->setCustomNextState($customPhaseState);
        }

        $this->phaseState->leave($this);
        $this->phaseState = $this->phaseState->getNextState($this);
        $this->phaseState->enter($this);
        
        $this->areas->setPhaseState($this->phaseState); // todo better wrapping
    }

    function toNextRound() {
        $overPhaseState = $this->phaseState;
        if (!$overPhaseState->getPhase()->isOver()) {
            throw new \InvalidArgumentException('Not over phase.');
        }

        if ($overPhaseState->isGameOver($this)) {
            throw new \InvalidArgumentException('Game is over.');
        }

        $keepDealer = $overPhaseState->getResult()->isKeepDealer();
        $isWin = $overPhaseState->getResult()->getResultType()->isWin();
        $this->roll($keepDealer, $isWin);
    }
}