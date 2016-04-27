<?php
namespace Saki\Game;

use Saki\Tile\TileSet;
use Saki\Util\Immutable;
use Saki\Win\Draw\DrawAnalyzer;
use Saki\Win\Score\CompositeScoreStrategy;
use Saki\Win\Score\OkaScoreStrategy;
use Saki\Win\Score\RankUmaScoreStrategy;
use Saki\Win\WinAnalyzer;
use Saki\Win\Yaku\YakuSet;

/**
 * Immutable data during a game.
 * @package Saki\Game
 */
class GameData implements Immutable {
    // specified
    private $playerType;
    private $prevailingContext;
    private $scoreStrategy;
    private $tileSet;
    private $yakuSet;
    // generated
    private $winAnalyzer;
    private $drawAnalyzer;

    /**
     * default: 4 player, east game, 25000-30000 initial point,
     */
    function __construct() {
        // specified
        $playerType = PlayerType::create(4);
        $pointSetting = new PointSetting($playerType, 25000, 30000);

        $this->playerType = $playerType;
        $this->prevailingContext = new PrevailingContext(
            $playerType, PrevailingType::create(PrevailingType::EAST)
        );
        $this->scoreStrategy = new CompositeScoreStrategy($pointSetting, [
            new RankUmaScoreStrategy($pointSetting),
            new OkaScoreStrategy($pointSetting)
        ]);
        $this->tileSet = TileSet::createStandard();
        $this->yakuSet = YakuSet::createStandard();
        // generated
        $this->winAnalyzer = new WinAnalyzer($this->yakuSet);
        $this->drawAnalyzer = DrawAnalyzer::createStandard();
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
     * @return CompositeScoreStrategy
     */
    function getScoreStrategy() {
        return $this->scoreStrategy;
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

    /**
     * @return WinAnalyzer
     */
    function getWinAnalyzer() {
        return $this->winAnalyzer;
    }

    /**
     * @return DrawAnalyzer
     */
    function getDrawAnalyzer() {
        return $this->drawAnalyzer;
    }
}