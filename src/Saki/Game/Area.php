<?php
namespace Saki\Game;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\MeldType;
use Saki\Meld\QuadMeldType;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

/**
 * A roundly reset area own by a player.
 * @package Saki\Game
 */
class Area {
    // immutable
    private $player;
    // shared variable
    private $areas;
    // game variable
    private $seatWind;
    private $seatWindTurn;
    private $point;
    // round variable
    private $handHolder;
    private $public;
    private $declare;
    private $riichiStatus;

    function __construct(Player $player, Areas $areas) {
        // immutable
        $this->player = $player;
        // shared variable
        $this->areas = $areas;
        // new round variable
        $this->handHolder = new HandHolder($areas->getTargetHolder(), $player->getInitialSeatWind());
        $this->public = new TileList();
        $this->declare = new MeldList();
        $this->riichiStatus = RiichiStatus::createNotRiichi();
        // game variable, round variable
        $this->resetImpl($player->getInitialSeatWind(), 0, $player->getInitialPoint());
    }

    /**
     * @param SeatWind $seatWind
     */
    function roll(SeatWind $seatWind) {
        $keepDealer = $this->seatWind->isDealer() && $seatWind->isDealer();
        $seatWindTurn = $keepDealer ? $this->seatWindTurn + 1 : 0;
        $this->resetImpl($seatWind, $seatWindTurn, $this->point);
    }

    /**
     * @param SeatWind $seatWind
     */
    function debugInit(SeatWind $seatWind) {
        $this->resetImpl($seatWind, 0, $this->getPlayer()->getInitialPoint());
    }

    /**
     * @param SeatWind $seatWind
     * @param int $seatWindTurn
     * @param int $point
     */
    protected function resetImpl(SeatWind $seatWind, int $seatWindTurn, int $point) {
        // game variable
        $this->seatWind = $seatWind;
        $this->seatWindTurn = $seatWindTurn;
        $this->point = $point;
        // round variable
        $this->handHolder->init();
        $this->public->removeAll();
        $this->declare->removeAll();
        $this->riichiStatus = RiichiStatus::createNotRiichi();
    }

    function debugSet(TileList $public, MeldList $declare = null, Tile $targetTile = null) {
        $new = $this->getHand()->toHand($public, $declare, $targetTile);

        $this->public->fromSelect($new->getPublic());
        $this->declare->fromSelect($new->getDeclare());
        $newTarget = $new->getTarget();
        if ($newTarget->exist()) {
            $this->getAreas()->getTargetHolder()
                ->replaceTarget($this->getSeatWind(), $newTarget->getTile());
        }
    }

    function debugMockHand(TileList $replace) {
        $new = $this->getHand()->toMockHand($replace);

        $this->public->fromSelect($new->getPublic());
        $this->declare->fromSelect($new->getDeclare());
        $newTarget = $new->getTarget();
        if ($newTarget->exist()) {
            $this->getAreas()->getTargetHolder()
                ->replaceTarget($this->getSeatWind(), $newTarget->getTile());
        }
    }

    /**
     * @return Player
     */
    function getPlayer() {
        return $this->player;
    }

    /**
     * @return Areas
     */
    protected function getAreas() {
        return $this->areas;
    }

    /**
     * @return SeatWind
     */
    function getSeatWind() {
        return $this->seatWind;
    }

    /**
     * @return int
     */
    function getSeatWindTurn() {
        if (!$this->getSeatWind()->isDealer()) {
            throw new \BadMethodCallException();
        }
        return $this->seatWindTurn;
    }

    /**
     * @return int
     */
    function getPoint() {
        return $this->point;
    }

    /**
     * @param int $point
     */
    function setPoint(int $point) {
        $this->point = $point;
    }

    /**
     * @return Target A Target own by this Area's SeatWind.
     */
    protected function getTarget() {
        return $this->getAreas()->getTargetHolder()
            ->getTarget($this->getSeatWind());
    }

    /**
     * @return TileList
     */
    protected function temp_getPublicPlusTarget() {
        return $this->getHand()->getPublicPlusTarget()->getCopy();
    }

    /**
     * @return Hand
     */
    function getHand() {
        return new Hand(
            $this->public,
            $this->declare,
            $this->getTarget()
        );
    }

    /**
     * @return TileList
     */
    function getDiscard() {
        return $this->getAreas()->getOpenHistory()
            ->getSelfDiscard($this->seatWind);
    }

    /**
     * @return RiichiStatus
     */
    function getRiichiStatus() {
        return $this->riichiStatus;
    }

    /**
     * @param RiichiStatus $riichiStatus
     */
    function setRiichiStatus(RiichiStatus $riichiStatus) {
        $this->riichiStatus = $riichiStatus;
    }

    //region operations
    function deal(array $tiles) {
        $this->public->fromArray($tiles);
    }

    function draw() {
        $newTile = $this->getAreas()->getWall()
            ->draw();

        $newTarget = new Target($newTile, TargetType::create(TargetType::DRAW), $this->getSeatWind());
        $this->getAreas()->getTargetHolder()
            ->setTarget($newTarget);
    }

    function drawReplacement() {
        $newTile = $this->getAreas()->getWall()
            ->drawReplacement();

        $newTarget = new Target($newTile, TargetType::create(TargetType::REPLACE), $this->getSeatWind());
        $this->getAreas()->getTargetHolder()
            ->setTarget($newTarget);
    }

    function discard(Tile $selfTile) {
        $newPublic = $this->getHand()->getPublicPlusTarget()->getCopy()
            ->remove($selfTile); // validate
        $this->public->fromSelect($newPublic);

        $newTarget = new Target($selfTile, TargetType::create(TargetType::DISCARD), $this->getSeatWind());
        $this->getAreas()->getTargetHolder()
            ->setTarget($newTarget);

        $this->getAreas()->recordOpen($selfTile, true);
    }

