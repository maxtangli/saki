<?php
namespace Saki\Meld;
use Saki\Tile\TileList;

/**
 * a special kind of MeldType which is able to turn into another TargetMeldType with one more tile.
 * @package Saki\Meld
 */
abstract class WeakMeldType extends MeldType {
    abstract function getTargetMeldType();
    final function getWaitingTiles(TileList $tileList) {
        if (!$this->valid($tileList)) {
            throw new \InvalidArgumentException();
        }
        return $this->getWaitingTilesImpl($tileList);
    }
    abstract protected function getWaitingTilesImpl(TileList $tileList);

    /**
     * @return WeakMeldType
     */
    static function getInstance() {
        return parent::getInstance();
    }
}