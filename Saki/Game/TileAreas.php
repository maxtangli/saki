<?php

namespace Saki\Game;

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayLikeObject;

/**
 * Provide collaborate operations on 1 Wall and 2-4 TileArea.
 * @package Saki\Game
 */
class TileAreas {
    // immutable
    private $playerList;
    private $roundTurnProvider;

    // immutable for each round, except for client setter calls
    private $accumulatedReachCount; // 積み棒

    // variable for each round
    private $wall;
    private $targetTile;
    private $openHistory;
    private $declareHistory;

    function __construct(Wall $wall, PlayerList $playerList, callable $roundTurnProvider) {
        $this->playerList = $playerList;
        $this->roundTurnProvider = $roundTurnProvider;

        $this->accumulatedReachCount = 0;

        $this->wall = $wall;
        $this->targetTile = null;
        $this->openHistory = new OpenHistory();
        $this->declareHistory = new DeclareHistory();
    }

    function reset() {
        $this->wall->reset(true);
        $this->targetTile = null;
        $this->openHistory->reset();
        $this->declareHistory->reset();
    }

    /**
     * note: the function is protected since
     * - client should be clearly about current phase and hand count by calling setPrivate or setPublic rather than this function.
     * @param Player $player
     * @param TileList $hand
     * @param MeldList|null $declareMeldList
     * @param Tile|null $targetTile
     */
    protected function debugSetHandImpl(Player $player, TileList $hand, MeldList $declareMeldList = null, Tile $targetTile = null) {
        $currentHand = $player->getTileArea()->getHandReference();
        $validHandPhase = $hand->getHandCount()->equalsPhase($currentHand->getHandCount());
        if (!$validHandPhase) {
            throw new \InvalidArgumentException(
                sprintf('Invalid $hand[%s(%s)], expected same phase one with current hand[%s(%s)].',
                    $hand, $hand->getHandCount(), $currentHand, $currentHand->getHandCount())
            );
        }

        $actualDeclareMeldList = $declareMeldList ?? MeldList::fromString('');

        if ($hand->isPrivateHand() && $targetTile && $hand->valueExist($targetTile)) {
            $actualTargetTile = $targetTile;
        } elseif ($hand->isPrivateHand() && !$targetTile) {
            $actualTargetTile = $hand[0];
        } elseif ($hand->isPublicHand() && !$targetTile && $this->hasTargetTile()) {
            $actualTargetTile = $this->getTargetTile()->getTile();
        } else {
            throw new \InvalidArgumentException(
                sprintf('Invalid combination of $hand[%s], $targetTile[%s].', $hand, $targetTile)
            );
        }

        $privateFullCount = $hand->count() + ($hand->isPublicHand() ? 1 : 0) + $actualDeclareMeldList->getHandCount();
        $validPrivateFullCount = $privateFullCount == 14;
        if (!$validPrivateFullCount) {
            throw new \InvalidArgumentException(
                sprintf('Invalid privateFullCount[%s] of $hand[%s], $actualDeclareMeldList[%s], $targetTile[%s].'
                    , $privateFullCount, $hand, $actualDeclareMeldList, $targetTile)
            );
        }

        $player->getTileArea()->getHandReference()->setInnerArray($hand->toArray());
        $player->getTileArea()->getDeclaredMeldListReference()->setInnerArray($actualDeclareMeldList->toArray());
        $this->setTargetTile(new TargetTile($actualTargetTile));
    }

    function debugSetPrivate(Player $player, TileList $hand, MeldList $declareMeldList = null, Tile $targetTile = null) {
        $this->debugSetHandImpl($player, $hand, $declareMeldList, $targetTile);
    }

    function debugSetPublic(Player $player, TileList $hand, MeldList $declareMeldList = null) {
        $this->debugSetHandImpl($player, $hand, $declareMeldList);
    }

    function debugReplaceHand(Player $player, TileList $replaceTileList) {
        $handReference = $player->getTileArea()->getHandReference();
        $valid = $replaceTileList->count() <= $handReference->count();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $replaceIndexes = range(0, $replaceTileList->count() - 1);
        $newHand = $handReference->toTileList()->replaceByIndex($replaceIndexes, $replaceTileList->toArray());
        $declaredMeldList = $player->getTileArea()->getDeclaredMeldListReference();

        // call to handle targetTile
        $this->debugSetHandImpl($player, $newHand, $declaredMeldList);
    }

