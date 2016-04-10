<?php
namespace Saki\Game;

use Saki\Command\CommandContext;
use Saki\Command\CommandParser;
use Saki\Command\CommandProcessor;
use Saki\Command\CommandSet;
use Saki\RoundPhase\NullPhaseState;
use Saki\RoundPhase\PrivatePhaseState;
use Saki\RoundPhase\PublicPhaseState;
use Saki\RoundPhase\RoundPhaseState;
use Saki\Tile\Tile;
use Saki\Win\WinAnalyzer;
use Saki\Win\WinTarget;

class Round {
    // immutable
    private $gameData;
    private $winAnalyzer;
    private $processor;
    // game variable
    private $roundWindData;
    // round variable
    private $playerList;
    private $turnManager;
    private $areas;
    /** @var RoundPhaseState */
    private $phaseState;

    function __construct() {
        // immutable
        $gameData = new GameData();
        $this->gameData = $gameData;
        $this->winAnalyzer = new WinAnalyzer($gameData->getYakuSet());
        $this->processor = new CommandProcessor(new CommandParser(new CommandContext($this), CommandSet::createStandard()));

        // game variable
        $this->roundWindData = new RoundWindData($gameData->getPlayerCount(), $gameData->getTotalRoundType());

        // round variable
        $this->playerList = new PlayerList($gameData->getPlayerCount(), $gameData->getInitialScore());
        $this->turnManager = new TurnManager($this->playerList);
        $wall = new Wall($gameData->getTileSet());

        $roundTurnProvider = function () {
            return $this->turnManager->getRoundTurn();
        };
        $this->areas = new Areas($wall, $this->playerList, $roundTurnProvider);

        $this->phaseState = new NullPhaseState();

        // initial
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function reset($keepDealer) {
        if (!is_bool($keepDealer)) {
            throw new \InvalidArgumentException('bool expected.');
        }

        $currentDealer = $this->getPlayerList()->getDealerPlayer();
        $nextDealer = $keepDealer ? $currentDealer : $this->getTurnManager()->getOffsetPlayer(1, $currentDealer);

        $this->roundWindData->reset($keepDealer);

        $this->turnManager->reset();
        $nextDealerPlayerWind = new PlayerWind($nextDealer->getTileArea()->getPlayerWind()->getWindTile());
        $this->areas->reset($nextDealerPlayerWind);

        $this->phaseState = new NullPhaseState();
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function debugReset(GameTurn $resetData) {
        $this->roundWindData->debugReset($resetData->getRoundWind(), $resetData->getDealerWind()->getIndex(), $resetData->getSelfWindTurn());

        $nextDealer = $this->getPlayerList()->getSelfWindPlayer($resetData->getDealerWind()->getWindTile());

        $this->turnManager->reset();
        $nextDealerPlayerWind = new PlayerWind($nextDealer->getTileArea()->getPlayerWind()->getWindTile());
        $this->areas->reset($nextDealerPlayerWind);

        $this->phaseState = new NullPhaseState();
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    // todo simplify
    function debugSkipTo(Player $actualCurrentPlayer, RoundPhase $roundPhase = null, $globalTurn = null,
                         Tile $mockDiscardTile = null) {
        if ($this->getTurnManager()->getGlobalTurn() != 1) {
            throw new \LogicException('Not implemented.');
        }

        $validCurrentState = $this->getPhaseState()->getRoundPhase()->isPrivateOrPublic();
        if (!$validCurrentState) {
            throw new \InvalidArgumentException();
        }

        $actualRoundPhase = $roundPhase ?? RoundPhase::getPrivateInstance();
        $validRoundPhase = $actualRoundPhase->isPrivateOrPublic();
        if (!$validRoundPhase) {
            throw new \InvalidArgumentException();
        }

        $actualGlobalTurn = $globalTurn ?? 1;
        $validActualGlobalTurn = ($actualGlobalTurn == 1);
        if (!$validActualGlobalTurn) {
            throw new \InvalidArgumentException('Not implemented.');
        }

        $actualMockDiscardTile = $mockDiscardTile ?? Tile::fromString('C');
        $validMockDiscardTile = !$actualMockDiscardTile->isWind();
        if (!$validMockDiscardTile) {
            throw new \InvalidArgumentException('Not implemented: consider FourWindDiscardedDraw issue.');
        }

        $isTargetTurn = function () use ($actualCurrentPlayer, $actualRoundPhase) {
            $currentPhaseState = $this->getPhaseState();
            $currentRoundPhase = $currentPhaseState->getRoundPhase();
            $currentPlayer = $this->getTurnManager()->getCurrentPlayer();

            $isTargetTurn = ($currentPlayer == $actualCurrentPlayer) && ($currentRoundPhase == $actualRoundPhase);
            $isGameOver = $currentRoundPhase->isOver() && $currentPhaseState->isGameOver($this);
            return $isGameOver || $isTargetTurn;
        };

        $pro = $this->getProcessor();
        $tileString = $actualMockDiscardTile->__toString();
        $discardScript = sprintf('discard I I:s-%s:%s', $tileString, $tileString);
        while (!$isTargetTurn()) {
            $currentRoundPhase = $this->getPhaseState()->getRoundPhase();
            if ($currentRoundPhase->isPrivate()) {
                $pro->process($discardScript);
            } elseif ($currentRoundPhase->isPublic()) {
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

    function getRoundWindData() {
        return $this->roundWindData;
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

    function getTurnManager() {
        return $this->turnManager;
    }

    /**
     * @return RoundPhaseState|PrivatePhaseState|PublicPhaseState|OverPhaseState
     */
    function getPhaseState() {
        return $this->phaseState;
    }

    function toNextPhase(RoundPhaseState $customPhaseState = null) {
        if ($customPhaseState !== null) {
            $this->phaseState->setCustomNextState($customPhaseState);
        }

        $this->phaseState->leave($this);
        $this->phaseState = $this->phaseState->getNextState($this);
        $this->phaseState->enter($this);
    }

    function toNextRound() {
        if (!$this->getPhaseState()->getRoundPhase()->isOver()) {
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

