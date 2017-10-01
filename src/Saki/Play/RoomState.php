<?php

namespace Saki\Play;

use Saki\Util\Enum;

/**
 * @package Saki\Play
 */
class RoomState extends Enum {
    const NULL = 0;
    const UNAUTHORIZED = 1;
    const IDLE = 2;
    const MATCHING = 3;
    const PLAYING = 4;

    /**
     * @return bool
     */
    function isNull() {
        return $this->getValue() == self::NULL;
    }

    /**
     * @return bool
     */
    function isUnauthorized() {
        return $this->getValue() == self::UNAUTHORIZED;
    }

    /**
     * @return bool
     */
    function isInRoom() {
        return in_array($this->getValue(), [self::UNAUTHORIZED, self::IDLE, self::MATCHING, self::PLAYING]);
    }

    /**
     * @return bool
     */
    function isIdle() {
        return $this->getValue() == self::IDLE;
    }

    /**
     * @return bool
     */
    function isMatching() {
        return $this->getValue() == self::MATCHING;
    }

    /**
     * @return bool
     */
    function isPlaying() {
        return $this->getValue() == self::PLAYING;
    }
}