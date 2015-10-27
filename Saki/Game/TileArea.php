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
use Saki\Tile\TileSortedList;

class TileArea {
    private $handTileSortedList;
    private $discardedTileList;
    private $declaredMeldList;
    private $reachTurn;

    function __construct() {
        $this->handTileSortedList = TileSortedList::fromString('');
        $this->discardedTileList = TileList::fromString('');
        $this->declaredMeldList = MeldList::fromString('');
        $this->reachTurn = false;
    }

    function reset() {
        $this->handTileSortedList->setInnerArray([]);
        $this->discardedTileList->reset();
        $this->declaredMeldList->setInnerArray([]);
        $this->reachTurn = false;
    }

    /**
     * @return TileSortedList
     */
    function getHandTileSortedList() {
        return $this->handTileSortedList;
    }

    /**
     * @return TileList
     */
    function getDiscardedTileList() {
        return $this->discardedTileList;
    }

    /**
     * @return MeldList
     */
    function getDeclaredMeldList() {
        return $this->declaredMeldList;
    }

    /**
     * @return bool 門前清
     */
    function isConcealed() {
        return $this->getDeclaredMeldList()->isConcealed();
    }

    function isReach() {
        return $this->reachTurn !== false;
    }

    function getReachTurn() {
        if (!$this->isReach()) {
            throw new \LogicException();
        }
        return $this->reachTurn;
    }

    function isDoubleReach() {
        return $this->isReach() && $this->getReachTurn() == 1;
    }

    function reach(Tile $selfTile, $reachTurn) {
        if ($this->isReach()) {
            throw new \InvalidArgumentException();
        }
        $this->discard($selfTile);
        $this->reachTurn = $reachTurn;
    }

    /**
     * @param Tile[] $otherTiles
     */
    function drawInit($otherTiles) {
        // always valid
        $this->getHandTileSortedList()->push($otherTiles);
    }

    function draw(Tile $newTile) {
        // always valid
        $this->getHandTileSortedList()->push($newTile);
    }

    function drawReplacement(Tile $newTile) {
        // always valid
        $this->getHandTileSortedList()->push($newTile);
    }

    function canDiscard(Tile $selfTile) {
        return $this->getHandTileSortedList()->valueExist($selfTile);
    }

    function discard(Tile $selfTile) {
        $this->getHandTileSortedList()->removeByValue($selfTile); // valid test
        $this->getDiscardedTileList()->push($selfTile);
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
        if ($handTiles && !$this->getHandTileSortedList()->valueExist($handTiles)) {
            return false;
        }

        if ($declaredMeld && !$this->getDeclaredMeldList()->valueExist($declaredMeld, function (Meld $a, Meld $b) {
                return $a->equals($b, false);
            })
        ) {
            return false;
        }

        // exist tiles can form a fromMeld
        try {
            $fromMeld = $declaredMeld ?: new Meld(new TileList($handTiles));
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        // fromMeld can to target meld
        $hasWaitingTile = !($handTiles && !$otherTile && !$declaredMeld);
        if ($hasWaitingTile) {
            $waitingTile = $otherTile ?: $handTiles[0];
            return $fromMeld->canToTargetMeld($waitingTile, $targetMeldType);
        } else {
            return $fromMeld->getMeldType() == $targetMeldType;
        }
    }

    protected function declareMeld(MeldType $targetMeldType, $targetConcealed = null, array $handTiles = null, Tile $otherTile = null, Meld $declaredMeld = null) {
        if (!$this->canDeclareMeld($targetMeldType, $handTiles, $otherTile, $declaredMeld)) {
            throw new \InvalidArgumentException(
                sprintf('can not declared meld for [%s],[%s],[%s],[%s],[%s],[%s]', $targetMeldType, $targetConcealed, implode(',', $handTiles), $otherTile, $declaredMeld, $this->getHandTileSortedList())
            );
        }

        // remove origin tiles and meld
        if ($handTiles) {
            $this->getHandTileSortedList()->removeByValue($handTiles);
        }

        if ($declaredMeld) {
            $this->getDeclaredMeldList()->removeByValue($declaredMeld, function (Meld $a, Meld $b) {
                return $a->equals($b, false);
            });
        }

        // push target meld
        $fromMeld = $declaredMeld ?: new Meld(new TileList($handTiles));
        $hasWaitingTile = !($handTiles && !$otherTile && !$declaredMeld);
        if ($hasWaitingTile) {
            $waitingTile = $otherTile ?: $handTiles[0];
            $targetMeld = $fromMeld->toTargetMeld($waitingTile, $targetMeldType, $targetConcealed);
        } else {
            $targetMeld = $fromMeld->toConcealed($targetConcealed);
        }
        $this->getDeclaredMeldList()->push($targetMeld);
        return $targetMeld;
    }

    function canKongBySelf(Tile $selfTile) {
        $selfTiles = [$selfTile, $selfTile, $selfTile, $selfTile];
        return $this->canDeclareMeld(QuadMeldType::getInstance(), $selfTiles, null, null);
    }

    function kongBySelf(Tile $selfTile) {
        $handTiles = [$selfTile, $selfTile, $selfTile, $selfTile];
        return $this->declareMeld(QuadMeldType::getInstance(), true, $handTiles, null, null);
    }

    function canPlusKongBySelf(Tile $selfTile) {
        $declaredMeld = new Meld(new TileList([$selfTile, $selfTile, $selfTile]));
        return $this->canDeclareMeld(QuadMeldType::getInstance(), [$selfTile], null, $declaredMeld);
    }

    /**
     * @param Tile $selfTile
     * @return Meld
     */
    function plusKongBySelf(Tile $selfTile) {
        $declaredMeld = new Meld(new TileList([$selfTile, $selfTile, $selfTile]));
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

    function canPongByOther(Tile $otherTile) {
        $handTiles = [$otherTile, $otherTile];
        return $this->canDeclareMeld(TripleMeldType::getInstance(), $handTiles, $otherTile, null);
    }

    function pongByOther(Tile $otherTile) {
        $handTiles = [$otherTile, $otherTile];
        return $this->declareMeld(TripleMeldType::getInstance(), false, $handTiles, $otherTile, null);
    }

    function canKongByOther(Tile $otherTile) {
        $handTiles = [$otherTile, $otherTile, $otherTile];
        return $this->canDeclareMeld(QuadMeldType::getInstance(), $handTiles, $otherTile, null);
    }

    function kongByOther(Tile $otherTile) {
        $handTiles = [$otherTile, $otherTile, $otherTile];
        return $this->declareMeld(QuadMeldType::getInstance(), false, $handTiles, $otherTile, null);
    }

    function canPlusKongByOther(Tile $otherTile) {
        $declaredMeld = new Meld(new TileList([$otherTile, $otherTile, $otherTile]));
        return $this->canDeclareMeld(QuadMeldType::getInstance(), null, $otherTile, $declaredMeld);
    }

    function plusKongByOther(Tile $otherTile) {
        $declaredMeld = new Meld(new TileList([$otherTile, $otherTile, $otherTile]));
        return $this->declareMeld(QuadMeldType::getInstance(), false, null, $otherTile, $declaredMeld);
    }
}