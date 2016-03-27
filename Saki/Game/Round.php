<?php
namespace Saki\Game;

use Saki\Command\CommandContext;
use Saki\Command\CommandParser;
use Saki\Command\CommandProcessor;
use Saki\Command\Debug\MockNextReplaceCommand;
use Saki\Command\Debug\MockDeadWallCommand;
use Saki\Command\Debug\MockHandCommand;
use Saki\Command\Debug\MockNextDrawCommand;
use Saki\Command\Debug\PassAllCommand;
use Saki\Command\Debug\SkipCommand;
use Saki\Command\PrivateCommand\ConcealedKongCommand;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PrivateCommand\NineNineDrawCommand;
use Saki\Command\PrivateCommand\PlusKongCommand;
use Saki\Command\PrivateCommand\ReachCommand;
use Saki\Command\PrivateCommand\WinBySelfCommand;
use Saki\Command\PublicCommand\BigKongCommand;
use Saki\Command\PublicCommand\ChowCommand;
use Saki\Command\PublicCommand\PongCommand;
use Saki\Command\PublicCommand\SmallKongCommand;
use Saki\Command\PublicCommand\WinByOtherCommand;
use Saki\RoundPhase\NullPhaseState;
use Saki\RoundPhase\PrivatePhaseState;
use Saki\RoundPhase\PublicPhaseState;
use Saki\RoundPhase\RoundPhaseState;
use Saki\Tile\Tile;
use Saki\Win\WinAnalyzer;
use Saki\Win\WinTarget;

class Round {
    // immutable during game
    private $gameData;
    private $winAnalyzer;

    // immutable during round
    private $roundWindData;

    // variable during round
    private $playerList;
    private $turnManager;
    private $tileAreas;
    /** @var RoundPhaseState */
    private $phaseState;

    // special: currently immutable, future variable
    private $processor;

    function __construct() {
        $gameData = new GameData();
        $this->gameData = $gameData;
        $this->winAnalyzer = new WinAnalyzer($gameData->getYakuSet());

        $this->roundWindData = new RoundWindData($gameData->getPlayerCount(), $gameData->getTotalRoundType());

        $this->playerList = new PlayerList($gameData->getPlayerCount(), $gameData->getInitialScore());
        $this->turnManager = new TurnManager($this->playerList);
        $wall = new Wall($gameData->getTileSet());
        $this->tileAreas = new TileAreas($wall, $this->playerList, function () {
            return $this->turnManager->getRoundTurn();
        });

        $this->phaseState = new NullPhaseState();

        $classes = [
            // private
            DiscardCommand::class,
            ConcealedKongCommand::class,
            PlusKongCommand::class,
            ReachCommand::class,
            WinBySelfCommand::class,
            NineNineDrawCommand::class,
            // public
            ChowCommand::class,
            PongCommand::class,
            BigKongCommand::class,
            WinByOtherCommand::class,
            // debug
            MockNextReplaceCommand::class,
            MockDeadWallCommand::class,
            MockHandCommand::class,
            MockNextDrawCommand::class,
            PassAllCommand::class,
            SkipCommand::class,
        ];
        $this->processor = new CommandProcessor(new CommandParser(new CommandContext($this), $classes));
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function reset($keepDealer) {
        if (!is_bool($keepDealer)) {
            throw new \InvalidArgumentException('bool expected.');
        }

        $currentDealer = $this->getPlayerList()->getDealerPlayer();
        $nextDealer = $keepDealer ? $currentDealer : $this->getTurnManager()->getOffsetPlayer(1, $currentDealer);

        $this->getRoundWindData()->reset($keepDealer);

        $this->getPlayerList()->reset($nextDealer);

        $this->getTurnManager()->reset();
        $this->getTileAreas()->reset();

        $this->phaseState = new NullPhaseState();
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    function debugReset(RoundResetData $resetData) {
        $this->getRoundWindData()->debugReset($resetData->getRoundWind(), $resetData->getRoundWindTurn(), $resetData->getSelfWindTurn());

        $dealer = $this->getPlayerList()->getSelfWindPlayer($resetData->getDealerWind());
        $this->getPlayerList()->reset($dealer);

        $this->getTurnManager()->reset();
        $this->getTileAreas()->reset();

        $this->phaseState = new NullPhaseState();
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

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

    function getTileAreas() {
        return $this->tileAreas;
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

