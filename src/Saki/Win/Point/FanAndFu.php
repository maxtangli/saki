<?php

namespace Saki\Win\Point;

use Saki\Util\Immutable;

/**
 * @package Saki\Win\Point
 */
class FanAndFu implements Immutable {
    private $fan;
    private $fu;

    /**
     * @param int $fan
     * @param int $fu
     * @return bool
     */
    static function valid(int $fan, int $fu) {
        return $fan >= 5 || $fu > 0;
    }

    /**
     * @param int $fan
     * @param int $fu
     */
    function __construct(int $fan, int $fu) {
        if (!self::valid($fan, $fu)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid argument $fan[%s], $fu[%s].', $fan, $fu)
            );
        }
        $this->fan = $fan;
        $this->fu = $fu;
    }

    /**
     * @return string
     */
    function __toString() {
        return implode(',', $this->toArray());
    }

    /**
     * @return int[]
     */
    function toArray() {
        return [$this->getFan(), $this->getFu()];
    }

    /**
     * @return int
     */
    function getFan() {
        return $this->fan;
    }

    /**
     * @return int
     */
    function getFu() {
        return $this->fu;
    }

    /**
     * @return PointLevel
     */
    function getPointLevel() {
        return PointLevel::fromFanAndFu($this->getFan(), $this->getFu());
    }
}