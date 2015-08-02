<?php
namespace Saki\Game;

use Saki\FinalScore\CompositeFinalScoreStrategy;
use Saki\FinalScore\MoundFinalScoreStrategy;
use Saki\FinalScore\RankingHorseFinalScoreStrategy;
use Saki\FinalScore\RankingHorseType;
use Saki\Tile\TileSet;

class RoundData {
    private $roundWindData;

    /**
     * @var Wall
     */
    private $wall; // 牌山
    private $accumulatedReachCount; // 積み棒
    /**
     * @var PlayerList
     */
    private $playerList;

    /**
     * @var FinalScoreStrategy
     */
    private $finalScoreStrategy;

    /**
     * @var TileAreas
     */
    private $tileAreas;

    /**
     * default: 4 player, east game, 25000-30000 initial score,
     */
    function __construct() {
        $this->roundWindData = new RoundWindData(4, GameLengthType::getInstance(GameLengthType::EAST));

        $this->wall = new Wall(TileSet::getStandardTileSet());
        $this->accumulatedReachCount = 0;
        $this->playerList = PlayerList::createStandard();
        $this->finalScoreStrategy = new CompositeFinalScoreStrategy([
            RankingHorseFinalScoreStrategy::fromType(RankingHorseType::getInstance(RankingHorseType::UMA_10_20)),
            new MoundFinalScoreStrategy(25000, 30000),
        ]);
        $this->tileAreas = new TileAreas($this->wall, $this->playerList);
    }

    function reset($keepDealer) {
        if (!is_bool($keepDealer)) {
            throw new \InvalidArgumentException('bool expected.');
        }
        $this->getRoundWindData()->reset($keepDealer);
        $this->getWall()->reset(true);
        $nextDealer = $keepDealer ? $this->getPlayerList()->getDealerPlayer() : $this->getPlayerList()->getDealerOffsetPlayer(1);
        $this->getPlayerList()->reset($nextDealer);
    }

    function getRoundWindData() {
        return $this->roundWindData;
    }

    function getWall() {
        return $this->wall;
    }

    function getAccumulatedReachCount() {
        return $this->accumulatedReachCount;
    }

    function setAccumulatedReachCount($accumulatedReachCount) {
        $this->accumulatedReachCount = $accumulatedReachCount;
    }

    function getPlayerList() {
        return $this->playerList;
    }

    function getFinalScoreStrategy() {
        return $this->finalScoreStrategy;
    }

    function getTileAreas() {
        return $this->tileAreas;
    }

    function addAccumulatedReachCount() {
        $this->setAccumulatedReachCount($this->getAccumulatedReachCount() + 1);
    }

    function hasMinusScorePlayer() {
        return $this->getPlayerList()->isAny(function(Player $player){return $player->getScore() < 0;});
    }
}