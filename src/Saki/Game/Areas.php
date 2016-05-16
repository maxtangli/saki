<?php

namespace Saki\Game;

use Saki\Meld\MeldList;
use Saki\Tile\TileList;
use Saki\Tile\TileSet;
use Saki\Util\ArrayList;

/**
 * Provide collaborate operations on 1 Wall and 2-4 Area.
 * @package Saki\Game
 */
class Areas {
    // variable
    /**
     * An ArrayList of Area, same size with PlayerList, order by ascend initial SeatWind.
     * @var ArrayList
     */
    private $areaList;
    private $pointHolder;
    private $riichiHolder;
    // round variable
    private $wall;
    private $currentTurn;
    private $openHistory;
    private $claimHistory;
    private $targetHolder;

    function __construct(TileSet $tileSet, PointSetting $pointSetting, PlayerList $playerList) {
        // variable
        $this->riichiHolder = new RiichiHolder($playerList->getPlayerType());
        $this->pointHolder = new PointHolder($pointSetting);

        // round variable
        $this->wall = new Wall($tileSet);
        $this->currentTurn = Turn::createFirst();
        $this->openHistory = new OpenHistory();
        $this->claimHistory = new ClaimHistory();
        $this->targetHolder = new TargetHolder();

        // immutable
        $this->areaList = $playerList->toArrayList(function (Player $player) {
            return new Area($player, $this);
        });
    }

    /**
     * @param bool $keepDealer
     * @param bool $isWin
     */
    function roll(bool $keepDealer, bool $isWin) {
        // variable
        $this->areaList->walk(function (Area $area) use ($keepDealer) {
            $area->roll($area->getSeatWind()->toRolled($keepDealer));
        });
        // $this->pointHolder no change
        $this->riichiHolder->roll($isWin);

        // round variable
        $this->wall->reset(true);
        $this->currentTurn = Turn::createFirst();
        $this->openHistory->reset();
        $this->claimHistory->reset();
        $this->targetHolder->init();
    }

    function debugInit(SeatWind $nextDealerInitialSeatWind) {
        // variable
        $nextDealerArea = $this->getInitialSeatWindArea($nextDealerInitialSeatWind);
        $nextDealerSeatWind = $nextDealerArea->getSeatWind();
        $this->areaList->walk(function (Area $area) use ($nextDealerSeatWind) {
            $area->debugInit($area->getSeatWind()->toNextSelf($nextDealerSeatWind));
        });
        $this->pointHolder->init();
        $this->riichiHolder->init();

        // round variable
        $this->wall->reset(true);
        $this->currentTurn = Turn::createFirst();
        $this->openHistory->reset();
        $this->claimHistory->reset();
        $this->targetHolder->init();
    }

    /**
     * @return ArrayList
     */
    function getAreaList() {
        return $this->areaList;
    }

    /**
     * @param SeatWind $seatWind
     * @return Area
     */
    function getArea(SeatWind $seatWind) {
        return $this->areaList->getSingle(function (Area $area) use ($seatWind) {
            return $area->getSeatWind() == $seatWind;
        });
    }

    /**
     * @param SeatWind $initialSeatWind
     * @return Area
     */
    function getInitialSeatWindArea(SeatWind $initialSeatWind) {
        return $this->areaList->getSingle(function (Area $area) use ($initialSeatWind) {
            return $area->getPlayer()->getInitialSeatWind() == $initialSeatWind;
        });
    }

    /**
     * @return Area
     */
    function getDealerArea() {
        return $this->getArea(SeatWind::createEast());
    }

    /**
     * @return Area
     */
    function getCurrentArea() {
        return $this->getArea($this->getCurrentSeatWind());
    }

    /**
     * @return PointHolder
     */
    function getPointHolder() {
        return $this->pointHolder;
    }

    /**
     * @return RiichiHolder
     */
    function getRiichiHolder() {
        return $this->riichiHolder;
    }

    /**
     * @return Wall
     */
    function getWall() {
        return $this->wall;
    }

    /**
     * @return Turn
     */
    function getTurn() {
        return $this->currentTurn;
    }

    /**
     * @return SeatWind
     */
    function getCurrentSeatWind() {
        return $this->getTurn()->getSeatWind();
    }

    /**
     * @param SeatWind[] $excludes
     * @return SeatWind[]
     */
    function getOtherSeatWinds(array $excludes) {
        return SeatWind::createList($this->areaList->count())
            ->remove($excludes)->toArray();
    }

    /**
     * Roll to $seatWind.
     * If $seatWind is not current, handle CircleCount update.
     * Do nothing otherwise.
     * @param SeatWind $seatWind
     */
    function toSeatWind(SeatWind $seatWind) {
        $this->currentTurn = $this->currentTurn->toSeatWind($seatWind);
    }

    /**
     * @return TargetHolder
     */
    function getTargetHolder() {
        return $this->targetHolder;
    }

    /**
     * @return OpenHistory
     */
    function getOpenHistory() {
        return $this->openHistory;
    }

    /**
     * @return ClaimHistory
     */
    function getClaimHistory() {
        return $this->claimHistory;
    }

    function deal() {
        // no Turn-change
        $playerType = PlayerType::create($this->areaList->count());
        $deal = $this->getWall()->deal($playerType);
        $this->areaList->walk(function (Area $area) use ($deal) {
            $initialTiles = $deal[$area->getSeatWind()->__toString()];
            $newHand = new Hand(new TileList($initialTiles), new MeldList(), Target::createNull());
            $area->setHand($newHand);
        });
        // no Target
    }
}