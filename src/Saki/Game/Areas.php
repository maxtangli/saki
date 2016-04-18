<?php

namespace Saki\Game;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\QuadMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * Provide collaborate operations on 1 Wall and 2-4 Area.
 * @package Saki\Game
 */
class Areas {
    // variable
    /**
     * An ArrayList of player count Area, order by ascend initial SeatWind.
     * Design note: Areas not implement as ArrayList since Area's order varies by Round.
     * @var ArrayList
     */
    private $areaList; // todo lock
    private $reachPoints;
    // round variable
    private $currentTurn;
    private $wall;
    private $target;
    private $openHistory;
    private $declareHistory;

    function __construct(Wall $wall, PlayerList $playerList) {
        // immutable
        $this->areaList = new ArrayList();
        $playerList->walk(function (Player $player) { // todo remove PlayerList in Areas
            $getTarget = function (SeatWind $seatWind) {
                return $this->getTarget($seatWind);
            };
            $area = new Area($getTarget, $player);
            $player->setArea($area);
            $this->areaList->insertLast($area);
        });

        // variable
        $this->reachPoints = 0;

        // round variable
        $this->currentTurn = Turn::createFirst();
        $this->wall = $wall;
        $this->target = Target::createNull();
        $this->openHistory = new OpenHistory();
        $this->declareHistory = new DeclareHistory();
    }

    /**
     * @param bool $keepDealer
     */
    function roll(bool $keepDealer) {
        // variable
        $this->areaList->walk(function (Area $area) use ($keepDealer) {
            $area->roll($area->getSeatWind()->toRolled($keepDealer));
        });
        // $this->reachPoints not changed

        // round variable
        $this->currentTurn = Turn::createFirst();
        $this->wall->reset(true);
        $this->target = Target::createNull();
        $this->openHistory->reset();
        $this->declareHistory->reset();
    }

    function debugInit(SeatWind $nextDealerInitialSeatWind) {
        // variable
        $nextDealerArea = $this->getAreaByInitial($nextDealerInitialSeatWind);
        $nextDealerSeatWind = $nextDealerArea->getSeatWind();
        $this->areaList->walk(function (Area $area) use ($nextDealerSeatWind) {
            $area->debugInit($area->getSeatWind()->toNextSelf($nextDealerSeatWind));
        });
        $this->reachPoints = 0;

        // round variable
        $this->currentTurn = Turn::createFirst();
        $this->wall->reset(true);
        $this->target = Target::createNull();
        $this->openHistory->reset();
        $this->declareHistory->reset();
    }

    // todo move into Hand logic?
    // $declare Use current if null.
    function debugSetPrivate(SeatWind $actor, TileList $private,
                             MeldList $declare = null, Tile $targetTile = null) {
        $validPrivate = $private->getHandSize()->isPrivate();
        if (!$validPrivate) {
            throw new \InvalidArgumentException();
        }

        $validTargetTile = $targetTile === null || $private->valueExist($targetTile);
        if (!$validTargetTile) {
            throw new \InvalidArgumentException();
        }

        $area = $this->getArea($actor);

        $currentTargetTile = $this->target->getTile();
        $actualTargetTile = $targetTile ??
            ($private->valueExist($currentTargetTile) ? $currentTargetTile : $private->getLast());
        $actualPublic = $private->getCopy()->remove($actualTargetTile);
        $actualDeclare = $declare ?? $area->getHand()->getDeclare();

        $area->debugSet($actualPublic, $actualDeclare);
        $this->setTarget($this->target->toSetValue($actualTargetTile));
    }

    // todo move into Hand logic?
    // Used in: MockHandCommand, TileParamDeclaration
    function debugMockHand(SeatWind $actor, TileList $replace) {
        if ($replace->count() == 14) {
            $this->debugSetPrivate($actor, $replace);
            return;
        }

        $replaceIndexes = range(0, $replace->count() - 1);
        $area = $this->getArea($actor);
        $hand = $area->getHand();
        $public = $hand->getPublic()->getCopy()
            ->replaceAt($replaceIndexes, $replace->toArray());
        $declare = $hand->getDeclare();

        $area->debugSet($public, $declare);
    }

