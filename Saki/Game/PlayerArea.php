<?php
namespace Saki\Game;

use Saki\Meld\QuadMeldType;
use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Meld\RunMeldType;
use Saki\Meld\TripleMeldType;
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

    function moveCandidateTileToHandIfExist() {
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
        $this->moveCandidateTileToHandIfExist();
        $this->getOnHandTileSortedList()->removeByValue($selfTile);
        $this->getDiscardedTileList()->push($selfTile);
    }

    function concealedKong(Tile $selfTile) {
        $valid = true;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->moveCandidateTileToHandIfExist();
        $this->getOnHandTileSortedList()->removeByValue([$selfTile,$selfTile,$selfTile,$selfTile]);
        $meld = new Meld(new TileList([$selfTile, $selfTile, $selfTile, $selfTile]), QuadMeldType::getInstance());
        $this->getExposedMeldList()->insert($meld);
    }

    function plusKong(Tile $selfTile) {
        $valid = true;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }
        $this->moveCandidateTileToHandIfExist();
        $this->getExposedMeldList()->plusKong($selfTile);
        $this->getOnHandTileSortedList()->removeByValue($selfTile);
    }

    function chow(Tile $newTile, Tile $selfTile1, Tile $selfTile2) {
        $meld = new Meld(new TileList([$newTile, $selfTile1, $selfTile2]), RunMeldType::getInstance());
        $this->getOnHandTileSortedList()->removeByValue([$selfTile1, $selfTile2]);
        $this->getExposedMeldList()->insert($meld);
    }

    function pong(Tile $newTile) {
        $meld = new Meld(new TileList([$newTile, $newTile, $newTile]), TripleMeldType::getInstance());
        $this->getOnHandTileSortedList()->removeByValue([$newTile, $newTile]);
        $this->getExposedMeldList()->insert($meld);
    }

    function exposedKong(Tile $newTile) {
        $meld = new Meld(new TileList([$newTile, $newTile, $newTile,$newTile]), QuadMeldType::getInstance());
        $this->getOnHandTileSortedList()->removeByValue([$newTile, $newTile, $newTile]);
        $this->getExposedMeldList()->insert($meld);
    }
}