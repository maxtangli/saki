<?php
namespace Saki\Game;
use Saki\Game\Meld\MeldList;
use Saki\Game\Tile\Tile;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\Pao\PaoList;

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
     * @param SeatWind $initial
     * @return Area
     */
    function getAreaByInitial(SeatWind $initial) {
        $isInitial = function (Area $area) use ($initial) {
            return $area->getInitialSeatWind() == $initial;
        };
        return $this->getSingle($isInitial);
    }

    /**
     * @param SeatWind[] $winners
     * @return PaoList
     */
    function generatePaoList(array $winners) {
        $paoList = new PaoList();
        foreach ($winners as $seatWind) {
            $area = $this->getArea($seatWind);
            if ($area->hasPao()) {
                $paoList->insertLast($area->getPao());
            }
        }
        return $paoList;
    }
}