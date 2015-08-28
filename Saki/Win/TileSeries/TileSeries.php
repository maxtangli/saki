<?php
namespace Saki\Win\TileSeries;

use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Util\Singleton;
use Saki\Win\WaitingType;

abstract class TileSeries extends Singleton {
    /**
     * @param MeldList $allMeldList
     * @return bool
     */
    abstract function existIn(MeldList $allMeldList);

    /**
     * @param MeldList $allMeldList
     * @param Tile $tile
     * @return WaitingType
     */
    final function getWaitingType(MeldList $allMeldList, Tile $tile) {
        if (!$this->existIn($allMeldList)) {
            return WaitingType::getInstance(WaitingType::NOT_EXIST);
        } else {
            $waitingType = $this->getWaitingTypeImpl($allMeldList, $tile);
            $valid = $waitingType instanceof WaitingType && $waitingType->exist();
            if (!$valid) {
                throw new \LogicException('Invalid implementation of getWaitingType.');
            }
            return $waitingType;
        }
    }

    /**
     * @param MeldList $allMeldList
     * @param Tile $tile
     * @return WaitingType
     */
    abstract protected function getWaitingTypeImpl(MeldList $allMeldList, Tile $tile);

    /**
     * @return TileSeries
     */
    static function getInstance() {
        return parent::getInstance();
    }
}