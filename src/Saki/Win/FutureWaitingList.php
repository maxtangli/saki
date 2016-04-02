<?php
namespace Saki\Win;

use Saki\Tile\Tile;
use Saki\Util\ArrayList;

class FutureWaitingList extends ArrayList {

    function isForWaitingDiscardedTile(Tile $tile) {
        return $this->isAny(function (FutureWaiting $futureWaiting) use($tile) {
            return $futureWaiting->getDiscardedTile() == $tile;
        });
    }
}