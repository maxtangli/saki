<?php
namespace Saki\Game;

use Saki\Meld\KongMeldType;
use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\SequenceMeldType;
use Saki\Meld\TripletMeldType;
use Saki\TileList;
use Saki\TileSortedList;
use Saki\Tile;
use Saki\Meld\MeldType;

class PlayerArea {
    private $onHandTileSortedList;
    private $discardedTileList;
    private $exposedMeldList;
    private $candidateTile;

    function __construct(TileSortedList $onHandTileSortedList = null, TileList $discardedTileList = null, MeldList $exposedMeldList = null, Tile $candidateTile = null) {
        $this->onHandTileSortedList = $onHandTileSortedList ?: TileSortedList::fromString('');
        $this->discardedTileList = $discardedTileList ?: TileList::fromString('');
        $this->exposedMeldList = $exposedMeldList ?: new MeldList([]);
        $this->candidateTile = $candidateTile;
    }

    function getOnHandTileSortedList() {
        return $this->onHandTileSortedList;
    }

    function getDiscardedTileList() {
        return $this->discardedTileList;
    }

    function getExposedMeldList() {
        return $this->exposedMeldList;
    }

    function hasCandidateTile() {
        return $this->candidateTile !== null;
    }

    function getCandidateTile() {
        if (!$this->hasCandidateTile()) {
            throw new \BadMethodCallException();
        }
        return $this->candidateTile;
    }

    function setCandidateTile(Tile $tile) {
        if ($tile === null) {
            throw new \InvalidArgumentException();
        }
        $this->candidateTile = $tile;
    }

    function removeCandidateTile() {
        if (!$this->hasCandidateTile()) {
            throw new \BadMethodCallException();
        }
        $ret = $this->candidateTile;
        $this->candidateTile = null;
        return $ret;
    }

    /**
     * @param $indexOrTile
     * @throws \InvalidArgumentException
     */
    function discard($indexOrTile) {
        if (is_int($indexOrTile)) {
            $tileSortedList = $this->getOnHandTileSortedList();
            if (isset($tileSortedList[$indexOrTile])) { //onHandIndex
                $tile = $tileSortedList[$indexOrTile];
            } elseif ($indexOrTile == count($tileSortedList) && $this->hasCandidateTile()) { // candidateIndex
                $tile = $this->getCandidateTile();
            } else {
                throw new \InvalidArgumentException();
            }
        } elseif ($indexOrTile instanceof Tile) {
            $tile = $indexOrTile;
        } else {
            throw new \InvalidArgumentException();
        }

        if ($this->hasCandidateTile()) {
            if ($this->getCandidateTile() == $tile) { // discardCandidate
                $this->getDiscardedTileList()->add($this->removeCandidateTile());
            } else { // keepCandidateAndDiscardOnHand
                $this->getOnHandTileSortedList()->replaceTile($tile, $this->removeCandidateTile());
                $this->getDiscardedTileList()->add($tile);
            }
        } else { // DiscardOnHand
            $this->getOnHandTileSortedList()->removeTile($tile);
            $this->getDiscardedTileList()->add($tile);
        }
    }

    /*
    function chow(Tile $onHandTile1, Tile $onHandTile2, Tile $newTile) {
        $this->createMeld(SequenceMeldType::getInstance(), [$onHandTile1, $onHandTile2], $newTile);
    }

    function exposedPong(Tile $onHandTile1, Tile $onHandTile2, Tile $newTile) {
        $this->createMeld(TripletMeldType::getInstance(), [$onHandTile1, $onHandTile2], $newTile);
    }

    function concealedPong(Tile $onHandTile1, Tile $onHandTile2, Tile $onHandTile3) {
        $this->createMeld(TripletMeldType::getInstance(), [$onHandTile1, $onHandTile2, $onHandTile3]);
    }

    function exposedKong(Tile $onHandTile1, Tile $onHandTile2, Tile $onHandTile3, Tile $newTile) {
        $this->createMeld(KongMeldType::getInstance(), [$onHandTile1, $onHandTile2, $onHandTile3], $newTile);
    }

    function concealedKong(Tile $onHandTile1, Tile $onHandTile2, Tile $onHandTile3, Tile $onHandTile4) {
        $this->createMeld(KongMeldType::getInstance(), [$onHandTile1, $onHandTile2, $onHandTile3, $onHandTile4]);
    }

    function plusKong(Tile $onHandTile) {
        $this->getExposedMeldList()->plusKong($onHandTile);
        $this->getOnHandTileSortedList()->remove($onHandTile);
    }
*/
    protected function createMeld(MeldType $targetMeld, array $onHandTiles, Tile $newTile = null) {
        $onHandTileList = $this->getOnHandTileSortedList();
        $targetTiles = $newTile === null ? $onHandTiles : array_merge($onHandTiles, [$newTile]);
        $targetTileList = new TileList($targetTiles);
        if ($targetMeld->valid($targetTileList)) {
            $meld = new Meld($targetTileList, $targetMeld);
            $onHandTileList->removeManyTiles($onHandTiles);
            $this->getExposedMeldList()->insert($meld);
        } else {
            throw new \InvalidArgumentException("Invalid \$targetTileList[$targetTileList] for \$targetMeld[$targetMeld]");
        }
    }

}