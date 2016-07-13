<?php
namespace Saki\Phase;

use Saki\Game\Claim;
use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Target;

/**
 * @package Saki\Phase
 */
class PublicPhaseState extends PhaseState {
    /**
     * @param SeatWind $actor
     * @param Claim $claim
     * @param Target $target
     * @return PublicPhaseState
     */
    static function createRobbing(SeatWind $actor, Claim $claim, Target $target) {
        $phase = new self();
        $phase->isRonOnly = true;
        $phase->setCustomNextState(
            new PrivatePhaseState($actor, false, $claim, $target)
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
     * @return bool
     */
    function isRobbing() {
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
        $nextActor = $round->getTurn()->getSeatWind()->toNext();
        $shouldDrawTile = true;
        return new PrivatePhaseState($nextActor, $shouldDrawTile);
    }

    function enter(Round $round) {
        // do nothing

        $target = $round->getOpenHistory()->getLastOpen()->toTarget();
        $round->getTargetHolder()->setTarget($target);
    }

    function leave(Round $round) {
        $this->handleDraw($round);

        if (!$this->getNextState($round)->getPhase()->isOver()) {
            $round->getTargetHolder()
                ->setTarget(Target::createNull());
        }
    }
    //endregion
}

