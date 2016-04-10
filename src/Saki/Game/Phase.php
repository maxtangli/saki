<?php
namespace Saki\Game;

use Saki\Util\Enum;

class Phase extends Enum {
    const NULL_PHASE = 0;
    const INIT_PHASE = 1;
    const PRIVATE_PHASE = 2;
    const PUBLIC_PHASE = 3;
    const OVER_PHASE = 4;

    static function getNullInstance() {
        return static::create(self::NULL_PHASE);
    }

    /**
     * @return Phase
     */
    static function getInitInstance() {
        return static::create(self::INIT_PHASE);
    }

    /**
     * @return Phase
     */
    static function getPrivateInstance() {
        return static::create(self::PRIVATE_PHASE);
    }

    /**
     * @return Phase
     */
    static function getPublicInstance() {
        return static::create(self::PUBLIC_PHASE);
    }

    /**
     * @return Phase
     */
    static function getOverInstance() {
        return static::create(self::OVER_PHASE);
    }

    function isNull() {
        return $this->getValue() == Phase::NULL_PHASE;
    }

    function isInit() {
        return $this->getValue() == Phase::INIT_PHASE;
    }

    function isPrivate() {
        return $this->getValue() == Phase::PRIVATE_PHASE;
    }

    function isPublic() {
        return $this->getValue() == Phase::PUBLIC_PHASE;
    }

    function isOver() {
        return $this->getValue() == Phase::OVER_PHASE;
    }

    function isPrivateOrPublic() {
        return $this->isPrivate() || $this->isPublic();
    }
}