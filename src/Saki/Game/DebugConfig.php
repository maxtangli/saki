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
     * @return bool
     */
    function isSkipTrivialPass() {
        return $this->skipTrivialPass;
    }

    /**
     * @param bool $skipTrivialPass
     */
    function enableDecider(bool $skipTrivialPass) {
        $this->enableDecider = true;
        $this->skipTrivialPass = $skipTrivialPass;
    }

    function disableDecider() {
        $this->enableDecider = false;
        $this->skipTrivialPass = false;
    }

    /**
     * @param bool $skipTrivialPass
     * @return $this
     */
    function setSkipTrivialPass(bool $skipTrivialPass) {
        $this->skipTrivialPass = $skipTrivialPass;
        return $this;
    }
}