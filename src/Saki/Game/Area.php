<?php
namespace Saki\Game;

use Saki\Hand\Hand;
use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\MeldType;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

/**
 * A roundly reset area for a player.
 * @package Saki\Game
 */
class Area {
    // game variable
    private $playerWind;

    // round variable
    private $public;
    private $discardLocked; // locked to support safe pass by reference
    private $declaredMeldList;
    private $reachStatus;

    // immutable
    private $getTarget;

    /**
     * @param callable $getTarget
     * @param PlayerWind $playerWind
     */
    function __construct(callable $getTarget, PlayerWind $playerWind) {
        $this->playerWind = $playerWind;

        $this->public = new TileList();
        $this->discardLocked = (new TileList())->lock();
        $this->declaredMeldList = new MeldList();
        $this->reachStatus = ReachStatus::createNotReach();

        $this->getTarget = $getTarget;
    }

    /**
     * @param PlayerWind $playerWind
     */
    function reset(PlayerWind $playerWind) {
        $this->playerWind = $playerWind;

        $this->public->removeAll();
        $this->discardLocked->unlock()->removeAll()->lock();
        $this->declaredMeldList->removeAll();
        $this->reachStatus = ReachStatus::createNotReach();
    }

    /**
     * @param TileList $public
     * @param MeldList $declare
     * @param TileList $tempHandTileList
     */
    function debugSet(TileList $public, MeldList $declare, TileList $tempHandTileList = null) {
        $valid = (new Hand($public, $declare, $this->getTarget()))
            ->isPublicPlusDeclareComplete();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->public->fromSelect($public);
        $this->declaredMeldList->fromSelect($declare);
    }

    function getPlayerWind() {
        return $this->playerWind;
    }

    /**
     * @return Target A Target own by this Area's PlayerWind.
     */
    protected function getTarget() {
        $f = $this->getTarget;
        /** @var Target $target */
        $target = $f();

        if ($target->exist() && !$target->isOwner($this->getPlayerWind())) {
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
            $this->declaredMeldList,
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
    
    function reach() {
        
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

    function tempGenKeepTargetData() {
        $lastTile = $this->public->getLast(); // validate
        $this->public->remove($lastTile);
        return new Target(
            $lastTile, TargetType::create(TargetType::KEEP), $this->getPlayerWind()
        );
    }

    function tempGenKongTargetData(Tile $kongSelfTile) {
        $this->public->fromSelect($this->getPublicPlusTarget()->remove($kongSelfTile)); // validate
        return new Target(
            $kongSelfTile, TargetType::create(TargetType::KONG), $this->getPlayerWind()
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
        if ($declaredMeld && !$this->declaredMeldList->valueExist($declaredMeld, function (Meld $a, Meld $b) {
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
            $this->declaredMeldList->remove($declaredMeld, function (Meld $a, Meld $b) {
                return $a->equalTo($b, false);
            });
        }

        $this->declaredMeldList->insertLast($targetMeld);
        return $targetMeld;
    }
    //endregion
}

