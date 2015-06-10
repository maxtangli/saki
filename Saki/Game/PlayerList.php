<?php
namespace Saki\Game;

use Saki\Util\ArrayLikeObject;

class PlayerList extends ArrayLikeObject {
    static function createPlayers($n, $initialScore) {
        return array_map(function ($i) use ($initialScore) {
            return new Player($i, $initialScore);
        }, range(1, $n));
    }

    function __construct(array $players) {
        parent::__construct($players);
    }

    function toFirstIndex($targetItem) {
        return parent::toFirstIndex($targetItem);
    }

    /**
     * @return Player[]
     */
    function toArray() {
        return parent::toArray();
    }

    /**
     * @param int $offset
     * @return Player
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }
}