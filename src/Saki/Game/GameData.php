<?php
namespace Saki\Game;

use Saki\FinalPoint\CompositeFinalPointStrategy;
use Saki\FinalPoint\MoundFinalPointStrategy;
use Saki\FinalPoint\RankingHorseFinalPointStrategy;
use Saki\FinalPoint\RankingHorseType;
use Saki\Tile\TileSet;
use Saki\Util\Immutable;
use Saki\Win\Yaku\YakuSet;

/**
 * Immutable data during a game.
 * @package Saki\Game
 */
class GameData implements Immutable {
    private $playerType;
    private $prevailingContext;
    private $initialPoint;
    private $finalPointStrategy;
    private $tileSet;
    private $yakuSet;

    /**
     * default: 4 player, east game, 25000-30000 initial point,
     */
    function __construct() {
        $playerType = PlayerType::create(4);

        $this->playerType = $playerType;
        $this->prevailingContext = new PrevailingContext(
            $playerType, PrevailingType::create(PrevailingType::EAST)
        );
        $this->initialPoint = 25000;
        $this->finalPointStrategy = new CompositeFinalPointStrategy([
            RankingHorseFinalPointStrategy::fromType(RankingHorseType::create(RankingHorseType::UMA_10_20)),
            new MoundFinalPointStrategy(25000, 30000),
        ]);
        $this->tileSet = TileSet::createStandard();
        $this->yakuSet = YakuSet::createStandard();
    }

    /**
     * @return PlayerType
     */
    function getPlayerType() {
        return $this->playerType;
    }

    /**
     * @return PrevailingContext
     */
    function getPrevailingContext() {
        return $this->prevailingContext;
    }

    /**
     * @return int
     */
    function getInitialPoint() {
        return $this->initialPoint;
    }

    /**
     * @return CompositeFinalPointStrategy
     */
    function getFinalPointStrategy() {
        return $this->finalPointStrategy;
    }

    /**
     * @return TileSet
     */
    function getTileSet() {
        return $this->tileSet;
    }

    /**
     * @return YakuSet
     */
    function getYakuSet() {
        return $this->yakuSet;
    }
}