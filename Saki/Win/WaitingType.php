<?php
namespace Saki\Win;

use Saki\Util\PriorityComparable;
use Saki\Util\Enum;

class WaitingType extends Enum {
    use PriorityComparable;

    function getPriority() {
        $m = [ // todo adjust orders
            self::TWO_SIDE_RUN_WAITING => 6,
            self::ONE_SIDE_RUN_WAITING => 5,
            self::MIDDLE_RUN_WAITING => 4,
            self::PAIR_WAITING => 3,
            self::TRIPLE_WAITING => 2,
            self::NOT_WAITING => 1,
        ];
        return $m[$this->getValue()];
    }

    const NOT_WAITING = 0; // ノー聴
    const TWO_SIDE_RUN_WAITING = 1; // 両面待ち 78 -> 678 or 789
    const ONE_SIDE_RUN_WAITING = 2; // 辺張待ち 89 -> 789
    const MIDDLE_RUN_WAITING = 3; // 嵌張待ち 7 9 -> 789
    const PAIR_WAITING = 4; // 単騎待ち 1 -> 11
    const TRIPLE_WAITING = 5; // 双碰待ち 11 -> 111

    function exist() {
        return $this->getValue() != self::NOT_WAITING;
    }
}