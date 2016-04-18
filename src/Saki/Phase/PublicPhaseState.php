<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;

/**
 * @package Saki\Phase
 */
class PublicPhaseState extends PhaseState {
    private $robQuad;
    private $postLeave;

    function __construct() {
        $this->robQuad = false;
        $this->postLeave = function () {
        };
    }

    /**
     * @return bool
     */
    function isRobQuad() {
        return $this->robQuad;
    }

    /**
     * @param bool $robQuad
     */
    function setRobQuad(bool $robQuad) {
        $this->robQuad = $robQuad;
    }

    /**
     * @return \Closure
     */
    function getPostLeave() {
        return $this->postLeave;
    }

    /**
     * @param callable $postLeave
     */
    function setPostLeave(callable $postLeave) {
        $this->postLeave = $postLeave;
    }

    /**
     * @param Round $round
     */
    protected function handleDraw(Round $round) {
        $drawAnalyzer = $round->getGameData()->getDrawAnalyzer();
        $drawOrFalse = $drawAnalyzer->analyzeDrawOrFalse($round);
        if ($drawOrFalse !== false) {
            $drawResult = $drawOrFalse->getResult($round);
            $this->setCustomNextState(new OverPhaseState($drawResult));
        } else {
            // do nothing
        }
    }

    //region PhaseState impl
    function getPhase() {
        return Phase::createPublic();
    }

    function getDefaultNextState(Round $round) {
        $nextActor = $round->getAreas()->getTurn()->getSeatWind()->toNext(); // todo simplify
        $shouldDrawTile = true;
        return new PrivatePhaseState($nextActor, $shouldDrawTile);
    }

    function enter(Round $round) {
        // do nothing
    }

    function leave(Round $round) {
        $this->handleDraw($round);
        call_user_func($this->getPostLeave());
    }
    //endregion
}