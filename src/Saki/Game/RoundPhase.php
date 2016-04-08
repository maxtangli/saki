<?php
namespace Saki\Game;

use Saki\Util\Enum;

class RoundPhase extends Enum {
    const NULL_PHASE = 0;
    const INIT_PHASE = 1;
    const PRIVATE_PHASE = 2;
    const PUBLIC_PHASE = 3;
    const OVER_PHASE = 4;

    static function getNullInstance() {
        return static::create(self::NULL_PHASE);
    }

    /**
     * @return RoundPhase
     */
    static function getInitInstance() {
        return static::create(self::INIT_PHASE);
    }

    /**
     * @return RoundPhase
     */
    static function getPrivateInstance() {
        return static::create(self::PRIVATE_PHASE);
    }

    /**
     * @return RoundPhase
     */
    static function getPublicInstance() {
        return static::create(self::PUBLIC_PHASE);
    }

    /**
     * @return RoundPhase
     */
    static function getOverInstance() {
        return static::create(self::OVER_PHASE);
    }

    function isNull() {
        return $this->getValue() == RoundPhase::NULL_PHASE;
    }

    function isInit() {
        return $this->getValue() == RoundPhase::INIT_PHASE;
    }

    function isPrivate() {
        return $this->getValue() == RoundPhase::PRIVATE_PHASE;
    }

    function isPublic() {
        return $this->getValue() == RoundPhase::PUBLIC_PHASE;
    }

    function isOver() {
        return $this->getValue() == RoundPhase::OVER_PHASE;
    }

    function isPrivateOrPublic() {
        return $this->isPrivate() || $this->isPublic();
    }
}