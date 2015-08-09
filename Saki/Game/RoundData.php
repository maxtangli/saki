<?php
namespace Saki\Game;

use Saki\FinalScore\CompositeFinalScoreStrategy;
use Saki\FinalScore\MoundFinalScoreStrategy;
use Saki\FinalScore\RankingHorseFinalScoreStrategy;
use Saki\FinalScore\RankingHorseType;
use Saki\Tile\TileSet;

class RoundData {
    private $roundWindData;
    private $playerList;
    private $finalScoreStrategy;
    private $tileAreas;
    private $roundPhase;

    /**
     * default: 4 player, east game, 25000-30000 initial score,
     */
    function __construct() {
        $this->roundWindData = new RoundWindData(4, TotalRoundType::getInstance(TotalRoundType::EAST));
        $this->playerList = PlayerList::createStandard();
        $this->finalScoreStrategy = new CompositeFinalScoreStrategy([
            RankingHorseFinalScoreStrategy::fromType(RankingHorseType::getInstance(RankingHorseType::UMA_10_20)),
            new MoundFinalScoreStrategy(25000, 30000),
        ]);
        $wall = new Wall(TileSet::getStandardTileSet());
        $this->tileAreas = new TileAreas($wall, $this->playerList);
        $this->roundPhase = RoundPhase::getInitPhaseInstance();
    }

    function reset($keepDealer) {
        if (!is_bool($keepDealer)) {
            throw new \InvalidArgumentException('bool expected.');
        }
        $this->getRoundWindData()->reset($keepDealer);
        $nextDealer = $keepDealer ? $this->getPlayerList()->getDealerPlayer() : $this->getPlayerList()->getDealerOffsetPlayer(1);
        $this->getPlayerList()->reset($nextDealer);
        $this->getTileAreas()->getWall()->reset(true);
        $this->setRoundPhase(RoundPhase::getInitPhaseInstance());
    }

    function getRoundWindData() {
        return $this->roundWindData;
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

    function getRoundPhase() {
        return $this->roundPhase;
    }

    function setRoundPhase(RoundPhase $roundPhase) {
        $this->roundPhase = $roundPhase;
    }
}