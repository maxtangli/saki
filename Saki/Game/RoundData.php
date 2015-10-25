<?php
namespace Saki\Game;

use Saki\FinalScore\CompositeFinalScoreStrategy;
use Saki\FinalScore\MoundFinalScoreStrategy;
use Saki\FinalScore\RankingHorseFinalScoreStrategy;
use Saki\FinalScore\RankingHorseType;
use Saki\Tile\TileSet;

/**
 * Holds immutable data during a game.
 * @package Saki\Game
 */
class GameData {
    private $playerCount;
    private $totalRoundType;
    private $initialScore;
    private $finalScoreStrategy;
    private $tileSet;

    /**
     * default: 4 player, east game, 25000-30000 initial score,
     */
    function __construct() {
        $this->playerCount = 4;
        $this->totalRoundType = TotalRoundType::getInstance(TotalRoundType::EAST);
        $this->initialScore = 25000;
        $this->finalScoreStrategy = new CompositeFinalScoreStrategy([
            RankingHorseFinalScoreStrategy::fromType(RankingHorseType::getInstance(RankingHorseType::UMA_10_20)),
            new MoundFinalScoreStrategy(25000, 30000),
        ]);
        $this->tileSet = TileSet::getStandardTileSet();
    }

    function getPlayerCount() {
        return $this->playerCount;
    }

    function getTotalRoundType() {
        return $this->totalRoundType;
    }

    function getInitialScore() {
        return $this->initialScore;
    }

    function getFinalScoreStrategy() {
        return $this->finalScoreStrategy;
    }

    function getTileSet() {
        return $this->tileSet;
    }
}

class RoundData {
    // immutable during game
    private $gameData;

    // immutable during round
    private $roundWindData;

    // variable during round
    private $playerList;
    private $turnManager;
    private $tileAreas;

    function __construct() {
        $gameData = new GameData();
        $this->gameData = $gameData;

        $this->roundWindData = new RoundWindData($gameData->getPlayerCount(), $gameData->getTotalRoundType());

        $this->playerList = new PlayerList($gameData->getPlayerCount(), $gameData->getInitialScore());
        $this->turnManager = new TurnManager($this->playerList);
        $wall = new Wall($gameData->getTileSet());
        $this->tileAreas = new TileAreas($wall, $this->playerList, function () {
            return $this->turnManager->getGlobalTurn();
        });
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

    function getTurnManager() {
        return $this->turnManager;
    }
}