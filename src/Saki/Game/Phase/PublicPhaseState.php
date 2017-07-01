<?php
namespace Saki\Game\Phase;

use Saki\Command\BufferedCommandDecider;
use Saki\Command\CommandDecider;
use Saki\Command\MockCommandDecider;
use Saki\Game\Claim;
use Saki\Game\Phase;
use Saki\Game\Riichi;
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
        $phase = new self($round);
        $phase->allowClaim = false;
        $phase->isRobbing = true;
        $phase->setCustomNextState(
            new PrivatePhaseState($round, $actor, false, $claim, $target)
        );
        return $phase;
    }

    /**
     * @param Round $round
     * @param Riichi $riichi
     * @return PublicPhaseState
     */
    static function createRiichi(Round $round, Riichi $riichi) {
        $phase = new self($round);
        $phase->riichi = $riichi;
        return $phase;
    }

    private $decider;
    private $allowClaim;
    private $isRobbing;
    /** @var Riichi */
    private $riichi;

    /**
     * @param Round $round
     */
    function __construct(Round $round) {
        parent::__construct($round);
        $this->decider = null;
        // robbing fields
        $this->allowClaim = true;
        $this->isRobbing = false;
        // riichi fields
        $this->riichi = null;
    }

    /**
     * @return CommandDecider
     */
    function getCommandDecider() {
        $round = $this->getRound();
        if (is_null($this->decider)) {
            $this->decider = $round->getDebugConfig()->isEnableDecider()
                ? new BufferedCommandDecider($round->getRule()->getPlayerType(), $round->getProcessor()->getParser())
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
    private function handleDraw() {
        $round = $this->getRound();
        $drawAnalyzer = $round->getRule()->getDrawAnalyzer();
        $drawOrFalse = $drawAnalyzer->analyzeDrawOrFalse($round);
        if ($drawOrFalse !== false) {
            $drawResult = $drawOrFalse->getResult($round);
            $this->setCustomNextState(
                new OverPhaseState($round, $drawResult)
            );
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
        $nextActor = $round->getTurnHolder()->getTurn()
            ->getSeatWind()->toNext();
        $shouldDrawTile = true;
        return new PrivatePhaseState($round, $nextActor, $shouldDrawTile);
    }

    function enter() {
        $round = $this->getRound();

        // set target
        $target = $round->getTurnHolder()->getOpenHistory()
            ->getLastOpen()->toTarget();
        $round->getTargetHolder()->setTarget($target);
    }

    function leave() {
        $round = $this->getRound();
        $isOver = $this->getNextState()->getPhase()->isOver();

        // riichi is handled before handleDraw() to support FourRiichiDraw
        if (isset($this->riichi) && !$isOver) {
            $this->riichi->postApply();
        }

        // handle draw
        $this->handleDraw();

        // clear target
        if (!$isOver) {
            $round->getTargetHolder()->setTarget(Target::createNull());
        }
    }
    //endregion
}

