<?php
namespace Saki\Game\RoundResult;

use Saki\Util\Enum;

class ScoreLevel extends Enum {
    const NONE = 1;
    const MAN_GAN = 2;
    const HANE_MAN = 3;
    const BAI_MAN = 4;
    const SAN_BAI_MAN = 5;
    const YAKU_MAN = 6;
    const W_YAKU_MAN = 7;

    static function getValue2StringMap() {
        return [
            self::NONE => 'none',
            self::MAN_GAN => 'man gan',
            self::HANE_MAN => 'hane man',
            self::BAI_MAN => 'bai man',
            self::SAN_BAI_MAN => 'san bai man',
            self::YAKU_MAN => 'yaku man',
            self::W_YAKU_MAN => 'w yaku man',
        ];
    }

    static function fromFanAndFuCount($fanCount, $fuCount = null) {
        switch ($fanCount) {
            case 1:
            case 2:
                $v = self::NONE;
                break;
            case 3:
                $v = $fuCount < 70 ? self::NONE : self::MAN_GAN;
                break;
            case 4:
                $v = $fuCount < 40 ? self::NONE : self::MAN_GAN;
                break;
            case 5:
                $v = self::MAN_GAN;
                break;
            case 6:
            case 7:
                $v = self::HANE_MAN;
                break;
            case 8:
            case 9:
            case 10:
                $v = self::HANE_MAN;
            break;
            case 11:
            case 12:
                $v = self::HANE_MAN;
            break;
            default:
                $v = self::YAKU_MAN;
        }
        return static::getInstance($v);
    }
}