<?php

namespace Saki\Game;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\QuadMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\Utils;

/**
 * Provide collaborate operations on 1 Wall and 2-4 Area.
 * @package Saki\Game
 */
class Areas {
    // immutable
    private $playerList;
    private $roundTurnProvider;

    // variable
    private $accumulatedReachCount;

    // round variable
    private $wall;
    private $target;
    private $openHistory;
    private $declareHistory;

    function __construct(Wall $wall, PlayerList $playerList, callable $roundTurnProvider) {
        $this->playerList = $playerList;
        $this->roundTurnProvider = $roundTurnProvider;

        $this->accumulatedReachCount = 0;

        $this->wall = $wall;
        $this->target = Target::createNull();
        $this->openHistory = new OpenHistory();
        $this->declareHistory = new DeclareHistory();

        $e = PlayerWind::createEast();
        foreach ($playerList as $i => $player) {
            /** @var Player $player */
            $player = $player;
            $getTarget = function () use ($player) {
                return $this->getTarget($player);
            };
            $playerWind = $e->toNext($i);
            $area = new Area($getTarget, $playerWind);
            $player->setTileArea($area);
        }
    }

    function reset(PlayerWind $nextDealer) {
        $this->wall->reset(true);
        $this->target = Target::createNull();
        $this->openHistory->reset();
        $this->declareHistory->reset();

        $this->playerList->walk(function (Player $player) use ($nextDealer) {
            $area = $player->getTileArea();
            $area->reset($area->getPlayerWind()->toNextSelf($nextDealer));
        });
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
        $actualDeclare = $declare ?? $player->getTileArea()->getHand()->getDeclare();

        $player->getTileArea()->debugSet($actualPublic, $actualDeclare, $private);
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
        $area = $player->getTileArea();
        $hand = $area->getHand();
        $public = $hand->getPublic()->getCopy()
            ->replaceAt($replaceIndexes, $replace->toArray());
        $declare = $hand->getDeclare();

        $area->debugSet($public, $declare, null);
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

    protected function getTarget(Player $player) {
        $seeTarget = $this->target->exist()
            && $this->target->isOwner($player->getTileArea()->getPlayerWind());
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

    function getDeclareHistory() {
        return $this->declareHistory;
    }

    protected function recordDeclare(PlayerWind $playerWind) {
        $this->declareHistory->recordDeclare($this->getRoundTurn());
    }

    // convert
    // data
    function getOutsideRemainTileAmount(Tile $tile) {
        $allPlayerDiscard = $this->playerList->getAggregated(TileList::fromString(''), function (TileList $l, Player $player) {
            return $l->insertLast($player->getTileArea()->getDiscard()->toArray());
        });

        $totalCount = $this->getWall()->getTileSet()->getCount(Utils::toPredicate($tile));
        $discardCount = $allPlayerDiscard->getCount(Utils::toPredicate($tile));
        $remainCount = $totalCount - $discardCount;
        return max(0, $remainCount); // note: in tests $remainCount may be negative because of mocking.
    }

    function isFirstTurnWin(Player $targetPlayer) {
        $reachStatus = $targetPlayer->getTileArea()->getReachStatus();
        if (!$reachStatus->isReach()) {
            return false;
        }

        $reachRoundTurn = $reachStatus->getReachRoundTurn();
        $reachNextRoundTurn = new RoundTurn(
            $reachRoundTurn->getGlobalTurn() + 1,
            $targetPlayer->getTileArea()->getPlayerWind()
        );
        $currentRoundTurn = $this->getRoundTurn();
        $isSameOrNextGlobalTurn = $currentRoundTurn->isBeforeOrSame($reachNextRoundTurn);

        $noDeclareSinceReach = !$this->getDeclareHistory()->hasDeclare($reachRoundTurn);
        return $isSameOrNextGlobalTurn && $noDeclareSinceReach;
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
                $player->getTileArea()->drawInit($newTiles);
            }
        }

        // no target until now
    }

    function draw(Player $player) {
        $newTile = $this->getWall()->draw();
        $player->getTileArea()->draw($newTile);

        $this->setTargetData(
            new Target($newTile, TargetType::create(TargetType::DRAW), $player->getTileArea()->getPlayerWind())
        );
    }

    function drawReplacement(Player $player) {
        $newTile = $this->getWall()->drawReplacement();
        $player->getTileArea()->draw($newTile);

        $this->setTargetData(
            new Target($newTile, TargetType::create(TargetType::REPLACEMENT), $player->getTileArea()->getPlayerWind())
        );
    }

