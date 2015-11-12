<?php

namespace Saki\Game;

// operations upon a Wall and 2-4 TileArea
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Game\RoundTurn;

class TileAreas {
    // immutable
    private $playerList;
    private $roundTurnProvider;

    // immutable for each round, except for client setter calls
    private $accumulatedReachCount; // 積み棒

    // variable for each round
    private $wall;
    private $targetTile;
    private $discardHistory;
    private $declareHistory;

    function __construct(Wall $wall, PlayerList $playerList, callable $roundTurnProvider) {
        $this->playerList = $playerList;
        $this->roundTurnProvider = $roundTurnProvider;

        $this->accumulatedReachCount = 0;

        $this->wall = $wall;
        $this->targetTile = null;
        $this->discardHistory = new DiscardHistory();
        $this->declareHistory = new DeclareHistory();
    }

    function reset() {
        $this->wall->reset(true);
        $this->targetTile = null;
        $this->discardHistory->reset();
        $this->declareHistory->reset();
    }

    function debugSet(Player $player, TileList $tileList, MeldList $declareMeldList = null, Tile $targetTile = null) {
        $actualDeclareMeldList = $declareMeldList ? : MeldList::fromString('');
        if ($tileList->isPrivateHandCount()) {
            $actualTargetTile = $targetTile ? : $tileList[0];
        } elseif ($tileList->isPublicHandCount() && $targetTile) {
            $actualTargetTile = $targetTile;
        } else {
            throw new \InvalidArgumentException(
                sprintf('Invalid $tileList[%s], $targetTile[%s]', $tileList, $targetTile)
            );
        }

        $tileArea = $player->getTileArea();
        $tileArea->getHandTileSortedList()->setInnerArray($tileList->toArray());
        $tileArea->getDeclaredMeldList()->setInnerArray($actualDeclareMeldList->toArray());
        $this->setTargetTile($actualTargetTile);
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

    function getTargetTile() {
        if (!$this->hasTargetTile()) {
            throw new \InvalidArgumentException('$publicTargetTile not existed.');
        }
        return $this->targetTile;
    }

    function setTargetTile(Tile $targetTile) {
        if ($targetTile === null) {
            throw new \InvalidArgumentException('$publicTargetTile should not be [null]');
        }
        $this->targetTile = $targetTile;
    }

    function getDiscardHistory() {
        return $this->discardHistory;
    }

    protected function recordDiscard($currentTurn, Tile $mySelfWind, Tile $tile) {
        $this->discardHistory->recordDiscardTile($currentTurn, $mySelfWind, $tile);
    }

    function getDeclareHistory() {
        return $this->declareHistory;
    }

    protected function recordDeclare(Tile $mySelfWind) {
        $this->declareHistory->recordDeclare($this->getRoundTurn()->getGlobalTurn(), $mySelfWind);
    }

    // convert

    function toPlayerHandTileList(Player $player, $isPrivate) {
        $originHandTileList = $player->getTileArea()->getHandTileSortedList();
        $targetTile = $this->getTargetTile();
        return $originHandTileList->toHandTileSortedList($isPrivate, $targetTile);
    }

    function toPlayerAllTileList(Player $player, $isPrivate) {
        $allTileList = $this->toPlayerHandTileList($player, $isPrivate);
        $declaredMeldTileList = $player->getTileArea()->getDeclaredMeldList()->toSortedTileList();
        $allTileList->merge($declaredMeldTileList);
        return $allTileList;
    }

    function toAllPlayersDiscardedTileList() {
        $sortedTileList = new TileSortedList([]);
        foreach ($this->playerList as $player) {
            $sortedTileList->insert($player->getTileArea()->getDiscardedTileList()->toArray(), 0);
        }
        return $sortedTileList;
    }

    // data

    function getOutsideRemainTileAmount(Tile $tile) {
        $total = $this->getWall()->getTileSet()->getEqualValueCount($tile);
        $discarded = $this->toAllPlayersDiscardedTileList()->getEqualValueCount($tile);
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
        $this->setTargetTile($newTile);
    }

    function drawReplacement(Player $player) {
        $newTile = $this->getWall()->drawReplacement();
        $player->getTileArea()->draw($newTile);
        $this->setTargetTile($newTile);
    }

    function discard(Player $player, Tile $selfTile) {
        $player->getTileArea()->discard($selfTile);
        $this->setTargetTile($selfTile);

        $this->recordDiscard($this->getRoundTurn()->getGlobalTurn(), $player->getSelfWind(), $selfTile);
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

        $isConcealed = $player->getTileArea()->getDeclaredMeldList()->count() == 0;
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

        $this->recordDiscard($this->getRoundTurn()->getGlobalTurn(), $player->getSelfWind(), $selfTile); // todo reach flag
    }

    function kongBySelf(Player $actPlayer, Tile $selfTile) {
        $actPlayer->getTileArea()->kongBySelf($selfTile);
        $this->drawReplacement($actPlayer);

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    function plusKongBySelf(Player $actPlayer, Tile $selfTile) {
        $actPlayer->getTileArea()->plusKongBySelf($selfTile);
        $this->drawReplacement($actPlayer);

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    function chowByOther(Player $actPlayer, Tile $tile1, Tile $tile2, Player $targetPlayer) {
        $this->assertNextPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getTileArea();
        $actPlayerArea = $actPlayer->getTileArea();

        $targetTile = $targetPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $actPlayerArea->chowByOther($targetTile, $tile1, $tile2); // test valid
        $targetPlayerArea->getDiscardedTileList()->pop();

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    function pongByOther(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getTileArea();
        $actPlayerArea = $actPlayer->getTileArea();

        $targetTile = $targetPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $actPlayerArea->pongByOther($targetTile); // test valid
        $targetPlayerArea->getDiscardedTileList()->pop();

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    function kongByOther(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getTileArea();
        $actPlayerArea = $actPlayer->getTileArea();

        $targetTile = $targetPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $actPlayerArea->kongByOther($targetTile); // test valid
        $this->drawReplacement($actPlayer);
        $targetPlayerArea->getDiscardedTileList()->pop();

        $this->recordDeclare($actPlayer->getSelfWind());
    }

    function plusKongByOther(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $currentPlayerArea = $targetPlayer->getTileArea();
        $playerArea = $actPlayer->getTileArea();

        $targetTile = $currentPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $playerArea->plusKongByOther($targetTile);
        $this->drawReplacement($actPlayer);
        $currentPlayerArea->getDiscardedTileList()->pop();

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