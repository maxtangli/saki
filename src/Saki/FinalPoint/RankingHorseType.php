<?php

namespace Saki\FinalPoint;

use Saki\Util\Enum;

class RankingHorseType extends Enum {
    const UMA_5_10 = 1;
    const UMA_10_20 = 2;
    const UMA_10_30 = 3;
    const UMA_20_30 = 4;

    static function getValue2StringMap() {
        return [
            self::UMA_5_10 => '5-10',
            self::UMA_10_20 => '10-20',
            self::UMA_10_30 => '10-30',
            self::UMA_20_30 => '20-30',
        ];
    }

    function toHorsePoints() {
        $a = [
            self::UMA_5_10 => [10000, 5000, -5000, -10000],
            self::UMA_10_20 => [20000, 10000, -10000, -20000],
            self::UMA_10_30 => [30000, 10000, -10000, -30000],
            self::UMA_20_30 => [30000, 20000, -20000, -30000],
        ];
        return $a[$this->getValue()];
    }
}