<?php
namespace Saki\Game\Phase;

use Saki\Command\BufferCommandDecider;
use Saki\Command\CommandDecider;
use Saki\Command\MockCommandDecider;
use Saki\Game\Claim;
use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Game\SeatWind;
use Saki\Game\Target;

/**
 * @package Saki\Game\Phase
 */
class PublicPhaseState extends PhaseState {
    /**
     * @param SeatWind $actor
     * @param Claim $claim
     * @param Target $target
     * @return PublicPhaseState
     */
    static function createRobbing(SeatWind $actor, Claim $claim, Target $target) {
        list($allowClaim, $isRobbing) = [false, true];
        $phase = new self($allowClaim, $isRobbing);
        $phase->setCustomNextState(
            new PrivatePhaseState($actor, false, $claim, $target)
        );
        return $phase;
    }

    static function create() {
        list($allowClaim, $isRobbing) = [true, false];
        return new self($allowClaim, $isRobbing);
    }

    private $decider;
    private $allowClaim;
    private $isRobbing;

    /**
     * @param bool $allowClaim
     * @param bool $isRobbing
     */
    private function __construct(bool $allowClaim, bool $isRobbing) {
        $this->decider = null;
        $this->allowClaim = $allowClaim;
        $this->isRobbing = $isRobbing;
    }

    /**
     * @param Round $round
     * @return CommandDecider
     */
    function getCommandDecider(Round $round) {
        if (is_null($this->decider)) {
            $this->decider = $round->enableDecider
                ? new BufferCommandDecider($round->getRule()->getPlayerType(), $round->getProcessor()->getParser())
                : new MockCommandDecider();
        }
        return $this->decider;
    }

    /**
     * @return boolean
     */
    function allowClaim() {
        return $this->allowClaim;
    }

    /**
     * @return bool
     */
    function isRobbing() {
        return $this->isRobbing;
    }

    /**
     * @param Round $round
     * @return bool
     */
    protected function handleDraw(Round $round) {
        $drawAnalyzer = $round->getRule()->getDrawAnalyzer();
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
        // set target
        $target = $round->getOpenHistory()->getLastOpen()->toTarget();
        $round->getTargetHolder()->setTarget($target);

        // bottom of sea not allow claim
        if ($round->getWall()->getDrawWall()->isBottomOfTheSea()) {
            $this->allowClaim = false;
        }
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

