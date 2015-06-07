<?php
namespace Saki\Game;

use Saki\Meld\KongMeldType;
use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\SequenceMeldType;
use Saki\Meld\TripletMeldType;
use Saki\TileList;
use Saki\TileOrderedList;
use Saki\Tile;
use Saki\Meld\MeldType;

class Hand {
    private $onHandTileOrderedList;
    private $discardedTileList;
    private $exposedMeldList;

    function __construct(TileOrderedList $onHandTileOrderedList, TileList $discardedTileList = null, MeldList $exposedMeldList = null) {
        $this->onHandTileOrderedList = $onHandTileOrderedList;
        $this->discardedTileList = $discardedTileList ?: TileList::fromString('', false);
        $this->exposedMeldList = $exposedMeldList ?: new MeldList([]);
    }

    function getOnHandTileOrderedList() {
        return $this->onHandTileOrderedList;
    }

    function getDiscardedTileList() {
        return $this->discardedTileList;
    }

    function getExposedMeldList() {
        return $this->exposedMeldList;
    }

    function discard(Tile $newTile) {
        $this->getDiscardedTileList()->add($newTile);
    }

    function replace(Tile $onHandTile, Tile $newTile) {
        $this->getOnHandTileOrderedList()->replace($onHandTile, $newTile);
        $this->discard($onHandTile);
    }

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
        $this->getOnHandTileOrderedList()->remove($onHandTile);
    }

    protected function createMeld(MeldType $targetMeld, array $onHandTiles, Tile $newTile = null) {
        $onHandTileList = $this->getOnHandTileOrderedList();
        $targetTiles = $newTile === null ? $onHandTiles : array_merge($onHandTiles, [$newTile]);
        $targetTileList = new TileList($targetTiles);
        if ($targetMeld->valid($targetTileList)) {
            $meld = new Meld($targetTileList, $targetMeld);
            $onHandTileList->removeMany($onHandTiles);
            $this->getExposedMeldList()->add($meld);
        } else {
            throw new \InvalidArgumentException("Invalid \$targetTileList[$targetTileList] for \$targetMeld[$targetMeld]");
        }
    }
}