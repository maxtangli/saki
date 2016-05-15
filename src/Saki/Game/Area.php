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
use Saki\Util\Utils;

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
    private $melded;
    private $riichiStatus;

    function __construct(Player $player, Areas $areas) {
        // immutable
        $this->player = $player;
        // shared variable
        $this->areas = $areas;
        // new round variable
        $this->handHolder = new HandHolder($areas->getTargetHolder(), $player->getInitialSeatWind());
        $this->public = new TileList();
        $this->melded = new MeldList();
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
        $this->melded->removeAll();
        $this->riichiStatus = RiichiStatus::createNotRiichi();
    }

    function debugSet(TileList $public, MeldList $declare = null, Tile $targetTile = null) {
        $newHand = $this->getHand()->toHand($public, $declare, $targetTile);
        $this->setHand($newHand);
    }

    function debugMockHand(TileList $replace) {
        $newHand = $this->getHand()->toMockHand($replace);
        $this->setHand($newHand);
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
    function getAreas() {
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
     * @return Hand
     */
    function getHand() {
        $target = $this->getAreas()->getTargetHolder()
            ->getTarget($this->getSeatWind());
        return new Hand(
            $this->public,
            $this->melded,
            $target
        );
    }

    /**
     * @param Hand $hand
     */
    function setHand(Hand $hand) {
        $this->public->fromSelect($hand->getPublic());
        $this->melded->fromSelect($hand->getMelded());
        if ($hand->getTarget()->exist()) {
            $this->getAreas()->getTargetHolder()
                ->setTarget($hand->getTarget());
        }
    }

    /**
     * @return TileList
     */
    function getDiscard() {
        return $this->getAreas()->getOpenHistory()
            ->getSelfDiscard($this->getSeatWind());
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
    function draw() {
        $hand = $this->getHand();

        $newPublic = $hand->getPublic();
        $newMelded = $hand->getMelded();
        
        $newTile = $this->getAreas()->getWall()
            ->draw();
        $newTarget = new Target($newTile, TargetType::create(TargetType::DRAW), $this->getSeatWind());
        
        $newHand = new Hand($newPublic, $newMelded, $newTarget);
        $this->setHand($newHand);
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

        $isConcealed = $this->getHand()->getMelded()->count() == 0;
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

        $this->setRiichiStatus(
            new RiichiStatus($this->getAreas()->getTurn())
        );
        $this->setPoint($this->getPoint() - 1000);
        $this->getAreas()->setRiichiPoints($this->getAreas()->getRiichiPoints() + 1000);

        $open = new Open($this->getSeatWind(), $selfTile, true);
        $open->apply($this);
    }

    function extendKongAfter() {
        // todo better way?
        $targetTile = $this->getAreas()->getOpenHistory()
            ->getSelfOpen($this->getSeatWind())
            ->getLast();
        $newTarget = new Target($targetTile, TargetType::create(TargetType::KEEP), $this->getSeatWind());
        $this->getAreas()->getTargetHolder()
            ->setTarget($newTarget);

        $fromMeldedTiles = [$targetTile, $targetTile, $targetTile];
        $isFromMelded = function (Meld $meld) use ($fromMeldedTiles) {
            return $meld->toArray() == $fromMeldedTiles;
        };
        $fromMelded = $this->getHand()->getMelded()
            ->getSingle($isFromMelded); // keep concealedFlag
        $claim = Claim::createFromMelded($this->getSeatWind(), $this->getAreas()->getTurn(),
            $targetTile, $fromMelded);

        $claim->apply($this);
    }
    //endregion
}