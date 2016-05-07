<?php
namespace Saki\Phase;

use Saki\Game\Round;

/**
 * @package Saki\Phase
 */
class RobbingPubicPhaseState extends PublicPhaseState {
    private $postLeave;

    /**
     * @param callable $postLeave
     */
    function __construct(callable $postLeave) {
        $this->postLeave = $postLeave;
        $this->setJustAfterKong();
    }

    //region PublicPhaseState override
    function isRonOnly() {
        return true;
    }

    function getDefaultNextState(Round $round) {
        $current = $round->getAreas()->getCurrentSeatWind();
        return new PrivatePhaseState($current, false, true);
    }

    function setCustomNextState(PhaseState $customNextState) {
        throw new \BadMethodCallException('Forbidden.');
    }

    function leave(Round $round) {
        // do not call parent::leave() to avoid Draw
        call_user_func($this->postLeave);
    }
    //
}