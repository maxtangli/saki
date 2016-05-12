<?php
namespace Saki\Game;

use Saki\Util\Enum;

/**
 * TargetType-ownBy?
 * draw          DRAW-self
 * concealedKong REPLACE-self
 * extendKong    KONG-other KEEP-self REPLACE-self
 * discard       DISCARD-other
 * chow, pung    KEEP-self
 * kong          REPLACE-self
 * @package Saki\Game
 */
class TargetType extends Enum {
    const DRAW = 0;
    const REPLACE = 1;
    const KEEP = 2;
    const DISCARD = 3;
    const KONG = 4;

    /**
     * @return bool
     */
    function isOwnByCreator() {
        switch ($this->getValue()) {
            case self::DRAW:
            case self::REPLACE:
            case self::KEEP:
                return true;
            case self::DISCARD:
            case self::KONG:
                return false;
        }
    }
}