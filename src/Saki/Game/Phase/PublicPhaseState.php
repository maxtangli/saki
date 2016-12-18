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
     * @param Round $round
     * @param SeatWind $actor
     * @param Claim $claim
     * @param Target $target
     * @return PublicPhaseState
     */
    static function createRobbing(Round $round, SeatWind $actor, Claim $claim, Target $target) {
        list($allowClaim, $isRobbing) = [false, true];
        $phase = new self($round, $allowClaim, $isRobbing);
        $phase->setCustomNextState(
            new PrivatePhaseState($round, $actor, false, $claim, $target)
        );
        return $phase;
    }

    /**
     * @param Round $round
     * @return PublicPhaseState
     */
    static function create(Round $round) {
        list($allowClaim, $isRobbing) = [true, false];
        return new self($round, $allowClaim, $isRobbing);
    }

    private $decider;
    private $allowClaim;
    private $isRobbing;

    /**
     * @param Round $round
     * @param bool $allowClaim
     * @param bool $isRobbing
     */
    function __construct(Round $round, bool $allowClaim, bool $isRobbing) {
        parent::__construct($round);
        $this->decider = null;
        $this->allowClaim = $allowClaim;
        $this->isRobbing = $isRobbing;
    }

    /**
     * @return CommandDecider
     */
    function getCommandDecider() {
        $round = $this->getRound();
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
     * @return bool
     */
    protected function handleDraw() {
        $round = $this->getRound();
        $drawAnalyzer = $round->getRule()->getDrawAnalyzer();
        $drawOrFalse = $drawAnalyzer->analyzeDrawOrFalse($round);
        if ($drawOrFalse !== false) {
            $drawResult = $drawOrFalse->getResult($round);
            $this->setCustomNextState(new OverPhaseState($round, $drawResult));
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

    function getDefaultNextState() {
        $round = $this->getRound();
        $nextActor = $round->getTurnHolder()->getTurn()->getSeatWind()->toNext();
        $shouldDrawTile = true;
        return new PrivatePhaseState($round, $nextActor, $shouldDrawTile);
    }

    function enter() {
        $round = $this->getRound();

        // set target
        $target = $round->getTurnHolder()->getOpenHistory()->getLastOpen()->toTarget();
        $round->getTargetHolder()->setTarget($target);

        // bottom of sea not allow claim
        if ($round->getWall()->getDrawWall()->isEmpty()) {
            $this->allowClaim = false;
        }
    }

    function leave() {
        $round = $this->getRound();

        $this->handleDraw();

        if (!$this->getNextState()->getPhase()->isOver()) {
            $round->getTargetHolder()
                ->setTarget(Target::createNull());
        }
    }
    //endregion
}

