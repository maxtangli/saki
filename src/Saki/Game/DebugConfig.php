<?php

namespace Saki\Game;

/**
 * @package Saki\Game
 */
class DebugConfig {
    private $enableDecider;
    private $skipTrivialPass;

    function __construct() {
        $this->enableDecider = true;
        $this->skipTrivialPass = true;
    }

    /**
     * @return bool
     */
    function isEnableDecider() {
        return $this->enableDecider;
    }

    /**
     * @param bool $enableDecider
     * @return $this
     */
    function setEnableDecider($enableDecider) {
        $this->enableDecider = $enableDecider;
        return $this;
    }

    /**
     * @return bool
     */
    function isSkipTrivialPass() {
        return $this->skipTrivialPass;
    }

    /**
     * @param bool $skipTrivialPass
     * @return $this
     */
    function setSkipTrivialPass($skipTrivialPass) {
        $this->skipTrivialPass = $skipTrivialPass;
        return $this;
    }
}