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
use Saki\Tile\Tile;
use Saki\Win\WinTarget;

/**
 * @package Saki\Game
 */
class Round {
    // immutable
    private $gameData;
    private $processor;
    private $playerList;
    // game variable
    private $prevailingCurrent;
    // round variable
    private $areas;
    /** @var PhaseState */
    private $phaseState;

    function __construct() {
        // immutable
        $gameData = new GameData();
        $this->gameData = $gameData;
        $this->processor = new CommandProcessor(
            new CommandParser(new CommandContext($this), CommandSet::createStandard())
        );
        $this->playerList = new PlayerList($gameData->getPlayerType(), $gameData->getScoreStrategy()->getPointSetting()->getInitialPoint());

        // game variable
        $this->prevailingCurrent = PrevailingCurrent::createFirst($gameData->getPrevailingContext());

        // round variable
        $this->areas = new Areas($gameData->getTileSet(), $gameData->getScoreStrategy()->getPointSetting(), $this->playerList);
        $this->phaseState = new NullPhaseState();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function roll(bool $keepDealer, bool $isWin = false) {
        // game variable
        $this->prevailingCurrent = $this->prevailingCurrent->toRolled($keepDealer);

        // round variable
        $this->areas->roll($keepDealer, $isWin);
        $this->phaseState = new NullPhaseState();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function debugInit(PrevailingStatus $PrevailingStatus) {
        // game variable
        $this->prevailingCurrent = $this->prevailingCurrent->toDebugInited($PrevailingStatus);

        // round variable
        $nextDealerSeatWind = $this->getAreas()->getInitialSeatWindArea(
            $PrevailingStatus->getInitialSeatWindOfDealer()
        )->getPlayer()->getInitialSeatWind();
        $this->areas->debugInit($nextDealerSeatWind);
        $this->phaseState = new NullPhaseState();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function getGameData() {
        return $this->gameData;
    }

    function getWinReport(SeatWind $actor) {
        // WinTarget will assert valid player
        return $this->getGameData()->getWinAnalyzer()->analyze(new WinTarget($actor, $this));
    }

    function getProcessor() {
        return $this->processor;
    }

    function getPrevailingCurrent() {
        return $this->prevailingCurrent;
    }

    function getPlayerList() {
        return $this->playerList;
    }

    /**
     * @return Areas
     */
    function getAreas() {
        return $this->areas;
    }

    /**
     * @return PhaseState|PrivatePhaseState|PublicPhaseState|OverPhaseState
     */
    function getPhaseState() {
        return $this->phaseState;
    }

    function toNextPhase(PhaseState $customPhaseState = null) {
        if ($customPhaseState !== null) {
            $this->phaseState->setCustomNextState($customPhaseState);
        }

        $this->phaseState->leave($this);
        $this->phaseState = $this->phaseState->getNextState($this);
        $this->phaseState->enter($this);
    }

    function toNextRound() {
        $overPhaseState = $this->getPhaseState();
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