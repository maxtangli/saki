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

/*
TileCount
- init: all 13
- private: current 14 other 13
- public: current 13 other 14
 */

class TileArea {
    private $handTileList;
    private $discardedTileList;
    private $declaredMeldList;
    private $reachGlobalTurn;

    function __construct() {
        $this->handTileList = TileList::fromString('');
        $this->discardedTileList = TileList::fromString('');
        $this->declaredMeldList = MeldList::fromString('');
        $this->reachGlobalTurn = false;
    }

    function reset() {
        $this->handTileList->removeAll();
        $this->discardedTileList->removeAll();
        $this->declaredMeldList->removeAll();
        $this->reachGlobalTurn = false;
    }

    /**
     * note: should not be used except when client is not sure handTileList is 13 or 14 style.
     * @return TileList
     */
    function getHandReference() {
        return $this->handTileList;
    }

    /**
     * @return TileList
     */
    function getDiscardedReference() {
        return $this->discardedTileList;
    }

    /**
     * @return MeldList
     */
    function getDeclaredMeldListReference() {
        return $this->declaredMeldList;
    }

    /**
     * @return bool 門前清
     */
    function isConcealed() {
        return $this->getDeclaredMeldListReference()->isConcealed();
    }

    function isReach() {
        return $this->reachGlobalTurn !== false;
    }

    function getReachGlobalTurn() {
        if (!$this->isReach()) {
            throw new \LogicException();
        }
        return $this->reachGlobalTurn;
    }

    function isDoubleReach() {
        return $this->isReach() && $this->getReachGlobalTurn() == 1;
    }

    function reach(Tile $selfTile, $reachGlobalTurn) {
        if ($this->isReach()) {
            throw new \InvalidArgumentException();
        }
        $this->discard($selfTile);
        $this->reachGlobalTurn = $reachGlobalTurn;
    }

    /**
     * @param Tile[] $otherTiles
     */
    function drawInit($otherTiles) {
        // always valid
        $this->getHandReference()->insertLast($otherTiles);
    }

    function draw(Tile $newTile) {
        // always valid
        $this->getHandReference()->insertLast($newTile);
    }

    function drawReplacement(Tile $newTile) {
        // always valid
        $this->getHandReference()->insertLast($newTile);
    }

    function canDiscard(Tile $selfTile) {
        return $this->getHandReference()->valueExist($selfTile);
    }

    function discard(Tile $selfTile) {
        $this->getHandReference()->remove($selfTile); // validate
        $this->getDiscardedReference()->insertLast($selfTile);
    }

    /*
     * kongBySelf      hand []        -> declare, handMeld
     * plusKongBySelf  hand 1,declare -> declare, declareMeld + hand1
     * chowByOther     hand [],other  -> declare, handMeld + other
     * pongByOther     hand [],other  -> declare, handMeld + other
     * kongByOther     hand [],other  -> declare, handMeld + other
     * plusKongByOther declare,other  -> declare, declareMeld + other
     */

