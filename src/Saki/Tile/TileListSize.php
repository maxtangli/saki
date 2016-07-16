<?php

namespace Saki\Tile;

/**
 * @package Saki\Tile
 */
class TileListSize {
    private $count;

    /**
     * @param int $count
     */
    function __construct(int $count) {
        $this->count = $count;
    }

    /**
     * @return string
     */
    function __toString() {
        $countToken = $this->getCount();
        $phaseToken = $this->isPrivate() ? 'private' : ($this->isPublic() ? 'public' : 'invalid');
        return sprintf('%s,%s', $countToken, $phaseToken);
    }

    /**
     * @return int
     */
    function getCount() {
        return $this->count;
    }

    /**
     * claim-count private public
     * 0 14 13
     * 1 11 10
     * 2 8 7
     * 3 5 4
     * 4 2 1
     * @return bool
     */
    function isPublic() {
        return $this->getCount() && $this->getCount() % 3 == 1;
    }

    /**
     * @return bool
     */
    function isPrivate() {
        return $this->getCount() && $this->getCount() % 3 == 2;
    }

    /**
     * @return bool
     */
    function isComplete() {
        return $this->getCount() >= 14;
    }

    /**
     * @param TileListSize $other
     * @return bool
     */
    function samePhase(TileListSize $other) {
        return ($this->isPrivate() && $other->isPrivate())
        || ($this->isPublic() && $other->isPublic());
    }
}