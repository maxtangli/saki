<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * @package Saki\Phase
 */
class PublicPhaseState extends PhaseState {
    /**
     * @param SeatWind $actor
     * @param callable $postEnter
     * @return PublicPhaseState
     */
    static function createRobbing(SeatWind $actor, callable $postEnter) {
        $phase = new self();
        $phase->isRonOnly = true;
        $phase->setCustomNextState(
            new PrivatePhaseState($actor, false, true, $postEnter)
        );
        return $phase;
    }
    
    private $isRonOnly;
    
    function __construct() {
        $this->isRonOnly = false;
    }
    
    /**
     * @return bool
     */
    function isRonOnly() {
        return $this->isRonOnly;
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
        $nextActor = $round->getAreas()->getTurn()->getSeatWind()->toNext();
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