    protected function canDeclareMeld(MeldType $targetMeldType, array $handTiles = null, Tile $otherTile = null, Meld $declaredMeld = null) {
        // exist
        if ($handTiles && !$this->getHandReference()->valueExist($handTiles)) {
            return false;
        }

        if ($declaredMeld && !$this->getDeclaredMeldListReference()->valueExist($declaredMeld, function (Meld $a, Meld $b) {
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

    protected function declareMeld(MeldType $targetMeldType, $targetConcealed = null,
                                   array $handTiles = null, Tile $otherTile = null, Meld $declaredMeld = null) {
        if (!$this->canDeclareMeld($targetMeldType, $handTiles, $otherTile, $declaredMeld)) {
            throw new \InvalidArgumentException(
                sprintf('can not declared meld for [%s],[%s],[%s],[%s],[%s],[%s]',
                    $targetMeldType, $targetConcealed, implode(',', $handTiles), $otherTile, $declaredMeld, $this->getHandReference())
            );
        }

        // remove origin tiles and meld
        if ($handTiles) {
            $this->getHandReference()->remove($handTiles);
        }

        if ($declaredMeld) {
            $this->getDeclaredMeldListReference()->remove($declaredMeld, function (Meld $a, Meld $b) {
                return $a->equalTo($b, false);
            });
        }

        // push target meld
        $fromMeld = $declaredMeld ?? new Meld($handTiles);
        $hasWaitingTile = !($handTiles && !$otherTile && !$declaredMeld);
        if ($hasWaitingTile) {
            $waitingTile = $otherTile ?? $handTiles[0];
            $targetMeld = $fromMeld->toTargetMeld($waitingTile, $targetMeldType, $targetConcealed);
        } else {
            $targetMeld = $fromMeld->toConcealed($targetConcealed);
        }
        $this->getDeclaredMeldListReference()->insertLast($targetMeld);
        return $targetMeld;
    }

    function canConcealedKong(Tile $selfTile) {
        $selfTiles = [$selfTile, $selfTile, $selfTile, $selfTile];
        return $this->canDeclareMeld(QuadMeldType::getInstance(), $selfTiles, null, null);
    }

    function concealedKong(Tile $selfTile) {
        $handTiles = [$selfTile, $selfTile, $selfTile, $selfTile];
        return $this->declareMeld(QuadMeldType::getInstance(), true, $handTiles, null, null);
    }

    function canPlusKong(Tile $selfTile) {
        $declaredMeld = new Meld([$selfTile, $selfTile, $selfTile]);
        return $this->canDeclareMeld(QuadMeldType::getInstance(), [$selfTile], null, $declaredMeld);
    }

    /**
     * @param Tile $selfTile
     * @return Meld
     */
    function plusKong(Tile $selfTile) {
        $declaredMeld = new Meld([$selfTile, $selfTile, $selfTile]);
        return $this->declareMeld(QuadMeldType::getInstance(), null, [$selfTile], null, $declaredMeld);
    }

    function canChowByOther(Tile $otherTile, Tile $selfTile1, Tile $selfTile2) {
        $handTiles = [$selfTile1, $selfTile2];
        return $this->canDeclareMeld(RunMeldType::getInstance(), $handTiles, $otherTile, null);
    }

    function chowByOther(Tile $otherTile, Tile $selfTile1, Tile $selfTile2) {
        $handTiles = [$selfTile1, $selfTile2];
        return $this->declareMeld(RunMeldType::getInstance(), false, $handTiles, $otherTile, null);
    }

    function canPong(Tile $otherTile) {
        $handTiles = [$otherTile, $otherTile];
        return $this->canDeclareMeld(TripleMeldType::getInstance(), $handTiles, $otherTile, null);
    }

    function pongByOther(Tile $otherTile) {
        $handTiles = [$otherTile, $otherTile];
        return $this->declareMeld(TripleMeldType::getInstance(), false, $handTiles, $otherTile, null);
    }

    function canBigKong(Tile $otherTile) {
        $handTiles = [$otherTile, $otherTile, $otherTile];
        return $this->canDeclareMeld(QuadMeldType::getInstance(), $handTiles, $otherTile, null);
    }

    function bigKong(Tile $otherTile) {
        $handTiles = [$otherTile, $otherTile, $otherTile];
        return $this->declareMeld(QuadMeldType::getInstance(), false, $handTiles, $otherTile, null);
    }

    function canSmallKong(Tile $otherTile) {
        $declaredMeld = new Meld([$otherTile, $otherTile, $otherTile]);
        return $this->canDeclareMeld(QuadMeldType::getInstance(), null, $otherTile, $declaredMeld);
    }

    function smallKong(Tile $otherTile) {
        $declaredMeld = new Meld([$otherTile, $otherTile, $otherTile]);
        return $this->declareMeld(QuadMeldType::getInstance(), false, null, $otherTile, $declaredMeld);
    }
}