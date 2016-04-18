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
        $this->playerList = new PlayerList($gameData->getPlayerType(), $gameData->getInitialPoint());
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

    // todo simplify
    function debugSkipTo(SeatWind $goalCurrentSeatWind, Phase $phase = null, $circleCount = null,
                         Tile $mockDiscardTile = null) {
        if ($this->getAreas()->getTurn()->getCircleCount() != 1) {
            throw new \LogicException('Not implemented.');
        }

        $validCurrentState = $this->getPhaseState()->getPhase()->isPrivateOrPublic();
        if (!$validCurrentState) {
            throw new \InvalidArgumentException();
        }

        $actualPhase = $phase ?? Phase::createPrivate();
        $validPhase = $actualPhase->isPrivateOrPublic();
        if (!$validPhase) {
            throw new \InvalidArgumentException();
        }

        $actualCircleCount = $circleCount ?? 1;
        $validActualCircleCount = ($actualCircleCount == 1);
        if (!$validActualCircleCount) {
            throw new \InvalidArgumentException('Not implemented.');
        }

        $actualMockDiscardTile = $mockDiscardTile ?? Tile::fromString('C');
        $validMockDiscardTile = !$actualMockDiscardTile->isWind();
        if (!$validMockDiscardTile) {
            throw new \InvalidArgumentException('Not implemented: consider FourWindDiscardedDraw issue.');
        }

        $isTargetTurn = function () use ($goalCurrentSeatWind, $actualPhase) {
            $currentPhaseState = $this->getPhaseState();
            $currentPhase = $currentPhaseState->getPhase();
            $currentSeatWind = $this->getAreas()->getCurrentSeatWind();
            $isTargetTurn = ($currentSeatWind == $goalCurrentSeatWind) && ($currentPhase == $actualPhase);
            $isGameOver = $currentPhase->isOver() && $currentPhaseState->isGameOver($this);
            return $isGameOver || $isTargetTurn;
        };

        $pro = $this->getProcessor();
        $tileString = $actualMockDiscardTile->__toString();
        $discardScript = sprintf('discard I I:s-%s:%s', $tileString, $tileString);
        while (!$isTargetTurn()) {
            $currentPhase = $this->getPhaseState()->getPhase();
            if ($currentPhase->isPrivate()) {
                $pro->process($discardScript);
            } elseif ($currentPhase->isPublic()) {
                $pro->process('passAll');
            } else {
                throw new \LogicException();
            }
        }
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