    function discard(Player $player, Tile $selfTile) {
        $player->getTileArea()->discard($selfTile);

        $this->setTargetData(
            new Target($selfTile, TargetType::create(TargetType::DISCARD), $player->getTileArea()->getPlayerWind())
        );

        $this->openHistory->record(
            new OpenRecord($this->getRoundTurn(), $selfTile, true)
        );
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

        $notReachYet = !$player->getTileArea()->getReachStatus()->isReach();
        if (!$notReachYet) { // Area
            throw new \InvalidArgumentException('Reach condition violated: not reach yet.');
        }

        $isConcealed = $player->getTileArea()->getHand()->getDeclare()->count() == 0;
        if (!$isConcealed) { // Area
            throw new \InvalidArgumentException('Reach condition violated: is isConcealed.');
        }

        $enoughScore = $player->getScore() >= 1000;
        if (!$enoughScore) { // Area
            throw new \InvalidArgumentException('Reach condition violated: at least 1000 score.');
        }

        $hasDrawTileChance = $this->getWall()->getRemainTileCount() >= 4;
        if (!$hasDrawTileChance) { // TilesArea
            throw new \InvalidArgumentException('Reach condition violated: at least 1 draw tile chance.');
        }

        $player->getTileArea()->discard($selfTile);

        $this->setTargetData(
            new Target($selfTile, TargetType::create(TargetType::DISCARD), $player->getTileArea()->getPlayerWind())
        );

        $player->getTileArea()->setReachStatus(
            new ReachStatus($this->getRoundTurn())
        );

        $player->setScore($player->getScore() - 1000);
        $this->setAccumulatedReachCount($this->getAccumulatedReachCount() + 1);

        $this->openHistory->record(
            new OpenRecord($this->getRoundTurn(), $selfTile, true)
        );
    }

    function concealedKong(Player $actPlayer, Tile $selfTile) {
        $handTiles = [$selfTile, $selfTile, $selfTile, $selfTile];
        $actPlayer->getTileArea()->declareMeld(QuadMeldType::create(), true, $handTiles, null, null);

        $this->drawReplacement($actPlayer); // set target

        $this->recordDeclare($actPlayer->getTileArea()->getPlayerWind());
    }

    function plusKongBefore(Player $actPlayer, Tile $selfTile) {
        $target = $actPlayer->getTileArea()->tempGenKongTargetData($selfTile);

        $this->setTargetData($target);
    }

    function plusKongAfter(Player $actPlayer, Tile $plusKongBeforeTile) { // todo remove $plusKongBeforeTile
        $this->setTargetData(
            $this->target->toSetValue(null, TargetType::create(TargetType::KEEP))
        ); // todo belong to where: here? plusKongCommand? PublicPhaseState?

        $declaredMeld = new Meld([$plusKongBeforeTile, $plusKongBeforeTile, $plusKongBeforeTile]);
        $actPlayer->getTileArea()->declareMeld(QuadMeldType::create(), null, null, $plusKongBeforeTile, $declaredMeld);

        $this->drawReplacement($actPlayer); // set target

        $this->openHistory->record(
            new OpenRecord($this->getRoundTurn(), $plusKongBeforeTile, false)
        );

        $this->recordDeclare($actPlayer->getTileArea()->getPlayerWind());
    }

    function chow(Player $actPlayer, Tile $selfTile1, Tile $selfTile2, Player $targetPlayer) {
        $this->assertNextPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getTileArea();
        $actPlayerArea = $actPlayer->getTileArea();

        $handTiles = [$selfTile1, $selfTile2];
        $targetTile = $targetPlayerArea->getDiscard()->getLast(); // validate
        $actPlayerArea->declareMeld(RunMeldType::create(), false, $handTiles, $targetTile, null); // validate
        $targetPlayerArea->removeDiscardLast();

        $keepTargetData = $actPlayerArea->tempGenKeepTargetData();
        $this->setTargetData($keepTargetData);

        $this->recordDeclare($actPlayer->getTileArea()->getPlayerWind());
    }

    function pong(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getTileArea();
        $actPlayerArea = $actPlayer->getTileArea();

        $targetTile = $targetPlayerArea->getDiscard()->getLast(); // validate
        $handTiles = [$targetTile, $targetTile];
        $actPlayerArea->declareMeld(TripleMeldType::create(), false, $handTiles, $targetTile, null); // validate
        $targetPlayerArea->removeDiscardLast();

        $keepTargetData = $actPlayerArea->tempGenKeepTargetData();
        $this->setTargetData($keepTargetData);

        $this->recordDeclare($actPlayer->getTileArea()->getPlayerWind());
    }

    function bigKong(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $targetPlayerArea = $targetPlayer->getTileArea();
        $actPlayerArea = $actPlayer->getTileArea();

        $targetTile = $targetPlayerArea->getDiscard()->getLast(); // validate
        $handTiles = [$targetTile, $targetTile, $targetTile];
        $actPlayerArea->declareMeld(QuadMeldType::create(), false, $handTiles, $targetTile, null); // validate
        $targetPlayerArea->removeDiscardLast();

        $this->drawReplacement($actPlayer); // set target

        $this->recordDeclare($actPlayer->getTileArea()->getPlayerWind());
    }

    function smallKong(Player $actPlayer, Player $targetPlayer) {
        $this->assertDifferentPlayer($actPlayer, $targetPlayer);
        $currentPlayerArea = $targetPlayer->getTileArea();
        $playerArea = $actPlayer->getTileArea();

        $targetTile = $currentPlayerArea->getDiscard()->getLast(); // validate
        $declaredMeld = new Meld([$targetTile, $targetTile, $targetTile]);
        $playerArea->declareMeld(QuadMeldType::create(), false, null, $targetTile, $declaredMeld); // validate
        $currentPlayerArea->removeDiscardLast();

        $this->drawReplacement($actPlayer); // set target

        $this->recordDeclare($actPlayer->getTileArea()->getPlayerWind());
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