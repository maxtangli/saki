<?php
namespace Saki\Game;

use Saki\Util\Enum;

/**
 * @package Saki\Game
 */
class Phase extends Enum {
    const NULL_PHASE = 0;
    const INIT_PHASE = 1;
    const PRIVATE_PHASE = 2;
    const PUBLIC_PHASE = 3;
    const OVER_PHASE = 4;

    /**
     * @return Phase
     */
    static function createNull() {
        return static::create(self::NULL_PHASE);
    }

    /**
     * @return Phase
     */
    static function createInit() {
        return static::create(self::INIT_PHASE);
    }

    /**
     * @return Phase
     */
    static function createPrivate() {
        return static::create(self::PRIVATE_PHASE);
    }

    /**
     * @return Phase
     */
    static function createPublic() {
        return static::create(self::PUBLIC_PHASE);
    }

    /**
     * @return Phase
     */
    static function createOver() {
        return static::create(self::OVER_PHASE);
    }

    /**
     * @return bool
     */
    function isNull() {
        return $this->getValue() == Phase::NULL_PHASE;
    }

    /**
     * @return bool
     */
    function isInit() {
        return $this->getValue() == Phase::INIT_PHASE;
    }

    /**
     * @return bool
     */
    function isPrivate() {
        return $this->getValue() == Phase::PRIVATE_PHASE;
    }

    /**
     * @return bool
     */
    function isPublic() {
        return $this->getValue() == Phase::PUBLIC_PHASE;
    }

    /**
     * @return bool
     */
    function isOver() {
        return $this->getValue() == Phase::OVER_PHASE;
    }

    /**
     * @return bool
     */
    function isPrivateOrPublic() {
        return $this->isPrivate() || $this->isPublic();
    }
}