    /**
     * @return PointFacade
     */
    function getPointFacade() {
        $seatWindList = SeatWind::createList($this->areaList->count());
        $items = $seatWindList->select(function (SeatWind $seatWind) {
            $point = $this->getArea($seatWind)->getPoint();
            return new PointFacadeItem($seatWind, $point);
        })->toArray();
        return new PointFacade($items);
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
    function getReachPoints() {
        return $this->reachPoints;
    }

    /**
     * @param int $reachPoints
     */
    function setReachPoints(int $reachPoints) {
        $this->reachPoints = $reachPoints;
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
    function toSeatWind(SeatWind $seatWind) {
        $this->currentTurn = $this->currentTurn->toSeatWind($seatWind);
    }

    /**
     * @return Wall
     */
    function getWall() {
        return $this->wall;
    }

    protected function getTarget(SeatWind $seatWind) {
        $seeTarget = $this->target->exist()
            && $this->target->isOwner($seatWind);
        return $seeTarget ? $this->target : Target::createNull();
    }

    /**
     * @param Target $target
     */
    protected function setTarget(Target $target) {
        $this->target = $target;
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
    protected function recordOpen(Tile $tile, bool $isDiscard) {
        $this->openHistory->record(new OpenRecord($this->getTurn(), $tile, $isDiscard));
    }

    function getDeclareHistory() {
        return $this->declareHistory;
    }

    protected function recordDeclare() {
        $this->declareHistory->recordDeclare($this->getTurn());
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
        $reachStatus = $this->getArea($actor)->getReachStatus();
        if (!$reachStatus->isFirstTurn($this->getTurn())) {
            return false;
        }

        $noDeclareSinceReach = !$this->getDeclareHistory()
            ->hasDeclare($reachStatus->getReachTurn());
        return $noDeclareSinceReach;
    }

    function drawInitForAll() {
        // notice do NOT trigger turn changes
        $drawTileCounts = [4, 4, 4, 1];
        foreach ($drawTileCounts as $drawTileCount) {
            $this->areaList->walk(function (Area $area) use ($drawTileCount) {
                $newTiles = $this->getWall()->drawInit($drawTileCount);
                $area->drawInit($newTiles);
            });
        }
        // no target
    }

    function draw(SeatWind $actor) {
        $newTile = $this->getWall()->draw();
        $this->getArea($actor)->draw($newTile);

        $this->setTarget(
            new Target($newTile, TargetType::create(TargetType::DRAW), $actor)
        );
    }

    function drawReplacement(SeatWind $actor) {
        $newTile = $this->getWall()->drawReplacement();
        $this->getArea($actor)->draw($newTile);

        $this->setTarget(
            new Target($newTile, TargetType::create(TargetType::REPLACEMENT), $actor)
        );
    }

    function discard(SeatWind $actor, Tile $selfTile) {
        $this->getArea($actor)->discard($selfTile);

        $this->setTarget(
            new Target($selfTile, TargetType::create(TargetType::DISCARD), $actor)
        );

        $this->recordOpen($selfTile, true);
    }

    /**
     * WARNING: assume some conditions validated by caller
     * @param SeatWind $actor
     * @param Tile $selfTile
     */
    function reach(SeatWind $actor, Tile $selfTile) {
        /**
         * https://ja.wikipedia.org/wiki/%E7%AB%8B%E7%9B%B4
         * 条件
         * - 立直していないこと。
         * - 門前であること。すなわち、チー、ポン、明槓をしていないこと。
         * - トビ有りのルールならば、点棒を最低でも1000点持っていること。つまり立直棒として1000点を供託したときにハコを割ってしまうような場合、立直はできない。供託時にちょうど0点になる場合、認められる場合と認められない場合がある。トビ無しの場合にハコを割っていた場合も、点棒を借りてリーチをかけることを認める場合と認めない場合がある。
         * - 壁牌（山）の残りが王牌を除いて4枚（三人麻雀では3枚）以上あること。すなわち立直を宣言した後で少なくとも1回の自摸が残されているということ。ただし、鳴きや暗槓が入って結果的に自摸の機会なく流局したとしてもペナルティはない。
         *
         * - * 聴牌していること。
         * - * 4人全員が立直をかけた場合、四家立直として流局となる（四家立直による途中流局を認めないルールもあり、その場合は続行される）。
         */

        $area = $this->getArea($actor);

        $notReachYet = !$area->getReachStatus()->isReach();
        if (!$notReachYet) { // Area
            throw new \InvalidArgumentException('Reach condition violated: not reach yet.');
        }

        $isConcealed = $area->getHand()->getDeclare()->count() == 0;
        if (!$isConcealed) { // Area
            throw new \InvalidArgumentException('Reach condition violated: is isConcealed.');
        }

        $enoughPoint = $area->getPoint() >= 1000;
        if (!$enoughPoint) { // Area
            throw new \InvalidArgumentException('Reach condition violated: at least 1000 point.');
        }

        $hasDrawTileChance = $this->getWall()->getRemainTileCount() >= 4;
        if (!$hasDrawTileChance) { // TilesArea
            throw new \InvalidArgumentException('Reach condition violated: at least 1 draw tile chance.');
        }

        $area->discard($selfTile);
        $this->setTarget(
            new Target($selfTile, TargetType::create(TargetType::DISCARD), $area->getSeatWind())
        );
        $area->setReachStatus(
            new ReachStatus($this->getTurn())
        );

        $area->setPoint($area->getPoint() - 1000);
        $this->setReachPoints($this->getReachPoints() + 1000);

        $this->recordOpen($selfTile, true);
    }

    function concealedKong(SeatWind $actor, Tile $selfTile) {
        $handTiles = [$selfTile, $selfTile, $selfTile, $selfTile];
        $this->getArea($actor)->declareMeld(QuadMeldType::create(), true, $handTiles, null, null);

        $this->drawReplacement($actor); // set target

        $this->recordDeclare();
    }

    function plusKongBefore(SeatWind $actor, Tile $selfTile) {
        $target = $this->getArea($actor)->tempGenKongTarget($selfTile);

        $this->setTarget($target);
    }

    function plusKongAfter(SeatWind $actor, Tile $plusKongBeforeTile) { // todo remove $plusKongBeforeTile
        $this->setTarget(
            $this->target->toSetValue(null, TargetType::create(TargetType::KEEP))
        ); // todo belong to where: here? plusKongCommand? PublicPhaseState?

        $declaredMeld = new Meld([$plusKongBeforeTile, $plusKongBeforeTile, $plusKongBeforeTile]);
        $this->getArea($actor)->declareMeld(QuadMeldType::create(), null, null, $plusKongBeforeTile, $declaredMeld);

        $this->drawReplacement($actor); // set target

        $this->recordOpen($plusKongBeforeTile, false);
        $this->recordDeclare();
    }

    function chow(SeatWind $actor, Tile $selfTile1, Tile $selfTile2) {
//        $this->assertNextPlayer($player, $targetPlayer);
        $targetArea = $this->getCurrentArea();
        $playerArea = $this->getArea($actor);

        $handTiles = [$selfTile1, $selfTile2];
        $targetTile = $targetArea->getDiscard()->getLast(); // validate
        $playerArea->declareMeld(RunMeldType::create(), false, $handTiles, $targetTile, null); // validate
        $targetArea->removeDiscardLast();

        $keepTarget = $playerArea->tempGenKeepTarget();
        $this->setTarget($keepTarget);

        $this->recordDeclare();
    }

    function pong(SeatWind $actor) {
//        $this->assertDifferentPlayer($player, $targetPlayer);
        $targetArea = $this->getCurrentArea();
        $playerArea = $this->getArea($actor);

        $targetTile = $targetArea->getDiscard()->getLast(); // validate
        $handTiles = [$targetTile, $targetTile];
        $playerArea->declareMeld(TripleMeldType::create(), false, $handTiles, $targetTile, null); // validate
        $targetArea->removeDiscardLast();

        $keepTarget = $playerArea->tempGenKeepTarget();
        $this->setTarget($keepTarget);

        $this->recordDeclare();
    }

    function bigKong(SeatWind $actor) {
//        $this->assertDifferentPlayer($player, $targetPlayer);
        $targetArea = $this->getCurrentArea();
        $playerArea = $this->getArea($actor);

        $targetTile = $targetArea->getDiscard()->getLast(); // validate
        $handTiles = [$targetTile, $targetTile, $targetTile];
        $playerArea->declareMeld(QuadMeldType::create(), false, $handTiles, $targetTile, null); // validate
        $targetArea->removeDiscardLast();

        $this->drawReplacement($actor); // set target

        $this->recordDeclare();
    }
}