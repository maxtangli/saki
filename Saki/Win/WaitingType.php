<?php
namespace Saki\Win;

use Saki\Util\Enum;

class WaitingType extends Enum {
    const NOT_EXIST = 0; // ノー聴
    const TWO_SIDE_RUN_WAITING = 1; // 両面待ち 78 -> 678 or 789
    const ONE_SIDE_RUN_WAITING = 2; // 辺張待ち 89 -> 789
    const MIDDLE_RUN_WAITING = 3; // 嵌張待ち 7 9 -> 789
    const SINGLE_PAIR_WAITING = 4; // 単騎待ち 1 -> 11
    const TWO_PONG_WAITING = 5; // 双碰待ち 11 -> 111

    function exist() {
        return $this->getValue() != self::NOT_EXIST;
    }
}