    // getter,setter
    /**
     * @return RoundTurn
     */
    protected function getRoundTurn() {
        $f = $this->roundTurnProvider;
        return $f();
    }

    function getAccumulatedReachCount() {
        return $this->accumulatedReachCount;
    }

    function setAccumulatedReachCount($accumulatedReachCount) {
        $this->accumulatedReachCount = $accumulatedReachCount;
    }

    function getWall() {
        return $this->wall;
    }

    function hasTargetTile() {
        return $this->targetTile !== null;
    }

    /**
     * @return TargetTile
     */
    function getTargetTile() {
        if (!$this->hasTargetTile()) {
            throw new \InvalidArgumentException('$targetTile not existed.');
        }
        return $this->targetTile;
    }

    function setTargetTile(TargetTile $targetTile) {
        if ($targetTile === null) {
            throw new \InvalidArgumentException('$targetTile should not be [null]');
        }
        $this->targetTile = $targetTile;
    }

    /**
     * @return OpenHistory
     */
    function getOpenHistory() {
        return $this->openHistory;
    }

    protected function recordOpen($currentTurn, Tile $mySelfWind, Tile $tile) {
        $this->openHistory->record($currentTurn, $mySelfWind, $tile);
    }

    function getDeclareHistory() {
        return $this->declareHistory;
    }

    protected function recordDeclare(Tile $mySelfWind) {
        $this->declareHistory->recordDeclare($this->getRoundTurn()->getGlobalTurn(), $mySelfWind);
    }

    // convert

    /**
     * @param Player $player
     * @return TileList
     */
    function getPublicHand(Player $player) {
        $originHand = $player->getTileArea()->getHandReference()->toTileList();
        return $originHand->isPublicHand() ? $originHand
            : $originHand->removeByValue($this->getTargetTile()->getTile());
    }

    /**
     * @param Player $player
     * @return TileList
     */
    function getPrivateHand(Player $player) {
        $originHand = $player->getTileArea()->getHandReference()->toTileList();
        return $originHand->isPrivateHand() ? $originHand :
            $originHand->push($this->getTargetTile()->getTile());
    }

    /**
     * @param Player $player
     * @return TileList
     */
    function getPrivateFull(Player $player) {
        $privateHand = $this->getPrivateHand($player);
        $declared = $player->getTileArea()->getDeclaredMeldListReference()->toTileList();
        return $privateHand->merge($declared);
    }

    /**
     * @return TileList
     */
    protected function getDiscarded() {
        return $this->playerList->toReducedValue(function (TileList $l, Player $player) {
            return $l->push($player->getTileArea()->getDiscardedReference()->toArray());
        }, TileList::fromString(''));
    }

    // data

    function getOutsideRemainTileAmount(Tile $tile) {
        $total = $this->getWall()->getTileSet()->getEqualValueCount($tile);
        $discarded = $this->getDiscarded()->getEqualValueCount($tile);
        $remain = $total - $discarded;
        return $remain;
    }

    function isFirstTurnWin(Player $targetPlayer) {
        if (!$targetPlayer->getTileArea()->isReach()) {
            return false;
        }

        $targetReachGlobalTurn = $targetPlayer->getTileArea()->getReachGlobalTurn();
        $targetReachRoundTurn = new RoundTurn($targetReachGlobalTurn, $targetPlayer->getSelfWind());

        $currentRoundTurn = $this->getRoundTurn();
        $isSameOrNextGlobalTurn = $currentRoundTurn->getPastFloatGlobalTurn($targetReachRoundTurn) <= 1;

        $fromTurn = $targetReachGlobalTurn;
        $fromWind = $targetPlayer->getSelfWind();
        $noDeclare = !$this->getDeclareHistory()->hasDeclare($fromTurn, $fromWind);

        return $isSameOrNextGlobalTurn && $noDeclare;
    }

    // operation

    function drawInitForAll() {
        // each player draw initial tiles, notice NOT to trigger turn changes by avoid calling PlayerList->toPlayer()
        $drawTileCounts = [4, 4, 4, 1];
        foreach ($drawTileCounts as $drawTileCount) {
            foreach ($this->playerList as $player) {
                $newTiles = $this->getWall()->drawInit($drawTileCount);
                $player->getTileArea()->drawInit($newTiles);
            }
        }
    }

