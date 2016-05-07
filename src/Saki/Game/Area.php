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
    private $getTarget;
    private $player;
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
     * @param callable $getTarget
     * @param Player $player
     */
    function __construct(callable $getTarget, Player $player) {
        // immutable
        $this->getTarget = $getTarget;
        $this->player = $player;
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
        $f = $this->getTarget;
        /** @var Target $target */
        $target = $f($this->getSeatWind());

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

    function canDeclareMeld(MeldType $toMeldType,
                            array $handTiles = null, Tile $otherTile = null, Meld $declaredMeld = null) {
        // exist handTiles
        if ($handTiles && !$this->getPublicPlusTarget()->valueExist($handTiles)) {
            return false;
        }

        // exist from meld
        if ($declaredMeld && !$this->declare->valueExist($declaredMeld, Meld::getEqual(false))
        ) {
            return false;
        }
        
        // exist tiles can form a fromMeld
        try {
            $fromMeld = $declaredMeld ?? new Meld($handTiles);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        // fromMeld can to target meld
        $hasWaitingTile = !($handTiles && !$otherTile && !$declaredMeld);
        if ($hasWaitingTile) {
            $waitingTile = $otherTile ?? $handTiles[0];
            return $fromMeld->canToTargetMeld($waitingTile, $toMeldType);
        } else {
            return $fromMeld->getMeldType() == $toMeldType;
        }
    }

    function declareMeld(MeldType $toMeldType, $toConcealed = null,
                         array $handTiles = null, Tile $otherTile = null, Meld $declaredMeld = null) {
        if (!$this->canDeclareMeld($toMeldType, $handTiles, $otherTile, $declaredMeld)) {
            throw new \InvalidArgumentException(
                sprintf('can not declared meld for 
                $targetMeldType[%s],$targetConcealed[%s],
                $handTiles[%s],$otherTile[%s],$declaredMeld[%s],
                $this->public[%s], $this->own[%s]',
                    $toMeldType, $toConcealed,
                    implode(',', $handTiles), $otherTile, $declaredMeld,
                    $this->public, $this->getPublicPlusTarget())
            );
        }

        // get target meld
        $fromMeld = $declaredMeld ?? new Meld($handTiles);
        $hasWaitingTile = !($handTiles && !$otherTile && !$declaredMeld);
        if ($hasWaitingTile) {
            $waitingTile = $otherTile ?? $handTiles[0];
            $targetMeld = $fromMeld->toTargetMeld($waitingTile, $toMeldType, $toConcealed);
        } else {
            $targetMeld = $fromMeld->toConcealed($toConcealed);
        }

        // remove origin tiles and meld, add new meld
        if ($handTiles) {
            $this->public->fromSelect($this->getPublicPlusTarget()->remove($handTiles));
            if ($otherTile) {
                $this->public->remove($otherTile); // todo other tile must be target tile
            }
        }

        if ($declaredMeld) {
            // remove meld ignoring concealed todo right?
            $this->declare->remove($declaredMeld, Meld::getEqual(false));
        }

        $this->declare->insertLast($targetMeld);
        return $targetMeld;
    }
    //endregion
}

/**
 * chow          hand [],other  -> declare, handMeld + other
 * pung          hand [],other  -> declare, handMeld + other
 * kong       hand [],other  -> declare, handMeld + other
 * concealedKong hand []        -> declare, handMeld
 * extendPung    hand 1,declare -> declare, declareMeld + hand1
 * @package Saki\Game
 */
class Claim {
    private $toMeldType;
    private $toConcealed;
    private $fromTiles;
    private $fromRequireTarget;
    private $fromMeld;

    /**
     * @param $toMeldType
     * @param $toConcealed
     * @param $fromTiles
     * @param $requireTarget
     * @param $fromMeld
     */
    function __construct(MeldType $toMeldType, bool $toConcealed
        , array $fromTiles, bool $requireTarget, Meld $fromMeld = null) {
        $this->toMeldType = $toMeldType;
        $this->toConcealed = $toConcealed;
        $this->fromTiles = $fromTiles;
        $this->fromRequireTarget = $requireTarget;
        $this->fromMeld = $fromMeld;
    }

    function valid(Hand $hand) {
    }

    function toClaimed(Hand $hand) {
    }
}