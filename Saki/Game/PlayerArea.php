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

    function keepCandidateTileIfExist() {
        if ($this->hasCandidateTile()) {
            $this->getOnHandTileSortedList()->push($this->removeCandidateTile());
        }
    }

    function draw(Tile $newTile) {
        $valid = !$this->hasCandidateTile();
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->setCandidateTile($newTile);
    }

    function discard(Tile $selfTile) {
        $valid = true;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->keepCandidateTileIfExist();
        $this->getOnHandTileSortedList()->removeByValue($selfTile);
        $this->getDiscardedTileList()->push($selfTile);
    }

    function concealedKong(Tile $selfTile) {
        $valid = true;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->keepCandidateTileIfExist();
        $this->getOnHandTileSortedList()->removeByValue([$selfTile,$selfTile,$selfTile,$selfTile]);
        $meld = new Meld(new TileList([$selfTile, $selfTile, $selfTile, $selfTile]), KongMeldType::getInstance());
        $this->getExposedMeldList()->insert($meld);
    }

    function plusKong(Tile $selfTile) {
        $valid = true;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->keepCandidateTileIfExist();
        $this->getExposedMeldList()->plusKong($selfTile);
        $this->getOnHandTileSortedList()->removeByValue($selfTile);
    }

    function chow(Tile $newTile, Tile $selfTile1, Tile $selfTile2) {

    }

    function pong(Tile $newTile) {

    }

    function exposedKong(Tile $newTile) {

    }
}