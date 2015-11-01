<?php
namespace Saki\Game;
use Saki\FinalScore\RankingHorseFinalScoreStrategy;
use Saki\FinalScore\MoundFinalScoreStrategy;
use Saki\FinalScore\RankingHorseType;
use Saki\Tile\TileSet;
use Saki\FinalScore\CompositeFinalScoreStrategy;
use Saki\Win\Yaku\YakuSet;

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
    private $yakuSet;

    /**
     * default: 4 player, east game, 25000-30000 initial score,
     */
    function __construct() {
        $this->playerCount = 4;
        $this->totalRoundType = GameLengthType::getInstance(GameLengthType::EAST);
        $this->initialScore = 25000;
        $this->finalScoreStrategy = new CompositeFinalScoreStrategy([
            RankingHorseFinalScoreStrategy::fromType(RankingHorseType::getInstance(RankingHorseType::UMA_10_20)),
            new MoundFinalScoreStrategy(25000, 30000),
        ]);
        $this->tileSet = TileSet::getStandardTileSet();
        $this->yakuSet = YakuSet::getStandardYakuSet();
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

    function getYakuSet() {
        return $this->yakuSet;
    }
}