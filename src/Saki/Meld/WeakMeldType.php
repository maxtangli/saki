<?php

namespace Saki\Meld;

use Saki\Tile\TileList;

/**
 * The MeldType which can be converted to target MeldType by adding 1 tile.
 * @package Saki\Meld
 */
abstract class WeakMeldType extends MeldType {
    function hasTargetMeldType() {
        return true;
    }

    /**
     * @return bool
     */
    abstract function getTargetMeldType();

    /**
     * @param TileList $meldTileList
     * @return TileList sorted waiting-tile list.
     */
    final function getWaitingTileList(TileList $meldTileList) {
        if (!$this->valid($meldTileList)) {
            throw new \InvalidArgumentException();
        }
        return $this->getWaitingTileListImpl($meldTileList);
    }

    abstract protected function getWaitingTileListImpl(TileList $validMeldTileList);

    /**
     * @param TileList $meldTileList
     * @return WaitingType
     */
    final function getWaitingType(TileList $meldTileList) {
        if (!$this->valid($meldTileList)) {
            throw new \InvalidArgumentException();
        }
        return $this->getWaitingTypeImpl($meldTileList);
    }

    abstract protected function getWaitingTypeImpl(TileList $validMeldTileList);
}