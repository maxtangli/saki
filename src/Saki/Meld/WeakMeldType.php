<?php

namespace Saki\Meld;

use Saki\Tile\TileList;
use Saki\Win\Waiting\WaitingType;

/**
 * The MeldType which can be converted to target MeldType by adding 1 tile.
 * Used in: WaitingAnalyzer.
 * @package Saki\Meld
 */
abstract class WeakMeldType extends MeldType {
    //region MeldType override
    function hasTargetMeldType() {
        return true;
    }
    //endregion

    /**
     * @param TileList $meldTileList
     * @return TileList sorted waiting-tile list.
     */
    final function getWaiting(TileList $meldTileList) {
        if (!$this->valid($meldTileList)) {
            throw new \InvalidArgumentException();
        }
        return $this->getWaitingImpl($meldTileList);
    }

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

    //region subclass hooks
    /**
     * @return bool
     */
    abstract function getTargetMeldType();

    /**
     * @param TileList $validMeldTileList
     * @return TileList
     */
    abstract protected function getWaitingImpl(TileList $validMeldTileList);

    /**
     * @param TileList $validMeldTileList
     * @return WaitingType
     */
    abstract protected function getWaitingTypeImpl(TileList $validMeldTileList);
    //endregion
}