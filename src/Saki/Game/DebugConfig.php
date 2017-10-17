<?php

namespace Saki\Game;

/**
 * @package Saki\Game
 */
class DebugConfig {
    private $enableDecider;
    private $skipTrivialPass;
    private $forceGameOver;

    function __construct() {
        $this->reset();
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s%s%s',
            $this->isEnableDecider() ? 'enableDecider' : '',
            $this->isSkipTrivialPass() ? 'skipTrivialPass' : '',
            $this->isForceGameOver() ? 'isForceGameOver' : '');
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
     * @return bool
     */
    function isForceGameOver() {
        return $this->forceGameOver;
    }

    /**
     * @return $this
     */
    function reset() {
        $this->enableDecider = true;
        $this->skipTrivialPass = true;
        $this->forceGameOver = false;
        return $this;
    }

    /**
     * @param bool $skipTrivialPass
     * @return $this
     */
    function enableDecider(bool $skipTrivialPass) {
        $this->enableDecider = true;
        $this->skipTrivialPass = $skipTrivialPass;
        return $this;
    }

    /**
     * @return $this
     */
    function disableDecider() {
        $this->enableDecider = false;
        $this->skipTrivialPass = false;
        return $this;
    }

    /**
     * @param bool $skipTrivialPass
     * @return $this
     */
    function setSkipTrivialPass(bool $skipTrivialPass) {
        $this->skipTrivialPass = $skipTrivialPass;
        return $this;
    }

    /**
     * @param bool $forceGameOver
     * @return $this
     */
    function setForceGameOver(bool $forceGameOver) {
        $this->forceGameOver = $forceGameOver;
        return $this;
    }
}