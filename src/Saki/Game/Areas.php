<?php

namespace Saki\Game;

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Util\Utils;
use Saki\Win\Point\PointList;

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
    private $areaList; // todo lock
    private $riichiPoints;
    // round variable
    private $currentTurn;
    private $wall;
    private $targetHolder;
    private $openHistory;
    private $claimHistory;

    function __construct(Wall $wall, PlayerList $playerList) {
        // variable
        $this->riichiPoints = 0;

        // round variable
        $this->currentTurn = Turn::createFirst();
        $this->wall = $wall;
        $this->targetHolder = new TargetHolder();
        $this->openHistory = new OpenHistory();
        $this->claimHistory = new ClaimHistory();

        // immutable
        $this->areaList = new ArrayList();
        $playerList->walk(function (Player $player) {
            $area = new Area($player, $this);
            $this->areaList->insertLast($area);
        });
    }

    /**
     * @param bool $keepDealer
     */
    function roll(bool $keepDealer) {
        // variable
        $this->areaList->walk(function (Area $area) use ($keepDealer) {
            $area->roll($area->getSeatWind()->toRolled($keepDealer));
        });
        // $this->riichiPoints not changed

        // round variable
        $this->currentTurn = Turn::createFirst();
        $this->wall->reset(true);
        $this->targetHolder->init();
        $this->openHistory->reset();
        $this->claimHistory->reset();
    }

    function debugInit(SeatWind $nextDealerInitialSeatWind) {
        // variable
        $nextDealerArea = $this->getAreaByInitial($nextDealerInitialSeatWind);
        $nextDealerSeatWind = $nextDealerArea->getSeatWind();
        $this->areaList->walk(function (Area $area) use ($nextDealerSeatWind) {
            $area->debugInit($area->getSeatWind()->toNextSelf($nextDealerSeatWind));
        });
        $this->riichiPoints = 0;

        // round variable
        $this->currentTurn = Turn::createFirst();
        $this->wall->reset(true);
        $this->targetHolder->init();
        $this->openHistory->reset();
        $this->claimHistory->reset();
    }

    /**
     * @return PointList
     */
    function getPointList() {
        $seatWindList = SeatWind::createList($this->areaList->count());
        $pointPairs = $seatWindList->select(function (SeatWind $seatWind) {
            $point = $this->getArea($seatWind)->getPoint();
            return [$seatWind, $point];
        })->toArray();
        return PointList::fromPointPairs($pointPairs);
    }

    /**
     * @param array $pointChangeMap
     */
    function applyPointChangeMap(array $pointChangeMap) {
        $this->areaList->walk(function (Area $area) use ($pointChangeMap) {
            $pointChange = $pointChangeMap[$area->getSeatWind()->__toString()];
            $area->setPoint($area->getPoint() + $pointChange);
        });
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
    function getAreaByInitial(SeatWind $initialSeatWind) {
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
     * @return int
     */
    function getRiichiPoints() {
        return $this->riichiPoints;
    }

    /**
     * @param int $riichiPoints
     */
    function setRiichiPoints(int $riichiPoints) {
        $this->riichiPoints = $riichiPoints;
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
     * Roll to $seatWind and handle CircleCount update if $seatWind is not current.
     * Do nothing otherwise.
     * @param SeatWind $seatWind
     */
    function toOrKeepSeatWind(SeatWind $seatWind) {
        $this->currentTurn = $this->currentTurn->toSeatWind($seatWind);
    }

    /**
     * @return Wall
     */
    function getWall() {
        return $this->wall;
    }

    /**
     * @param Target $target
     */
    protected function setTarget(Target $target) {
        $this->targetHolder->setTarget($target);
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
     * @param Tile $tile
     * @param bool $isDiscard
     */
    function recordOpen(Tile $tile, bool $isDiscard) {
        $this->openHistory->record(new OpenRecord($this->getTurn(), $tile, $isDiscard));
    }

    /**
     * @return ClaimHistory
     */
    function getClaimHistory() {
        return $this->claimHistory;
    }

    function recordClaim(Turn $turn = null) {
        $this->claimHistory->recordClaim($turn ?? $this->getTurn());
    }

    function getOutsideRemainTileAmount(Tile $tile) { // todo move
        $allDiscarded = (new TileList())->fromSelectMany($this->areaList, function (Area $area) {
            return $area->getDiscard()->toArray();
        });

        $totalCount = $this->getWall()->getTileSet()->getCount(Utils::toPredicate($tile));
        $discardCount = $allDiscarded->getCount(Utils::toPredicate($tile));
        $remainCount = $totalCount - $discardCount;
        return max(0, $remainCount); // note: in tests $remainCount may be negative because of mocking.
    }

    function isFirstTurnWin(SeatWind $actor) { // todo move
        $riichiStatus = $this->getArea($actor)->getRiichiStatus();
        if (!$riichiStatus->isFirstTurn($this->getTurn())) {
            return false;
        }

        $noDeclareSinceRiichi = !$this->getClaimHistory()
            ->hasClaim($riichiStatus->getRiichiTurn());
        return $noDeclareSinceRiichi;
    }

    function deal() {
        // do NOT trigger turn changes
        $playerType = PlayerType::create($this->areaList->count());
        $deal = $this->getWall()->deal($playerType);
        $this->areaList->walk(function (Area $area) use ($deal) {
            $initialTiles = $deal[$area->getSeatWind()->__toString()];
            $newHand = new Hand(new TileList($initialTiles), new MeldList(), Target::createNull());
            $area->setHand($newHand);
        });
        // no target
    }
}