    function draw(Player $player) {
        $newTile = $this->getWall()->draw();
        $player->getTileArea()->draw($newTile);
        $this->setTargetTile(new TargetTile($newTile));
    }

    function drawReplacement(Player $player) {
        $newTile = $this->getWall()->drawReplacement();
        $player->getTileArea()->draw($newTile);
        $this->setTargetTile(new TargetTile($newTile, true));
    }

    function discard(Player $player, Tile $selfTile) {
        $player->getTileArea()->discard($selfTile);
        $this->setTargetTile(new TargetTile($selfTile));

        $this->recordOpen($this->getRoundTurn()->getGlobalTurn(), $player->getSelfWind(), $selfTile);
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

        $notReachYet = !$player->getTileArea()->isReach();
        if (!$notReachYet) { // TileArea
            throw new \InvalidArgumentException('Reach condition violated: not reach yet.');
        }

        $isConcealed = $player->getTileArea()->getDeclaredMeldListReference()->count() == 0;
        if (!$isConcealed) { // TileArea
            throw new \InvalidArgumentException('Reach condition violated: is isConcealed.');
        }

        $enoughScore = $player->getScore() >= 1000;
        if (!$enoughScore) { // TileArea
            throw new \InvalidArgumentException('Reach condition violated: at least 1000 score.');
        }

        $hasDrawTileChance = $this->getWall()->getRemainTileCount() >= 4;
        if (!$hasDrawTileChance) { // TilesArea
            throw new \InvalidArgumentException('Reach condition violated: at least 1 draw tile chance.');
        }

        $player->getTileArea()->reach($selfTile, $this->getRoundTurn()->getGlobalTurn());
        $player->setScore($player->getScore() - 1000);
        $this->setAccumulatedReachCount($this->getAccumulatedReachCount() + 1);

        $this->recordOpen($this->getRoundTurn()->getGlobalTurn(), $player->getSelfWind(), $selfTile); // todo reach flag
    }

    function concealedKong(Player $actPlayer, Tile $selfTile) {
        $actPlayer->getTileArea()->concealedKong($selfTile);
        $this->drawReplacement($actPlayer);

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    function plusKongBefore(Player $actPlayer, Tile $selfTile) {
        $this->setTargetTile(
            new TargetTile($selfTile, false, true)
        );
    }

    function plusKong(Player $actPlayer, Tile $selfTile) {
        $actPlayer->getTileArea()->plusKong($selfTile);
        $this->drawReplacement($actPlayer);

        $this->recordOpen($this->getRoundTurn()->getGlobalTurn(), $actPlayer->getSelfWind(), $selfTile);

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    function chow(Player $actPlayer, Tile $tile1, Tile $tile2, Player $targetPlayer) {
        $this->assertNextPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getTileArea();
        $actPlayerArea = $actPlayer->getTileArea();

        $targetTile = $targetPlayerArea->getDiscardedReference()->getLast(); // test valid
        $actPlayerArea->chowByOther($targetTile, $tile1, $tile2); // test valid
        $targetPlayerArea->getDiscardedReference()->pop();

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    function pong(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getTileArea();
        $actPlayerArea = $actPlayer->getTileArea();

        $targetTile = $targetPlayerArea->getDiscardedReference()->getLast(); // test valid
        $actPlayerArea->pongByOther($targetTile); // test valid
        $targetPlayerArea->getDiscardedReference()->pop();

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    function bigKong(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getTileArea();
        $actPlayerArea = $actPlayer->getTileArea();

        $targetTile = $targetPlayerArea->getDiscardedReference()->getLast(); // test valid
        $actPlayerArea->bigKong($targetTile); // test valid
        $this->drawReplacement($actPlayer);
        $targetPlayerArea->getDiscardedReference()->pop();

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    function smallKong(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $currentPlayerArea = $targetPlayer->getTileArea();
        $playerArea = $actPlayer->getTileArea();

        $targetTile = $currentPlayerArea->getDiscardedReference()->getLast(); // test valid
        $playerArea->smallKong($targetTile);
        $this->drawReplacement($actPlayer);
        $currentPlayerArea->getDiscardedReference()->pop();

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    protected function assertNextPlayer(Player $nextPlayer, Player $prePlayer) {
        list($iNext, $iPre) = $this->playerList->valueToIndex([$nextPlayer, $prePlayer]);
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