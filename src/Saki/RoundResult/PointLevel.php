<?php
namespace Saki\RoundResult;

use Saki\Util\Enum;

class PointLevel extends Enum {
    const NONE = 1;
    const MAN_GAN = 2;
    const HANE_MAN = 3;
    const BAI_MAN = 4;
    const SAN_BAI_MAN = 5;
    const YAKU_MAN = 6;
    const W_YAKU_MAN = 7;
    const MULTI_YAKU_MAN = 9;

    /**
     * @param int $fan
     * @param int|null $fu
     * @return PointLevel
     */
    static function fromFanAndFu($fan, $fu = null) {
        switch ($fan) {
            case 1:
            case 2:
                $v = self::NONE;
                break;
            case 3:
                $v = $fu < 70 ? self::NONE : self::MAN_GAN;
                break;
            case 4:
                $v = $fu < 40 ? self::NONE : self::MAN_GAN;
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
                if ($fan >= 39) {
                    $v = self::MULTI_YAKU_MAN;
                } elseif ($fan >= 26) {
                    $v = self::W_YAKU_MAN;
                } else {
                    $v = self::YAKU_MAN;
                }
        }
        return static::create($v);
    }

    function isYakuMan() {
        $targetValues = [
            self::YAKU_MAN, self::W_YAKU_MAN, self::MULTI_YAKU_MAN
        ];
        return in_array($this->getValue(), $targetValues);
    }
}