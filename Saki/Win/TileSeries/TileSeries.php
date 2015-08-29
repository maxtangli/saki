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
     * @param Tile $winTile
     * @return WaitingType
     */
    final function getWaitingType(MeldList $allMeldList, Tile $winTile) {
        if (!$allMeldList->tileExist($winTile)) {
            return WaitingType::getInstance(WaitingType::NOT_EXIST);
        } elseif (!$this->existIn($allMeldList)) {
            return WaitingType::getInstance(WaitingType::NOT_EXIST);
        } else {
            $waitingType = $this->getWaitingTypeImpl($allMeldList, $winTile);
            $valid = $waitingType instanceof WaitingType && $waitingType->exist();
            if (!$valid) {
                throw new \LogicException(
                    sprintf('Invalid implementation of [%s].getWaitingType $allMeldList[%s], $winTile[%s].' . "\n" .
                        'Returned $waitingType[%s].'
                        , get_called_class(), $allMeldList, $winTile, $waitingType)
                );
            }
            return $waitingType;
        }
    }

    /**
     * precondition: $allMeldList exist in $this, $winTile exist in $allMeldList
     * @param MeldList $allMeldList
     * @param Tile $winTile
     * @return WaitingType
     */
    abstract protected function getWaitingTypeImpl(MeldList $allMeldList, Tile $winTile);

    /**
     * @return TileSeries
     */
    static function getInstance() {
        return parent::getInstance();
    }
}