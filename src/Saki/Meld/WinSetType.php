<?php
namespace Saki\Meld;

use Saki\Util\Enum;

/**
 * @package Saki\Meld
 */
class WinSetType extends Enum {
    const HAND_WIN_SET = 1;
    const DECLARE_WIN_SET = 2;
    const PAIR = 3;
    const PURE_WEAK = 4;
    const SPECIAL = 5;

    /**
     * @return bool
     */
    function isHandWinSet() {
        return $this->getValue() == self::HAND_WIN_SET;
    }

    /**
     * @return bool
     */
    function isDeclareWinSet() {
        return $this->getValue() == self::DECLARE_WIN_SET;
    }

    /**
     * @return bool
     */
    function isWinSet() {
        return $this->isHandWinSet() || $this->isDeclareWinSet();
    }

    /**
     * @return bool
     */
    function isPair() {
        return $this->getValue() == self::PAIR;
    }

    /**
     * @return bool
     */
    function isWinSetOrPair() {
        return $this->isWinSet() || $this->isPair();
    }

    /**
     * @return bool
     */
    function isPureWeak() {
        return $this->getValue() == self::PURE_WEAK;
    }

    /**
     * @return bool
     */
    function isSpecial() {
        return $this->getValue() == self::SPECIAL;
    }
}