<?php
namespace Saki\Game;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\MeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

/**
 * A roundly reset area own by a player.
 * @package Saki\Game
 */
class Area {
    // immutable
    private $player;
    // variable
    private $targetHolder;
    // game variable
    private $seatWind;
    private $seatWindTurn;
    private $point;
    // round variable
    private $public;
    private $discardLocked; // locked to support safe pass by reference
    private $declare;
    private $riichiStatus;

    /**
     * @param TargetHolder $targetHolder
     * @param Player $player
     */
    function __construct(TargetHolder $targetHolder, Player $player) {
        // immutable
        $this->player = $player;
        // variable
        $this->targetHolder = $targetHolder;
        // game variable
        $this->seatWind = $player->getInitialSeatWind();
        $this->seatWindTurn = 0;
        $this->point = $player->getInitialPoint();
        // round variable
        $this->public = new TileList();
        $this->discardLocked = (new TileList())->lock();
        $this->declare = new MeldList();
        $this->riichiStatus = RiichiStatus::createNotRiichi();
    }

    /**
     * @param SeatWind $seatWind
     */
    function roll(SeatWind $seatWind) {
        // game variable
        $keepDealer = $this->seatWind->isDealer() && $seatWind->isDealer();
        $this->seatWind = $seatWind;
        $this->seatWindTurn = $keepDealer ? $this->seatWindTurn + 1 : 0;
        // $this->point not change
        // round variable
        $this->public->removeAll();
        $this->discardLocked->unlock()->removeAll()->lock();
        $this->declare->removeAll();
        $this->riichiStatus = RiichiStatus::createNotRiichi();
    }

    function debugInit(SeatWind $seatWind) {
        // game variable
        $this->seatWind = $seatWind;
        $this->seatWindTurn = 0;
        $this->point = $this->getPlayer()->getInitialPoint();
        // round variable
        $this->public->removeAll();
        $this->discardLocked->unlock()->removeAll()->lock();
        $this->declare->removeAll();
        $this->riichiStatus = RiichiStatus::createNotRiichi();
    }

    /**
     * @param TileList $public
     * @param MeldList $declare
     */
    function debugSet(TileList $public, MeldList $declare) {
        $valid = (new Hand($public, $declare, $this->getTarget()))
            ->isPublicPlusDeclareComplete();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->public->fromSelect($public);
        $this->declare->fromSelect($declare);
    }

    /**
     * @return Player
     */
    function getPlayer() {
        return $this->player;
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
        $target = $this->targetHolder->getTarget($this->getSeatWind());
        
        if ($target->exist() && !$target->isOwner($this->getSeatWind())) {
            throw new \LogicException();
        }

        return $target;
    }

    /**
     * @return TileList
     */
    protected function getPublicPlusTarget() {
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
        return $this->discardLocked;
    }

    /**
     * @return RiichiStatus
     */
    function getRiichiStatus() {
        return $this->riichiStatus;
    }

    //region operations
    /**
     * @param RiichiStatus $riichiStatus
     */
    function setRiichiStatus(RiichiStatus $riichiStatus) {
        $this->riichiStatus = $riichiStatus;
    }

    function drawInit(array $tiles) {
        $this->public->insertLast($tiles);
    }

    function drawOrReplace(Tile $tile) {
        // public no change since $tile will be target tile
    }

    function discard(Tile $selfTile) {
        $this->public->fromSelect($this->getPublicPlusTarget()->remove($selfTile)); // validate
        $this->discardLocked->unlock()->insertLast($selfTile)->lock();
    }

    function removeDiscardLast() {
        $this->discardLocked->unlock()->removeLast()->lock();
    }

    function tempGenKeepTarget() {
        $lastTile = $this->public->getLast(); // validate
        $this->public->remove($lastTile);
        return new Target(
            $lastTile, TargetType::create(TargetType::KEEP), $this->getSeatWind()
        );
    }

    function tempGenKongTarget(Tile $kongSelfTile) {
        $this->public->fromSelect($this->getPublicPlusTarget()->remove($kongSelfTile)); // validate
        return new Target(
            $kongSelfTile, TargetType::create(TargetType::KONG), $this->getSeatWind()
        );
    }
    
    function claim(MeldType $toMeldType, $toConcealed = null,
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