<?php
namespace Saki\Phase;

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

    /**
     * PrivatePhaseState constructor.
     * @param SeatWind $actor
     * @param bool $shouldDrawTile
     * @param bool $isCurrent
     */
    function __construct(SeatWind $actor, bool $shouldDrawTile, bool $isCurrent = false) {
        $this->actor = $actor;
        $this->shouldDrawTile = $shouldDrawTile;
        $this->isCurrent = $isCurrent;
    }

    function getActor() {
        return $this->actor;
    }

    function shouldDrawTile() {
        return $this->shouldDrawTile;
    }

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

        $round->getAreas()->toSeatWind($actor);

        if ($this->shouldDrawTile()) {
            $round->getAreas()->draw($actor);
        }
    }

    function leave(Round $round) {
        // do nothing
    }
    //endregion
}