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
        $matchDiscard = function (FutureWaiting $futureWaiting) use ($tile) {
            return $futureWaiting->getDiscard() == $tile;
        };
        return $this->any($matchDiscard);
    }

    /**
     * @return ArrayList
     */
    function toDiscardList() {
        $toDiscard = function (FutureWaiting $futureWaiting) {
            return $futureWaiting->getDiscard();
        };
        return $this->toArrayList($toDiscard);
    }
}