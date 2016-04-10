<?php
namespace Saki\Game;

use Saki\FinalPoint\CompositeFinalPointStrategy;
use Saki\FinalPoint\MoundFinalPointStrategy;
use Saki\FinalPoint\RankingHorseFinalPointStrategy;
use Saki\FinalPoint\RankingHorseType;
use Saki\Tile\TileSet;
use Saki\Win\Yaku\YakuSet;

/**
 * Holds immutable data during a game.
 * @package Saki\Game
 */
class GameData {
    private $playerCount;
    private $totalRoundType;
    private $initialPoint;
    private $finalPointStrategy;
    private $tileSet;
    private $yakuSet;

    /**
     * default: 4 player, east game, 25000-30000 initial point,
     */
    function __construct() {
        $this->playerCount = 4;
        $this->totalRoundType = GameLengthType::create(GameLengthType::EAST);
        $this->initialPoint = 25000;
        $this->finalPointStrategy = new CompositeFinalPointStrategy([
            RankingHorseFinalPointStrategy::fromType(RankingHorseType::create(RankingHorseType::UMA_10_20)),
            new MoundFinalPointStrategy(25000, 30000),
        ]);
        $this->tileSet = TileSet::createStandard();
        $this->yakuSet = YakuSet::createStandard();
    }

    function getPlayerCount() {
        return $this->playerCount;
    }

    function getTotalRoundType() {
        return $this->totalRoundType;
    }

    function getInitialPoint() {
        return $this->initialPoint;
    }

    function getFinalPointStrategy() {
        return $this->finalPointStrategy;
    }

    function getTileSet() {
        return $this->tileSet;
    }

    function getYakuSet() {
        return $this->yakuSet;
    }
}