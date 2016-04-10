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
    // immutable
    private $turnProvider;
    /**
     * An ArrayList of player count Area, order by ascend initial SeatWind.
     * Design note: Areas not implement as ArrayList since Area's order varies by Round.
     * @var ArrayList
     */
    private $areaList;
    private $playerList; // todo remove
    // variable
    private $reachPoints;
    // round variable
    private $currentTurn;
    private $wall;
    private $target;
    private $openHistory;
    private $declareHistory;

    function __construct(Wall $wall, PlayerList $playerList, callable $turnProvider) {
        $this->turnProvider = $turnProvider;
        $this->areaList = new ArrayList();
        $this->playerList = $playerList;
        $playerList->walk(function (Player $player) { // todo remove PlayerList in Areas
            $getTarget = function () use ($player) {
                return $this->getTarget($player);
            };
            $area = new Area($getTarget, $player->getInitialSeatWind(), $player->getInitialPoint());
            $player->setArea($area);
            $this->areaList->insertLast($area);
        });

        $this->reachPoints = 0;

        $this->currentTurn = Turn::createFirst();
        $this->wall = $wall;
        $this->target = Target::createNull();
        $this->openHistory = new OpenHistory();
        $this->declareHistory = new DeclareHistory();
    }

    function reset(SeatWind $nextDealer) {
        $this->playerList->walk(function (Player $player) use ($nextDealer) {
            $area = $player->getArea();
            $area->reset($area->getSeatWind()->toNextSelf($nextDealer));
        });

        // $this->reachPoints not changed

        $this->currentTurn = Turn::createFirst();
        $this->wall->reset(true);
        $this->target = Target::createNull();
        $this->openHistory->reset();
        $this->declareHistory->reset();
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
     * @return Area
     */
    function getCurrentArea() {
        return $this->getArea($this->getCurrentTurn()->getSeatWind());
    }

    function tempGetCurrentPlayer() {
        return $this->playerList->getPlayer($this->getCurrentTurn()->getSeatWind());
    }

    function tempGetOffsetPlayer(int $offset) {
        $currentSeatWind = $this->getCurrentTurn()->getSeatWind();
        $offsetSeatWind = $currentSeatWind->toNext($offset);
        return $this->playerList->getPlayer($offsetSeatWind);
    }

    /**
     * used in YakuTestData
     * todo move into Hand logic?
     * @param Player $player
     * @param TileList $private
     * @param MeldList|null $declare Use current if null.
     * @param Tile|null $targetTile
     */
    function debugSetPrivate(Player $player, TileList $private,
                             MeldList $declare = null, Tile $targetTile = null) {
        $validPrivate = $private->getHandSize()->isPrivate();
        if (!$validPrivate) {
            throw new \InvalidArgumentException();
        }

        $validTargetTile = $targetTile === null || $private->valueExist($targetTile);
        if (!$validTargetTile) {
            throw new \InvalidArgumentException();
        }

        $currentTargetTile = $this->target->getTile();
        $actualTargetTile = $targetTile ??
            ($private->valueExist($currentTargetTile) ? $currentTargetTile : $private->getLast());
        $actualPublic = $private->getCopy()->remove($actualTargetTile);
        $actualDeclare = $declare ?? $player->getArea()->getHand()->getDeclare();

        $player->getArea()->debugSet($actualPublic, $actualDeclare, $private);
        $this->setTargetData($this->target->toSetValue($actualTargetTile));
    }

    // todo move into Hand logic?
    // Used in: MockHandCommand, TileParamDeclaration
    function debugMockHand(Player $player, TileList $replace) {
        if ($replace->count() == 14) {
            $this->debugSetPrivate($player, $replace);
            return;
        }

        $replaceIndexes = range(0, $replace->count() - 1);
        $area = $player->getArea();
        $hand = $area->getHand();
        $public = $hand->getPublic()->getCopy()
            ->replaceAt($replaceIndexes, $replace->toArray());
        $declare = $hand->getDeclare();

        $area->debugSet($public, $declare, null);
    }

    // getter,setter
    /**
     * @return Turn
     */
    function getCurrentTurn() {
        $f = $this->turnProvider;
        return $f();
    }

    /**
     * @param SeatWind $seatWind
     */
    function toSeatWind(SeatWind $seatWind) {
        $this->currentTurn = $this->currentTurn->toSeatWind($seatWind);
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

    function getWall() {
        return $this->wall;
    }

    protected function getTarget(Player $player) {
        $seeTarget = $this->target->exist()
            && $this->target->isOwner($player->getArea()->getSeatWind());
        return $seeTarget ? $this->target : Target::createNull();
    }

    /**
     * @param Target $target
     */
    protected function setTargetData(Target $target) {
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
        $this->openHistory->record(new OpenRecord($this->getCurrentTurn(), $tile, $isDiscard));
    }

    function getDeclareHistory() {
        return $this->declareHistory;
    }

    protected function recordDeclare() {
        $this->declareHistory->recordDeclare($this->getCurrentTurn());
    }

    // convert
    // data
    function getOutsideRemainTileAmount(Tile $tile) {
        $allPlayerDiscard = $this->playerList->getAggregated(TileList::fromString(''), function (TileList $l, Player $player) {
            return $l->insertLast($player->getArea()->getDiscard()->toArray());
        });

        $totalCount = $this->getWall()->getTileSet()->getCount(Utils::toPredicate($tile));
        $discardCount = $allPlayerDiscard->getCount(Utils::toPredicate($tile));
        $remainCount = $totalCount - $discardCount;
        return max(0, $remainCount); // note: in tests $remainCount may be negative because of mocking.
    }

    function isFirstTurnWin(Player $targetPlayer) {
        $reachStatus = $targetPlayer->getArea()->getReachStatus();
        if (!$reachStatus->isReach()) {
            return false;
        }

        $reachTurn = $reachStatus->getReachTurn();
        $reachNextTurn = new Turn(
            $reachTurn->getCircleCount() + 1,
            $targetPlayer->getArea()->getSeatWind()
        );
        $currentTurn = $this->getCurrentTurn();
        $isSameOrNextCircleCount = $currentTurn->isBeforeOrSame($reachNextTurn);

        $noDeclareSinceReach = !$this->getDeclareHistory()->hasDeclare($reachTurn);
        return $isSameOrNextCircleCount && $noDeclareSinceReach;
    }

    // operation

    function drawInitForAll() {
        // each player draw initial tiles, notice NOT to trigger turn changes by avoid calling PlayerList->toPlayer()
        $drawTileCounts = [4, 4, 4, 1];
        foreach ($drawTileCounts as $drawTileCount) {
            foreach ($this->playerList as $player) {
                /** @var Player $player */
                $player = $player;
                $newTiles = $this->getWall()->drawInit($drawTileCount);
                $player->getArea()->drawInit($newTiles);
            }
        }
        // no target until now
    }

    function draw(Player $player) {
        $newTile = $this->getWall()->draw();
        $player->getArea()->draw($newTile);

        $this->setTargetData(
            new Target($newTile, TargetType::create(TargetType::DRAW), $player->getArea()->getSeatWind())
        );
    }

    function drawReplacement(Player $player) {
        $newTile = $this->getWall()->drawReplacement();
        $player->getArea()->draw($newTile);

        $this->setTargetData(
            new Target($newTile, TargetType::create(TargetType::REPLACEMENT), $player->getArea()->getSeatWind())
        );
    }

    function discard(Player $player, Tile $selfTile) {
        $player->getArea()->discard($selfTile);

        $this->setTargetData(
            new Target($selfTile, TargetType::create(TargetType::DISCARD), $player->getArea()->getSeatWind())
        );

        $this->recordOpen($selfTile, true);
    }

    /**
     * WARNING: assume some conditions validated by caller
     * @param Player $player
     * @param Tile $selfTile
     */
    function reach(Player $player, Tile $selfTile) {
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

        $notReachYet = !$player->getArea()->getReachStatus()->isReach();
        if (!$notReachYet) { // Area
            throw new \InvalidArgumentException('Reach condition violated: not reach yet.');
        }

        $isConcealed = $player->getArea()->getHand()->getDeclare()->count() == 0;
        if (!$isConcealed) { // Area
            throw new \InvalidArgumentException('Reach condition violated: is isConcealed.');
        }

        $enoughPoint = $player->getArea()->getPoint() >= 1000;
        if (!$enoughPoint) { // Area
            throw new \InvalidArgumentException('Reach condition violated: at least 1000 point.');
        }

        $hasDrawTileChance = $this->getWall()->getRemainTileCount() >= 4;
        if (!$hasDrawTileChance) { // TilesArea
            throw new \InvalidArgumentException('Reach condition violated: at least 1 draw tile chance.');
        }

        $player->getArea()->discard($selfTile);

        $this->setTargetData(
            new Target($selfTile, TargetType::create(TargetType::DISCARD), $player->getArea()->getSeatWind())
        );

        $player->getArea()->setReachStatus(
            new ReachStatus($this->getCurrentTurn())
        );

        $player->getArea()->setPoint($player->getArea()->getPoint() - 1000);
        $this->setReachPoints($this->getReachPoints() + 1000);

        $this->recordOpen($selfTile, true);
    }

    function concealedKong(Player $actPlayer, Tile $selfTile) {
        $handTiles = [$selfTile, $selfTile, $selfTile, $selfTile];
        $actPlayer->getArea()->declareMeld(QuadMeldType::create(), true, $handTiles, null, null);

        $this->drawReplacement($actPlayer); // set target

        $this->recordDeclare();
    }

    function plusKongBefore(Player $actPlayer, Tile $selfTile) {
        $target = $actPlayer->getArea()->tempGenKongTargetData($selfTile);

        $this->setTargetData($target);
    }

    function plusKongAfter(Player $actPlayer, Tile $plusKongBeforeTile) { // todo remove $plusKongBeforeTile
        $this->setTargetData(
            $this->target->toSetValue(null, TargetType::create(TargetType::KEEP))
        ); // todo belong to where: here? plusKongCommand? PublicPhaseState?

        $declaredMeld = new Meld([$plusKongBeforeTile, $plusKongBeforeTile, $plusKongBeforeTile]);
        $actPlayer->getArea()->declareMeld(QuadMeldType::create(), null, null, $plusKongBeforeTile, $declaredMeld);

        $this->drawReplacement($actPlayer); // set target

        $this->recordOpen($plusKongBeforeTile, false);
        $this->recordDeclare();
    }

    function chow(Player $actPlayer, Tile $selfTile1, Tile $selfTile2, Player $targetPlayer) {
        $this->assertNextPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getArea();
        $actPlayerArea = $actPlayer->getArea();

        $handTiles = [$selfTile1, $selfTile2];
        $targetTile = $targetPlayerArea->getDiscard()->getLast(); // validate
        $actPlayerArea->declareMeld(RunMeldType::create(), false, $handTiles, $targetTile, null); // validate
        $targetPlayerArea->removeDiscardLast();

        $keepTargetData = $actPlayerArea->tempGenKeepTargetData();
        $this->setTargetData($keepTargetData);

        $this->recordDeclare();
    }

    function pong(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getArea();
        $actPlayerArea = $actPlayer->getArea();

        $targetTile = $targetPlayerArea->getDiscard()->getLast(); // validate
        $handTiles = [$targetTile, $targetTile];
        $actPlayerArea->declareMeld(TripleMeldType::create(), false, $handTiles, $targetTile, null); // validate
        $targetPlayerArea->removeDiscardLast();

        $keepTargetData = $actPlayerArea->tempGenKeepTargetData();
        $this->setTargetData($keepTargetData);

        $this->recordDeclare();
    }

    function bigKong(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getArea();
        $actPlayerArea = $actPlayer->getArea();

        $targetTile = $targetPlayerArea->getDiscard()->getLast(); // validate
        $handTiles = [$targetTile, $targetTile, $targetTile];
        $actPlayerArea->declareMeld(QuadMeldType::create(), false, $handTiles, $targetTile, null); // validate
        $targetPlayerArea->removeDiscardLast();

        $this->drawReplacement($actPlayer); // set target

        $this->recordDeclare();
    }

    function smallKong(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $currentPlayerArea = $targetPlayer->getArea();
        $playerArea = $actPlayer->getArea();

        $targetTile = $currentPlayerArea->getDiscard()->getLast(); // validate
        $declaredMeld = new Meld([$targetTile, $targetTile, $targetTile]);
        $playerArea->declareMeld(QuadMeldType::create(), false, null, $targetTile, $declaredMeld); // validate
        $currentPlayerArea->removeDiscardLast();

        $this->drawReplacement($actPlayer); // set target

        $this->recordDeclare();
    }

    protected function assertNextPlayer(Player $nextPlayer, Player $prePlayer) {
        list($iNext, $iPre) = $this->playerList->getIndex([$nextPlayer, $prePlayer]);
        $valid = ($iNext == ($iPre + 1));
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf('[%s] should be next of [%s]', $nextPlayer, $prePlayer)
            );
        }
    }

    protected function assertDifferentPlayer(Player $player, Player $otherPlayer) {
        $valid = $player != $otherPlayer;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
    }
}