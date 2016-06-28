<?php
namespace Saki\Phase;

use Saki\Game\Claim;
use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Target;
use Saki\Game\TargetType;

/**
 * @package Saki\Phase
 */
class PrivatePhaseState extends PhaseState {
    private $actor;
    private $shouldDraw;
    private $claim;
    private $target;

    /**
     * @param SeatWind $actor
     * @param bool $shouldDraw
     * @param Claim|null $claim
     * @param Target|null $target
     */
    function __construct(SeatWind $actor, bool $shouldDraw, Claim $claim = null, Target $target = null) {
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

    //region PhaseState impl
    function getPhase() {
        return Phase::createPrivate();
    }

    function getDefaultNextState(Round $round) {
        return new PublicPhaseState();
    }

    function enter(Round $round) {
        $actor = $this->getActor();

        $area = $round->getArea($actor);

        $round->toSeatWind($actor);

        if ($this->shouldDraw()) {
            $newTile = $round->getWall()
                ->draw();
            $newTarget = new Target($newTile, TargetType::create(TargetType::DRAW), $actor);
            $round->getTargetHolder()
                ->setTarget($newTarget);
        }

        if ($this->claim !== null) {
            $target = $this->target ?? $round->getOpenHistory()->getLastOpen()->toTarget();
            $round->getTargetHolder()
                ->setTarget($target);
            $this->claim->apply($area);
        }
    }

    function leave(Round $round) {

        $round->getTargetHolder()
            ->setTarget(Target::createNull());
    }
    //endregion
}