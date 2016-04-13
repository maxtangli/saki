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
    private $reachStatus;

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
        $this->reachStatus = ReachStatus::createNotReach();
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
        $this->reachStatus = ReachStatus::createNotReach();
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
        $this->reachStatus = ReachStatus::createNotReach();
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
     * @return ReachStatus
     */
    function getReachStatus() {
        return $this->reachStatus;
    }

    //region operations
    /**
     * @param ReachStatus $reachStatus
     */
    function setReachStatus(ReachStatus $reachStatus) {
        $this->reachStatus = $reachStatus;
    }

    function drawInit(array $tiles) {
        $this->public->insertLast($tiles);
    }

    function draw(Tile $tile) { // todo rename to includes replacement
        // do nothing for public, since $tile will be target tile
    }

    function discard(Tile $selfTile) {
        $this->public->fromSelect($this->getPublicPlusTarget()->remove($selfTile)); // validate
        $this->discardLocked->unlock()->insertLast($selfTile)->lock();
    }

    function removeDiscardLast() { // todo remove? furiten logic right?
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

    /**
     * kongBySelf      hand []        -> declare, handMeld
     * plusKongBySelf  hand 1,declare -> declare, declareMeld + hand1
     * chowByOther     hand [],other  -> declare, handMeld + other
     * pongByOther     hand [],other  -> declare, handMeld + other
     * kongByOther     hand [],other  -> declare, handMeld + other
     * plusKongByOther declare,other  -> declare, declareMeld + other
     */
    function canDeclareMeld(MeldType $targetMeldType, array $handTiles = null, Tile $otherTile = null, Meld $declaredMeld = null) {
        // exist handTiles
        if ($handTiles && !$this->getPublicPlusTarget()->valueExist($handTiles)) {
            return false;
        }

        // exist source meld
        if ($declaredMeld && !$this->declare->valueExist($declaredMeld, function (Meld $a, Meld $b) {
                return $a->equalTo($b, false);
            })
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
            return $fromMeld->canToTargetMeld($waitingTile, $targetMeldType);
        } else {
            return $fromMeld->getMeldType() == $targetMeldType;
        }
    }

    function declareMeld(MeldType $targetMeldType, $targetConcealed = null,
                         array $handTiles = null, Tile $otherTile = null, Meld $declaredMeld = null) {
        if (!$this->canDeclareMeld($targetMeldType, $handTiles, $otherTile, $declaredMeld)) {
            throw new \InvalidArgumentException(
                sprintf('can not declared meld for 
                $targetMeldType[%s],$targetConcealed[%s],
                $handTiles[%s],$otherTile[%s],$declaredMeld[%s],
                $this->public[%s], $this->own[%s]',
                    $targetMeldType, $targetConcealed,
                    implode(',', $handTiles), $otherTile, $declaredMeld,
                    $this->public, $this->getPublicPlusTarget())
            );
        }

        // get target meld
        $fromMeld = $declaredMeld ?? new Meld($handTiles);
        $hasWaitingTile = !($handTiles && !$otherTile && !$declaredMeld);
        if ($hasWaitingTile) {
            $waitingTile = $otherTile ?? $handTiles[0];
            $targetMeld = $fromMeld->toTargetMeld($waitingTile, $targetMeldType, $targetConcealed);
        } else {
            $targetMeld = $fromMeld->toConcealed($targetConcealed);
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
            $this->declare->remove($declaredMeld, function (Meld $a, Meld $b) {
                return $a->equalTo($b, false);
            });
        }

        $this->declare->insertLast($targetMeld);
        return $targetMeld;
    }
    //endregion
}

