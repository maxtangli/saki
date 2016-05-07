<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;

/**
 * @package Saki\Phase
 */
class PublicPhaseState extends PhaseState {
    private $justAfterKong;

    function __construct() {
        $this->justAfterKong = false;
    }

    /**
     * @return bool
     */
    function isJustAfterKong() {
        return $this->justAfterKong;
    }

    function setJustAfterKong() {
        $this->justAfterKong = true;
    }

    /**
     * @return bool
     */
    function isRonOnly() {
        return false;
    }

    /**
     * @param Round $round
     * @return bool
     */
    protected function handleDraw(Round $round) {
        $drawAnalyzer = $round->getGameData()->getDrawAnalyzer();
        $drawOrFalse = $drawAnalyzer->analyzeDrawOrFalse($round);
        if ($drawOrFalse !== false) {
            $drawResult = $drawOrFalse->getResult($round);
            $this->setCustomNextState(new OverPhaseState($drawResult));
            return true;
        } else {
            // do nothing
            return false;
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
    }
    //endregion
}

