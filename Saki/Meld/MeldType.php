<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
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

    /**
     * @param Tile $firstTile
     * @return TileList[] possible sorted TileLists begin with $firstTile under this MeldType.
     */
    abstract function getPossibleTileLists(Tile $firstTile);

    final protected function getPossibleTileListsImplByRepeat(Tile $firstTile) {
        $tiles = array_fill(0, $this->getTileCount(), $firstTile);
        return [new TileList($tiles)];
    }

    /**
     * note: convenient call for $meldType instanceof WeakMeldType
     * @return bool
     */
    function hasTargetMeldType() {
        return false;
    }

    /**
     * note: A持有B，B的某些信息方法，A也需要直接暴露-》将这些信息打包为单个类以便于共享。
     * @return WinSetType
     */
    abstract function getWinSetType();
}

