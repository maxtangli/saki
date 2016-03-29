<?php

namespace Saki\Meld;

use Saki\Tile\TileList;

abstract class WeakMeldType extends MeldType {
    function hasTargetMeldType() {
        return true;
    }

    abstract function getTargetMeldType();

    final function getWaitingTiles(TileList $meldTileList) {
        if (!$this->hasTargetMeldType()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid call on no-target-meld-type MeldType[%s]', get_called_class())
            );
        }

        if (!$this->valid($meldTileList)) {
            throw new \InvalidArgumentException();
        }

        return $this->getWaitingTilesImpl($meldTileList);
    }

    abstract protected function getWaitingTilesImpl(TileList $validMeldTileList);

    final function getWaitingType(TileList $meldTileList) {
        if (!$this->hasTargetMeldType()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid call on no-target-meld-type MeldType[%s]', get_called_class())
            );
        }

        if (!$this->valid($meldTileList)) {
            throw new \InvalidArgumentException();
        }

        return $this->getWaitingTypeImpl($meldTileList);
    }

    abstract protected function getWaitingTypeImpl(TileList $validMeldTileList);
}