<?php
namespace Saki\Game;

use Saki\Command\CommandContext;
use Saki\Command\CommandParser;
use Saki\Command\CommandProcessor;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PrivateCommand\ExposedKongCommand;
use Saki\Command\PrivateCommand\NineNineDrawCommand;
use Saki\Command\PrivateCommand\PlusKongCommand;
use Saki\Command\PrivateCommand\ReachCommand;
use Saki\Command\PrivateCommand\WinBySelfCommand;
use Saki\Command\PublicCommand\BigKongCommand;
use Saki\Command\PublicCommand\ChowCommand;
use Saki\Command\PublicCommand\Debug\DebugPassCommand;
use Saki\Command\PublicCommand\PongCommand;
use Saki\Command\PublicCommand\SmallKongCommand;
use Saki\Command\PublicCommand\WinByOtherCommand;
use Saki\RoundPhase\NullPhaseState;
use Saki\RoundPhase\RoundPhaseState;
use Saki\Win\WinAnalyzer;
use Saki\Win\WinTarget;

class RoundData {
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
            ExposedKongCommand::class,
            PlusKongCommand::class,
            ReachCommand::class,
            WinBySelfCommand::class,
            NineNineDrawCommand::class,
            // public
            ChowCommand::class,
            PongCommand::class,
            BigKongCommand::class,
            SmallKongCommand::class,
            WinByOtherCommand::class,
            // public/debug
            DebugPassCommand::class,
        ];
        $this->processor = new CommandProcessor(new CommandParser(new CommandContext($this), $classes));
    }

    function reset($keepDealer) {
        if (!is_bool($keepDealer)) {
            throw new \InvalidArgumentException('bool expected.');
        }

        $this->getRoundWindData()->reset($keepDealer);

        $currentDealer = $this->getPlayerList()->getDealerPlayer();
        $nextDealer = $keepDealer ? $currentDealer : $this->getTurnManager()->getOffsetPlayer(1, $currentDealer);
        $this->getPlayerList()->reset($nextDealer);

        $this->getTurnManager()->reset();
        $this->getTileAreas()->reset();

        $this->phaseState = new NullPhaseState();
    }

    function debugReset(RoundDebugResetData $resetData) {
        $this->getRoundWindData()->debugReset($resetData->getRoundWind(), $resetData->getRoundWindTurn(), $resetData->getSelfWindTurn());

        $dealer = $this->getPlayerList()->getSelfWindPlayer($resetData->getDealerWind());
        $this->getPlayerList()->reset($dealer);

        $this->getTurnManager()->reset();
        $this->getTileAreas()->reset();

        $this->phaseState = new NullPhaseState();
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
     * @return \Saki\RoundPhase\RoundPhaseState|\Saki\RoundPhase\OverPhaseState
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

        $this->toNextPhase(); // into init phase
        $this->toNextPhase(); // into private phase
    }

    function getProcessor() {
        return $this->processor;
    }
}

