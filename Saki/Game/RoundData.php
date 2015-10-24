<?php
namespace Saki\Game;

use Saki\FinalScore\CompositeFinalScoreStrategy;
use Saki\FinalScore\MoundFinalScoreStrategy;
use Saki\FinalScore\RankingHorseFinalScoreStrategy;
use Saki\FinalScore\RankingHorseType;
use Saki\RoundResult\RoundResult;
use Saki\Tile\TileSet;

class GameData {
    private $playerCount;
    private $totalRoundType;
    private $finalScoreStrategy;

    /**
     * default: 4 player, east game, 25000-30000 initial score,
     */
    function __construct() {
        $this->playerCount = 4;
        $this->totalRoundType = TotalRoundType::getInstance(TotalRoundType::EAST);
        $this->finalScoreStrategy = new CompositeFinalScoreStrategy([
            RankingHorseFinalScoreStrategy::fromType(RankingHorseType::getInstance(RankingHorseType::UMA_10_20)),
            new MoundFinalScoreStrategy(25000, 30000),
        ]);
    }

    function getPlayerCount() {
        return $this->playerCount;
    }

    function getTotalRoundType() {
        return $this->totalRoundType;
    }

    function getFinalScoreStrategy() {
        return $this->finalScoreStrategy;
    }
}

class RoundData {
    // immutable during game
    private $gameData;

    // immutable during game
    private $roundWindData;

    // variable
    private $playerList;

    private $tileAreas;

    private $roundPhase;

    private $roundResult;

    function __construct() {
        $gameData = new GameData();
        $this->gameData = $gameData;

        $this->roundWindData = new RoundWindData($gameData->getPlayerCount(), $gameData->getTotalRoundType());

        $this->playerList = PlayerList::createStandard();
        $wall = new Wall(TileSet::getStandardTileSet()); // 14ms
        $this->tileAreas = new TileAreas($wall, $this->playerList);

        $this->roundPhase = RoundPhase::getInitPhaseInstance();
        $this->roundResult = null;
    }

    function reset($keepDealer) {
        if (!is_bool($keepDealer)) {
            throw new \InvalidArgumentException('bool expected.');
        }

        $this->getRoundWindData()->reset($keepDealer);

        $nextDealer = $keepDealer ? $this->getPlayerList()->getDealerPlayer() : $this->getPlayerList()->getDealerOffsetPlayer(1);
        $this->getPlayerList()->reset($nextDealer);
        $this->getTileAreas()->reset();

        $this->setRoundPhase(RoundPhase::getInitPhaseInstance());
        $this->setRoundResult(null);
    }

    function getGameData() {
        return $this->gameData;
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

    function getRoundPhase() {
        return $this->roundPhase;
    }

    function setRoundPhase(RoundPhase $roundPhase) {
        $this->roundPhase = $roundPhase;
    }

    function getRoundResult() {
        if (!$this->roundResult) {
            throw new \LogicException();
        }
        return $this->roundResult;
    }

    function setRoundResult(RoundResult $roundResult = null) {
        $this->roundResult = $roundResult;
    }
}