<?php
namespace Saki\Game;

use Saki\Util\Enum;

class TargetType extends Enum {
    const DRAW = 0; // private
    const REPLACEMENT = 1; // private
    const KEEP = 2; // private
    const DISCARD = 3; // public
    const KONG = 4; // public

    function isOwnByCreator() {
        switch ($this->getValue()) {
            case self::DRAW:
            case self::REPLACEMENT:
            case self::KEEP:
                return true;
            case self::DISCARD:
            case self::KONG:
                return false;
        }
    }
}