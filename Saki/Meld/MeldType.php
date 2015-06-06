<?php
namespace Saki\Meld;

use Saki\TileList;
use Saki\Util\Singleton;

abstract class MeldType extends Singleton{
    abstract function getTileCount();

    final function valid(TileList $tileList) {
        return $this->validCount($tileList) && $this->validFaces($tileList);
    }

    final protected function validCount(TileList $tileList) {
        return count($tileList) == $this->getTileCount();
    }

    abstract protected function validFaces(TileList $tileList);
}