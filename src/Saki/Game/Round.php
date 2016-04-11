<?php
namespace Saki\Game;

use Saki\Command\CommandContext;
use Saki\Command\CommandParser;
use Saki\Command\CommandProcessor;
use Saki\Command\CommandSet;
use Saki\Phase\NullPhaseState;
use Saki\Phase\PhaseState;
use Saki\Phase\PrivatePhaseState;
use Saki\Phase\PublicPhaseState;
use Saki\Tile\Tile;
use Saki\Win\WinAnalyzer;
use Saki\Win\WinTarget;

class Round {
    // immutable
    private $gameData;
    private $winAnalyzer;
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
        $this->winAnalyzer = new WinAnalyzer($gameData->getYakuSet());
        $this->processor = new CommandProcessor(
            new CommandParser(new CommandContext($this), CommandSet::createStandard())
        );

        // game variable
        $this->prevailingCurrent = new PrevailingCurrent($gameData->getPrevailingContext());

        // round variable
        $this->playerList = new PlayerList($gameData->getPlayerCount(), $gameData->getInitialPoint());
        $wall = new Wall($gameData->getTileSet());

        $this->areas = new Areas($wall, $this->playerList);

        $this->phaseState = new NullPhaseState();

        // initial
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function reset(bool $keepDealer) {
        $currentDealer = $this->getPlayerList()->getEastPlayer();
        $nextDealer = $keepDealer ? $currentDealer : $this->getAreas()->tempGetOffsetPlayer(1);

        $this->prevailingCurrent = $this->prevailingCurrent->toReset($keepDealer);

        $nextDealerSeatWind = new SeatWind($nextDealer->getArea()->getSeatWind()->getWindTile());
        $this->areas->reset($nextDealerSeatWind);

        $this->phaseState = new NullPhaseState();
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function debugReset(PrevailingStatus $PrevailingStatus) {
        $this->prevailingCurrent = $this->prevailingCurrent->toDebugReset($PrevailingStatus);

        $nextDealer = $this->getPlayerList()->getPlayerByInitial(
            $PrevailingStatus->getInitialSeatWindOfDealer()
        );

        $nextDealerSeatWind = new SeatWind($nextDealer->getArea()->getSeatWind()->getWindTile());
        $this->areas->reset($nextDealerSeatWind);

        $this->phaseState = new NullPhaseState();
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    // todo simplify
    function debugSkipTo(Player $actualCurrentPlayer, Phase $phase = null, $circleCount = null,
                         Tile $mockDiscardTile = null) {
        if ($this->getAreas()->getCurrentTurn()->getCircleCount() != 1) {
            throw new \LogicException('Not implemented.');
        }

        $validCurrentState = $this->getPhaseState()->getPhase()->isPrivateOrPublic();
        if (!$validCurrentState) {
            throw new \InvalidArgumentException();
        }

        $actualPhase = $phase ?? Phase::getPrivateInstance();
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

        $isTargetTurn = function () use ($actualCurrentPlayer, $actualPhase) {
            $currentPhaseState = $this->getPhaseState();
            $currentPhase = $currentPhaseState->getPhase();
            $currentPlayer = $this->getAreas()->tempGetCurrentPlayer();

            $isTargetTurn = ($currentPlayer == $actualCurrentPlayer) && ($currentPhase == $actualPhase);
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

    function getWinAnalyzer() {
        return $this->winAnalyzer;
    }

    function getWinResult(Player $player) {
        // WinTarget will assert valid player
        return $this->getWinAnalyzer()->analyzeTarget(new WinTarget($player, $this));
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

        $keepDealer = $this->getPhaseState()->getRoundResult()->isKeepDealer();
        $this->reset($keepDealer);
    }

    function getProcessor() {
        return $this->processor;
    }
}

