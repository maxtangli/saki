<?php

namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;

abstract class WeakMeldType extends MeldType {
    function hasTargetMeldType() {
        return true;
    }

    abstract function getTargetMeldType();

    final function getWaitingTiles(TileSortedList $meldTileSortedList) {
        if (!$this->hasTargetMeldType()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid call on no-target-meld-type MeldType[%s]', get_called_class())
            );
        }

        if (!$this->valid($meldTileSortedList)) {
            throw new \InvalidArgumentException();
        }

        return $this->getWaitingTilesImpl($meldTileSortedList);
    }

    abstract protected function getWaitingTilesImpl(TileSortedList $validMeldTileSortedList);

    final function getWaitingType(TileSortedList $meldTileSortedList) {
        if (!$this->hasTargetMeldType()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid call on no-target-meld-type MeldType[%s]', get_called_class())
            );
        }

        if (!$this->valid($meldTileSortedList)) {
            throw new \InvalidArgumentException();
        }

        return $this->getWaitingTypeImpl($meldTileSortedList);
    }

    abstract protected function getWaitingTypeImpl(TileSortedList $validMeldTileSortedList);

    /**
     * @return WeakMeldType
     */
    static function getInstance() {
        return parent::getInstance();
    }
}