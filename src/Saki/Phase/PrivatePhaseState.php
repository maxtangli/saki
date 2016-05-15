<?php
namespace Saki\Phase;

use Saki\Game\Claim;
use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * @package Saki\Phase
 */
class PrivatePhaseState extends PhaseState {
    private $actor;
    private $shouldDrawTile;
    private $isCurrent;
    private $postEnter; // todo into Claim
    
    function __construct(SeatWind $actor, bool $shouldDrawTile, bool $isCurrent = false, $postEnter = null) {
        $this->actor = $actor;
        $this->shouldDrawTile = $shouldDrawTile;
        $this->isCurrent = $isCurrent;
        $this->postEnter = $postEnter;
    }

    /**
     * @return SeatWind
     */
    function getActor() {
        return $this->actor;
    }

    /**
     * @return bool
     */
    function shouldDrawTile() {
        return $this->shouldDrawTile;
    }

    /**
     * @return bool
     */
    function isCurrent() {
        return $this->isCurrent;
    }
    
    //region PhaseState impl
    function getPhase() {
        return Phase::createPrivate();
    }

    function getDefaultNextState(Round $round) {
        return new PublicPhaseState();
    }

    function enter(Round $round) {
        $actor = $this->getActor();
        $area = $round->getAreas()->getArea($actor);

        $round->getAreas()->toOrKeepSeatWind($actor);

        if ($this->shouldDrawTile()) {
            $area->draw();
        }
        
        if ($this->postEnter !== null) {
            if ($this->postEnter instanceof Claim) {
                $claim = $this->postEnter;
                $claim->apply($area);
            } else {
                call_user_func($this->postEnter);
            }
        }
    }

    function leave(Round $round) {
        // do nothing
    }
    //endregion
}