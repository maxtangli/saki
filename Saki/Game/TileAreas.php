<?php

namespace Saki\Game;

// operations upon a Wall and 2-4 PlayerArea
use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;

class TileAreas {
    private $wall;
    private $playerList;
    private $accumulatedReachCount; // 積み棒
    private $publicTargetTile;
    private $discardHistory;

    function __construct(Wall $wall, PlayerList $playerList) {
        $this->wall = $wall;
        $this->playerList = $playerList;
        $this->accumulatedReachCount = 0;
        $this->discardHistory = new DiscardHistory();
    }

    function reset() {
        $this->wall->reset(true);
        $this->publicTargetTile = null;
        $this->discardHistory->init();
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

    function hasPublicTargetTile() {
        return $this->publicTargetTile !== null;
    }

    function getPublicTargetTile() {
        if (!$this->hasPublicTargetTile()) {
            throw new \InvalidArgumentException('$publicTargetTile not existed.');
        }
        return $this->publicTargetTile;
    }

    function setPublicTargetTile(Tile $publicTargetTile) {
        if ($publicTargetTile === null) {
            throw new \InvalidArgumentException('$publicTargetTile should not be [null]');
        }
        $this->publicTargetTile = $publicTargetTile;
    }

    function getDiscardHistory() {
        return $this->discardHistory;
    }

    /**
     * @param Player $player
     * @param $includeTargetTile
     * @return TileSortedList
     */
    function toPlayerHandTileList(Player $player, $includeTargetTile) {
        $handTileList = new TileSortedList($player->getPlayerArea()->getHandTileSortedList()->toArray());
        if ($includeTargetTile) {
            if (!$handTileList->validPrivatePhaseCount()) {
                $handTileList->push($this->getPublicTargetTile());
            }
        } else {
            if (!$handTileList->validPublicPhaseCount()) {
                $handTileList->removeByValue($player->getPlayerArea()->getPrivateTargetTile());
            }
        }
        return $handTileList;
    }

    function toPlayerAllTileList(Player $player, $includeTargetTile) {
        $handTileList = $this->toPlayerHandTileList($player, $includeTargetTile);
        $declaredMeldTileList = $player->getPlayerArea()->getDeclaredMeldList()->toSortedTileList();
        $allTileList = $handTileList;
        $allTileList->merge($declaredMeldTileList);
        return $allTileList;
    }

    function toAllPlayersDiscardedTileList() {
        $sortedTileList = new TileSortedList([]);
        foreach ($this->playerList as $player) {
            $sortedTileList->insert($player->getPlayerArea()->getDiscardedTileList()->toArray(), 0);
        }
        return $sortedTileList;
    }

    function getTileRemainAmount(Tile $tile) {
        $total = $this->getWall()->getTileSet()->getValueCount($tile);
        $discarded = $this->toAllPlayersDiscardedTileList()->getValueCount($tile);
        $remain = $total - $discarded;
        return $remain;
    }

    function drawInitForAll() {
        // each player draw initial tiles, notice NOT to trigger turn changes by avoid calling PlayerList->toPlayer()
        $drawTileCounts = [4, 4, 4, 1];
        $players = $this->playerList->toArray();
        foreach ($drawTileCounts as $drawTileCount) {
            foreach($players as $player) {
                $this->drawInit($player, $drawTileCount);
            }
        }
    }

    protected function drawInit(Player $player, $drawTileCount) {
        $player->getPlayerArea()->drawInit($this->getWall()->remainTileListPop($drawTileCount));
    }

    function draw(Player $player) {
        $player->getPlayerArea()->draw($this->getWall()->remainTileListPop());
    }

    function drawReplacement(Player $player) {
        $player->getPlayerArea()->draw($this->getWall()->deadWallShift());
    }

    function discard(Player $player, Tile $selfTile) {
        $player->getPlayerArea()->discard($selfTile);
        $this->setPublicTargetTile($selfTile);

        $this->discardHistory->recordDiscardTile($this->playerList->getGlobalTurn(), $player->getSelfWind(), $selfTile);
    }

    /**
     * WARNING: assume valid condition checked by caller
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

        $notReachYet = !$player->getPlayerArea()->isReach();
        if (!$notReachYet) { // PlayerArea
            throw new \InvalidArgumentException('Reach condition violated: not reach yet.');
        }

        $isConcealed = $player->getPlayerArea()->getDeclaredMeldList()->count() == 0;
        if (!$isConcealed) { // PlayerArea
            throw new \InvalidArgumentException('Reach condition violated: is concealed.');
        }

        $enoughScore = $player->getScore() >= 1000;
        if (!$enoughScore) { // PlayerArea
            throw new \InvalidArgumentException('Reach condition violated: at least 1000 score.');
        }

        $hasDrawTileChance = $this->getWall()->getRemainTileCount() >= 4;
        if (!$hasDrawTileChance) { // TilesArea
            throw new \InvalidArgumentException('Reach condition violated: at least 1 draw tile chance.');
        }

        $player->getPlayerArea()->reach($selfTile, $this->playerList->getGlobalTurn());
        $player->setScore($player->getScore() - 1000);
        $this->setAccumulatedReachCount($this->getAccumulatedReachCount() + 1);
    }

    function kongBySelf(Player $player, Tile $selfTile) {
        $player->getPlayerArea()->kongBySelf($selfTile);
        $this->drawReplacement($player);
    }

    function plusKongBySelf(Player $player, Tile $selfTile) {
        $player->getPlayerArea()->plusKongBySelf($selfTile);
        $this->drawReplacement($player);
    }

    function chowByOther(Player $actPlayer, Tile $tile1, Tile $tile2, Player $targetPlayer) {
        $this->assertNextPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getPlayerArea();
        $actPlayerArea = $actPlayer->getPlayerArea();

        $targetTile = $targetPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $actPlayerArea->chowByOther($targetTile, $tile1, $tile2); // test valid
        $targetPlayerArea->getDiscardedTileList()->pop();
    }

    function pongByOther(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getPlayerArea();
        $actPlayerArea = $actPlayer->getPlayerArea();

        $targetTile = $targetPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $actPlayerArea->pongByOther($targetTile); // test valid
        $targetPlayerArea->getDiscardedTileList()->pop();
    }

    function kongByOther(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getPlayerArea();
        $actPlayerArea = $actPlayer->getPlayerArea();

        $targetTile = $targetPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $actPlayerArea->kongByOther($targetTile); // test valid
        $this->drawReplacement($actPlayer);
        $targetPlayerArea->getDiscardedTileList()->pop();
    }

    function plusKongByOther(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $currentPlayerArea = $targetPlayer->getPlayerArea();
        $playerArea = $actPlayer->getPlayerArea();

        $targetTile = $currentPlayerArea->getDiscardedTileList()->getLast(); // test valid
        $playerArea->plusKongByOther($targetTile);
        $this->drawReplacement($actPlayer);
        $currentPlayerArea->getDiscardedTileList()->pop();
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