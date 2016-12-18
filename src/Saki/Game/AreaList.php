<?php
namespace Saki\Game;
use Saki\Game\Meld\MeldList;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Game
 */
class AreaList extends ArrayList {
    /**
     * @param Round $round
     * @return AreaList A List of Area, same size with PlayerList, order by ascend initial SeatWind.
     */
    static function create(Round $round) {
        $toArea = function (SeatWind $initialSeatWind) use ($round) {
            return new Area($initialSeatWind, $round);
        };
        $playerType = $round->getRule()->getPlayerType();
        $areaList = $playerType->getSeatWindList($toArea);
        return new static($areaList->toArray());
    }

    /**
     * @param bool $keepDealer
     */
    function roll(bool $keepDealer) {
        $roll = function (Area $area) use ($keepDealer) {
            $area->roll($area->getSeatWind()->toRolled($keepDealer));
        };
        $this->walk($roll);
    }

    /**
     * @param Tile[][] $dealResult
     */
    function deal(array $dealResult) {
        $acceptDeal = function (Area $area) use ($dealResult) {
            $initialTiles = $dealResult[$area->getSeatWind()->__toString()];
            $newHand = new Hand(new TileList($initialTiles), new MeldList(), Target::createNull());
            $area->setHand($newHand);
        };
        $this->walk($acceptDeal);
    }

    /**
     * @param PrevailingStatus $prevailingStatus
     */
    function debugInit(PrevailingStatus $prevailingStatus) {
        $nextDealerInitialSeatWind = $prevailingStatus->getInitialSeatWindOfDealer();
        $isInitialSeatWind = function (Area $area) use ($nextDealerInitialSeatWind) {
            return $area->getInitialSeatWind() == $nextDealerInitialSeatWind;
        };
        /** @var Area $nextDealerArea */
        $nextDealerArea = $this->getSingle($isInitialSeatWind);
        $nextDealerSeatWind = $nextDealerArea->getSeatWind();

        $initArea = function (Area $area) use ($nextDealerSeatWind) {
            $area->debugInit($area->getSeatWind()->toNextSelf($nextDealerSeatWind));
        };
        $this->walk($initArea);
    }

    /**
     * @param SeatWind $initialSeatWind
     * @return Area
     */
    private function getInitialSeatWindArea(SeatWind $initialSeatWind) {
        $isInitialSeatWind = function (Area $area) use ($initialSeatWind) {
            return $area->getInitialSeatWind() == $initialSeatWind;
        };
        return $this->getSingle($isInitialSeatWind);
    }

    /**
     * @param SeatWind $seatWind
     * @return Area
     */
    function getArea(SeatWind $seatWind) {
        $isSeatWind = function (Area $area) use ($seatWind) {
            return $area->getSeatWind() == $seatWind;
        };
        return $this->getSingle($isSeatWind);
    }

    /**
     * @param SeatWind[] $excludes
     * @return SeatWind[]
     */
    function getOtherSeatWinds(array $excludes) {
        return SeatWind::createList($this->count())
            ->remove($excludes)
            ->toArray();
    }
}