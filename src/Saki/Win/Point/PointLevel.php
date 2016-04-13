<?php
namespace Saki\Win\Point;

use Saki\Util\Enum;

/**
 * @package Saki\Result
 */
class PointLevel extends Enum {
    const NONE = 1;
    const MANGAN = 2;
    const HANEMAN = 3;
    const BAIMAN = 4;
    const SANBAIMAN = 5;
    const YAKUMAN = 6;
    const W_YAKUMAN = 7;
    const MULTI_YAKUMAN = 9;

    /**
     * @param int $fan
     * @param int|null $fu
     * @return PointLevel
     */
    static function fromFanAndFu(int $fan, int $fu = null) {
        $valid = $fan >= 5 || $fu !== null;
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        switch ($fan) {
            case 1:
            case 2:
                $v = self::NONE;
                break;
            case 3:
                $v = $fu < 70 ? self::NONE : self::MANGAN;
                break;
            case 4:
                $v = $fu < 40 ? self::NONE : self::MANGAN;
                break;
            case 5:
                $v = self::MANGAN;
                break;
            case 6:
            case 7:
                $v = self::HANEMAN;
                break;
            case 8:
            case 9:
            case 10:
                $v = self::HANEMAN;
                break;
            case 11:
            case 12:
                $v = self::HANEMAN;
                break;
            default:
                if ($fan >= 39) {
                    $v = self::MULTI_YAKUMAN;
                } elseif ($fan >= 26) {
                    $v = self::W_YAKUMAN;
                } else {
                    $v = self::YAKUMAN;
                }
        }
        return static::create($v);
    }

    /**
     * @return bool
     */
    function isNone() {
        return $this->getValue() == self::NONE;
    }

    /**
     * @return bool
     */
    function isYakuMan() {
        $targetValues = [
            self::YAKUMAN, self::W_YAKUMAN, self::MULTI_YAKUMAN
        ];
        return $this->isTargetValue($targetValues);
    }
}