    protected function assertValidRiichi(Tile $selfTile) {
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
        $tileExist = $this->getHand()->getPrivate()
            ->valueExist($selfTile);
        if (!$tileExist) {
            throw new \InvalidArgumentException();
        }

        $notRiichiYet = !$this->getRiichiStatus()->isRiichi();
        if (!$notRiichiYet) { // Area
            throw new \InvalidArgumentException('Riichi condition violated: not reach yet.');
        }

        $isConcealed = $this->getHand()->getDeclare()->count() == 0;
        if (!$isConcealed) { // Area
            throw new \InvalidArgumentException('Riichi condition violated: is isConcealed.');
        }

        $enoughPoint = $this->getPoint() >= 1000;
        if (!$enoughPoint) { // Area
            throw new \InvalidArgumentException('Riichi condition violated: at least 1000 point.');
        }

        $hasDrawTileChance = $this->getAreas()->getWall()
                ->getRemainTileCount() >= 4;
        if (!$hasDrawTileChance) { // TilesArea
            throw new \InvalidArgumentException('Riichi condition violated: at least 1 draw tile chance.');
        }
    }

    /**
     * WARNING: assume some conditions validated by caller
     * @param Tile $selfTile
     */
    function riichi(Tile $selfTile) {
        $this->assertValidRiichi($selfTile);

        $this->discard($selfTile);

        $this->setRiichiStatus(
            new RiichiStatus($this->getAreas()->getTurn())
        );
        $this->setPoint($this->getPoint() - 1000);
        $this->getAreas()->setRiichiPoints($this->getAreas()->getRiichiPoints() + 1000);

        $this->getAreas()->recordOpen($selfTile, true);
    }

    function concealedKong(Tile $selfTile) {
        $handTiles = [$selfTile, $selfTile, $selfTile, $selfTile];
        $this->claim(QuadMeldType::create(), true, $handTiles, null, null);

        $this->drawReplacement();

        $this->getAreas()->recordDeclare();
    }

    function extendKongBefore(Tile $selfTile) {
        $newPublic = $this->getHand()->getPublicPlusTarget()->getCopy()
            ->remove($selfTile); // validate
        $this->public->fromSelect($newPublic);

        $newTarget = new Target($selfTile, TargetType::create(TargetType::KONG), $this->getSeatWind());
        $this->getAreas()->getTargetHolder()
            ->setTarget($newTarget);
    }

    function extendKongAfter() {
        $this->getAreas()->getTargetHolder()
            ->setKongToKeep();

        $targetTile = $this->getHand()->getTarget()->getTile();
        $declaredMeld = new Meld([$targetTile, $targetTile, $targetTile]);
        $this->claim(QuadMeldType::create(), null, null, $targetTile, $declaredMeld);

        $this->drawReplacement();

        $this->getAreas()->recordOpen($targetTile, false);
        $this->getAreas()->recordDeclare();
    }

    function chow(Tile $selfTile1, Tile $selfTile2) {
        $targetTile = $this->getHand()->getTarget()->getTile(); // validate
        $handTiles = [$selfTile1, $selfTile2];
        $this->claim(RunMeldType::create(), false, $handTiles, $targetTile, null); // validate

        $newTarget = $this->genKeepTarget();
        $this->getAreas()->getTargetHolder()
            ->setTarget($newTarget);

        $this->getAreas()->getOpenHistory()->setLastDiscardDeclared();
        $this->getAreas()->recordDeclare();
    }

    function pung() {
        $targetTile = $this->getHand()->getTarget()->getTile(); // validate
        $handTiles = [$targetTile, $targetTile];
        $this->claim(TripleMeldType::create(), false, $handTiles, $targetTile, null); // validate

        $newTarget = $this->genKeepTarget();
        $this->getAreas()->getTargetHolder()
            ->setTarget($newTarget);

        $this->getAreas()->getOpenHistory()->setLastDiscardDeclared();
        $this->getAreas()->recordDeclare();
    }

    function kong() {
        $targetTile = $this->getHand()->getTarget()->getTile();
        $handTiles = [$targetTile, $targetTile, $targetTile];
        $this->claim(QuadMeldType::create(), false, $handTiles, $targetTile, null); // validate

        $this->drawReplacement();

        $this->getAreas()->getOpenHistory()->setLastDiscardDeclared();
        $this->getAreas()->recordDeclare();
    }

    protected function genKeepTarget() {
        $lastTile = $this->public->getLast(); // validate
        $this->public->removeLast();
        return new Target(
            $lastTile, TargetType::create(TargetType::KEEP), $this->getSeatWind()
        );
    }

    // todo $otherTile is always TargetTile
    protected function claim(MeldType $toMeldType, $toConcealed = null,
                             array $handTiles = null, Tile $otherTile = null, Meld $declaredMeld = null) {
        $claim = new Claim($toMeldType, $toConcealed, $handTiles, $otherTile, $declaredMeld);

        $fromPublicPlusTarget = $this->getHand()->getPublicPlusTarget();
        $fromDeclare = $this->getHand()->getDeclare();

        if (!$claim->valid($fromPublicPlusTarget, $fromDeclare)) {
            throw new \InvalidArgumentException();
        }

        $toPublic = $claim->getToPublic($fromPublicPlusTarget);
        $toDeclare = $claim->getToDeclare($fromDeclare);

        $this->public->fromSelect($toPublic);
        $this->declare->fromSelect($toDeclare);

        return $claim->getToMeld();
    }
    //endregion
}