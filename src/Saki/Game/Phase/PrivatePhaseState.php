<?php
namespace Saki\Game\Phase;

use Saki\Game\Claim;
use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Target;
use Saki\Game\TargetType;

/**
 * @package Saki\Game\Phase
 */
class PrivatePhaseState extends PhaseState {
    private $actor;
    private $shouldDraw;
    private $claim;
    private $target;

    /**
     * @param Round $round
     * @param SeatWind $actor
     * @param bool $shouldDraw
     * @param Claim|null $claim
     * @param Target|null $target
     */
    function __construct(Round $round, SeatWind $actor, bool $shouldDraw, Claim $claim = null, Target $target = null) {
        if ($shouldDraw && $claim) {
            throw new \InvalidArgumentException();
        }
        parent::__construct($round);

        $this->actor = $actor;
        $this->shouldDraw = $shouldDraw;
        $this->claim = $claim;
        $this->target = $target;
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
    function shouldDraw() {
        return $this->shouldDraw;
    }

    /**
     * @return bool
     */
    function hasClaim() {
        return $this->claim !== null;
    }

    /**
     * @return Claim
     */
    function getClaim() {
        if (!$this->hasClaim()) {
            throw new \BadMethodCallException();
        }
        return $this->claim;
    }

    /**
     * @return boolean
     */
    function allowClaim() {
        return true;
    }

    //region PhaseState impl
    function getPhase() {
        return Phase::createPrivate();
    }

    function getDefaultNextState() {
        return PublicPhaseState::create($this->getRound());
    }

    function enter() {
        $round = $this->getRound();
        $actor = $this->getActor();
        $round->getTurnHolder()->toSeatWind($actor);

        if ($this->shouldDraw()) {
            $newTile = $round->getWall()->getDrawWall()
                ->outNext();
            $newTarget = new Target($newTile, TargetType::create(TargetType::DRAW), $actor);
            $round->getTargetHolder()
                ->setTarget($newTarget);
        }

        if ($this->hasClaim()) {
            $target = $this->target ?? $round->getTurnHolder()->getOpenHistory()->getLastOpen()->toTarget();
            $round->getTargetHolder()
                ->setTarget($target);
            $this->claim->apply();
        }
    }

    function leave() {
        if (!$this->getNextState()->getPhase()->isOver()) {
            $this->getRound()->getTargetHolder()
                ->setTarget(Target::createNull());
        }
    }
    //endregion
}