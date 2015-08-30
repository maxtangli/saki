<?php
namespace Saki\Meld;

use Saki\Tile\TileList;
use Saki\Util\Singleton;

abstract class MeldType extends Singleton {
    function __toString() {
        // Saki\Meld\MeldType -> MeldType
        $actualClass = get_called_class();
        $lastSeparatorPos = strrpos($actualClass, '\\');
        return substr($actualClass, $lastSeparatorPos + 1);
    }

    // valid

    abstract function getTileCount();

    final function valid(TileList $tileList) {
        return $this->validCount($tileList) && $this->validFaces($tileList);
    }

    final protected function validCount(TileList $tileList) {
        return count($tileList) == $this->getTileCount();
    }

    abstract protected function validFaces(TileList $tileList);

    // target MeldType

    final function hasTargetMeldType() {
        return !empty($this->getTargetMeldType());
    }

    abstract function getTargetMeldType();

    final function getWaitingTiles(TileList $tileList) {
        if (!$this->hasTargetMeldType()) {
            throw new \InvalidArgumentException(
                sprintf('Invalid call on no-target-meld-type MeldType[%s]', get_called_class())
            );
        }

        if (!$this->valid($tileList)) {
            throw new \InvalidArgumentException();
        }

        return $this->getWaitingTilesImpl($tileList);
    }

    abstract protected function getWaitingTilesImpl(TileList $tileList);

    /**
     * @return MeldType
     */
    static function getInstance() {
        return parent::getInstance();
    }
}