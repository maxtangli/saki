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

class Round {
    // immutable
    private $gameData;
    private $processor;
    // game variable
    private $prevailingCurrent;
    // round variable
    private $playerList;
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

        // game variable
        $this->prevailingCurrent = new PrevailingCurrent($gameData->getPrevailingContext());

        // round variable
        $this->playerList = new PlayerList($gameData->getPlayerType(), $gameData->getScoreStrategy()->getPointSetting()->getInitialPoint());
        $wall = new Wall($gameData->getTileSet());

        $this->areas = new Areas($wall, $this->playerList);

        $this->phaseState = new NullPhaseState();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function roll(bool $keepDealer) {
        // game variable
        $this->prevailingCurrent = $this->prevailingCurrent->toRolled($keepDealer);

        // round variable
        $this->areas->roll($keepDealer);

        $this->phaseState = new NullPhaseState();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function debugInit(PrevailingStatus $PrevailingStatus) {
        // game variable
        $this->prevailingCurrent = $this->prevailingCurrent->toDebugInited($PrevailingStatus);

        // round variable
        $nextDealerSeatWind = $this->getAreas()->getAreaByInitial(
            $PrevailingStatus->getInitialSeatWindOfDealer()
        )->getPlayer()->getInitialSeatWind();
        $this->areas->debugInit($nextDealerSeatWind); // todo wrong, score/seatWindTurn not inited. should use Areas.debugInit

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
        if (!$this->getPhaseState()->getPhase()->isOver()) {
            throw new \InvalidArgumentException('Not over phase.');
        }

        if ($this->getPhaseState()->isGameOver($this)) {
            throw new \InvalidArgumentException('Game is over.');
        }

        $keepDealer = $this->getPhaseState()->getResult()->isKeepDealer();
        $this->roll($keepDealer);
    }
}