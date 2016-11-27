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
    private $allowClaim;

    /**
     * @param SeatWind $actor
     * @param bool $shouldDraw
     * @param Claim|null $claim
     * @param Target|null $target
     */
    function __construct(SeatWind $actor, bool $shouldDraw, Claim $claim = null, Target $target = null) {
        if ($shouldDraw && $claim) {
            throw new \InvalidArgumentException();
        }

        $this->actor = $actor;
        $this->shouldDraw = $shouldDraw;
        $this->claim = $claim;
        $this->target = $target;
        $this->allowClaim = true;
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
        return $this->allowClaim;
    }

    //region PhaseState impl
    function getPhase() {
        return Phase::createPrivate();
    }

    function getDefaultNextState(Round $round) {
        return PublicPhaseState::create();
    }

    function enter(Round $round) {
        $actor = $this->getActor();
        $area = $round->getArea($actor);
        $round->toSeatWind($actor);

        if ($this->shouldDraw()) {
            $newTile = $round->getWall()->getDrawWall()
                ->draw();
            $newTarget = new Target($newTile, TargetType::create(TargetType::DRAW), $actor);
            $round->getTargetHolder()
                ->setTarget($newTarget);
        }

        if ($this->hasClaim()) {
            $target = $this->target ?? $round->getOpenHistory()->getLastOpen()->toTarget();
            $round->getTargetHolder()
                ->setTarget($target);
            $this->claim->apply($area);
        }
    }

    function leave(Round $round) {
        if (!$this->getNextState($round)->getPhase()->isOver()) {
            $round->getTargetHolder()
                ->setTarget(Target::createNull());
        }
    }
    //endregion
}