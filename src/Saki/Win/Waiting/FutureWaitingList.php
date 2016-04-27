<?php
namespace Saki\Win\Waiting;

use Saki\Tile\Tile;
use Saki\Util\ArrayList;

/**
 * @package Saki\Win\Waiting
 */
class FutureWaitingList extends ArrayList {
    /**
     * @param Tile $tile
     * @return bool
     */
    function discardExist(Tile $tile) {
        return $this->any(function (FutureWaiting $futureWaiting) use ($tile) {
            return $futureWaiting->getDiscard() == $tile;
        });
    }
}