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

    abstract function getTileCount();

    final function valid(TileList $tileList) {
        return $this->validCount($tileList) && $this->validFaces($tileList);
    }

    final protected function validCount(TileList $tileList) {
        return count($tileList) == $this->getTileCount();
    }

    abstract protected function validFaces(TileList $tileList);

    /**
     * @return MeldType
     */
    static function getInstance() {
        return parent::getInstance();
    }
}