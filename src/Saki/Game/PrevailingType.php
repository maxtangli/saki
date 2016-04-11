<?php
namespace Saki\Game;

use Saki\Util\Enum;

/**
 * Design note: Nothing but an implementation helper for PrevailingContext.
 * @package Saki\Game
 */
class PrevailingType extends Enum {
    const EAST = 1;
    const EAST_SOUTH = 2;

    /**
     * PrevailingContext's implementation helper.
     * @return PrevailingWind
     */
    function getNormalLast() {
        switch ($this->getValue()) {
            case self::EAST:
                return PrevailingWind::fromString('E');
            case self::EAST_SOUTH:
                return PrevailingWind::fromString('S');
        }
        throw new \LogicException();
    }

    /**
     * PrevailingContext's implementation helper.
     * @return PrevailingWind
     */
    function getSuddenDeathLast() {
        return $this->getNormalLast()->toNext();
    }
}