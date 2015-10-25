<?php
namespace Saki\Meld;

use Saki\Util\Enum;

class WinSetType extends Enum {
    const HAND_WIN_SET = 1;
    const DECLARE_WIN_SET = 2;
    const PAIR = 3;
    const PURE_WEAK = 4;

    function isHandWinSet() {
        return $this->getValue() == self::HAND_WIN_SET;
    }

    function isDeclareWinSet() {
        return $this->getValue() == self::DECLARE_WIN_SET;
    }

    function isWinSet() {
        return $this->isHandWinSet() || $this->isDeclareWinSet();
    }

    function isPair() {
        return $this->getValue() == self::PAIR;
    }

    function isWinSetOrPair() {
        return $this->isWinSet() || $this->isPair();
    }

    function isPureWeak() {
        return $this->getValue() == self::PURE_WEAK;
    }
}