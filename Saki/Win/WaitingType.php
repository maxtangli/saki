<?php
namespace Saki\Win;

use Saki\Util\Enum;
use Saki\Util\Utils;

class WaitingType extends Enum {
    static function getComparator() {
        $descBestOnes = [ // todo adjust orders
            WaitingType::getInstance(self::TWO_SIDE_RUN_WAITING),
            WaitingType::getInstance(self::ONE_SIDE_RUN_WAITING),
            WaitingType::getInstance(self::MIDDLE_RUN_WAITING),
            WaitingType::getInstance(self::PAIR_WAITING),
            WaitingType::getInstance(self::TRIPLE_WAITING),
            WaitingType::getInstance(self::NOT_WAITING),
        ];
        return Utils::getComparatorByBestArray($descBestOnes);
    }

    function compareTo(WaitingType $other) {
        $f = $this->getComparator();
        return $f($this, $other);
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

    /**
     * @param $value
     * @return WaitingType
     */
    static function getInstance($value) {
        return parent::getInstance($value);
    }
}