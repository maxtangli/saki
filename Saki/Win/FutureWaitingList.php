<?php
namespace Saki\Win;

use Saki\Util\ArrayLikeObject;

class FutureWaitingList extends ArrayLikeObject {

    function isForWaitingDiscardedTile(Tile $tile) {
        return $this->any(function (FutureWaiting $futureWaiting) use($tile) {
            return $futureWaiting->getDiscardedTile() == $tile;
        });
    }
}