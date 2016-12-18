<?php
namespace Saki\Game;

use Saki\Command\Command;
use Saki\Command\CommandProcessor;
use Saki\Command\CommandSet;
use Saki\Command\PlayerCommand;
use Saki\Game\Meld\MeldList;
use Saki\Game\Phase\InitPhaseState;
use Saki\Game\Phase\OverPhaseState;
use Saki\Game\Phase\PhaseState;
use Saki\Game\Phase\PrivatePhaseState;
use Saki\Game\Phase\PublicPhaseState;
use Saki\Game\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\WinReport;
use Saki\Win\WinTarget;

/** todo simplify roll(),debugInit(),toNextPhase()
 * @package Saki\Game
 */
class Round {
    // immutable
    private $rule;
    // variable
    private $processor;
    private $prevailing;
    /**
     * An ArrayList of Area, same size with PlayerList, order by ascend initial SeatWind.
     * @var ArrayList
     */
    private $areaList;
    private $pointHolder;
    private $riichiHolder;
    // round variable
    private $wall;
    private $turn;
    private $openHistory;
    private $claimHistory;
    private $targetHolder;
    /** @var PhaseState */
    private $phaseState;
    // todo remove temp debug
    public $enableDecider = false;

    function __construct() {
        $rule = new Rule();

        // immutable
        $this->rule = $rule;

        // variable
        $this->processor = new CommandProcessor($this, CommandSet::createStandard());
        $this->prevailing = Prevailing::createFirst($rule->getPrevailingContext());
        $this->riichiHolder = new RiichiHolder($rule->getPlayerType());
        $this->pointHolder = new PointHolder($rule->getScoreStrategy()->getPointSetting());

        // round variable
        $this->wall = new Wall($rule->getTileSet(), $rule->getPlayerType());
        $this->turn = Turn::createFirst();
        $this->openHistory = new OpenHistory();
        $this->claimHistory = new ClaimHistory();
        $this->targetHolder = new TargetHolder();

        // variable
        $toArea = function (SeatWind $initialSeatWind) {
            return new Area($initialSeatWind, $this);
        };
        $this->areaList = $rule->getPlayerType()->getSeatWindList($toArea);
        $this->deal();

        // to private phase
        $this->phaseState = new InitPhaseState($this);
        $this->toNextPhase();
    }

    /**
     * @return string
     */
    function __toString() {
        return 'Round._toString(): todo.';
    }

    /**
     * @param bool $keepDealer
     * @param bool $isWin
     */
    function roll(bool $keepDealer, bool $isWin = false) {
        // variable
        $this->processor->init();
        $this->prevailing = $this->prevailing->toRolled($keepDealer);
        $roll = function (Area $area) use ($keepDealer) {
            $area->roll($area->getSeatWind()->toRolled($keepDealer));
        };
        $this->areaList->walk($roll);
        // $this->pointHolder no change
        $this->riichiHolder->roll($isWin);

        // round variable
        $this->wall->init();
        $this->turn = Turn::createFirst();
        $this->openHistory->reset();
        $this->claimHistory->reset();
        $this->targetHolder->init();
        $this->deal();

        // to private phase
        $this->phaseState = new InitPhaseState($this);
        $this->toNextPhase();
    }

    /**
     * @param PrevailingStatus $prevailingStatus
     */
    function debugInit(PrevailingStatus $prevailingStatus) {
        $nextDealerInitialSeatWind = $prevailingStatus->getInitialSeatWindOfDealer();
        $nextDealerArea = $this->getInitialSeatWindArea($nextDealerInitialSeatWind);
        $nextDealerSeatWind = $nextDealerArea->getSeatWind();

        // variable
        $this->processor->init();
        $this->prevailing = $this->prevailing->toDebugInitialized($prevailingStatus);
        $this->areaList->walk(function (Area $area) use ($nextDealerSeatWind) {
            $area->debugInit($area->getSeatWind()->toNextSelf($nextDealerSeatWind));
        });
        $this->pointHolder->init();
        $this->riichiHolder->init();

        // round variable
        $this->wall->init();
        $this->turn = Turn::createFirst();
        $this->openHistory->reset();
        $this->claimHistory->reset();
        $this->targetHolder->init();
        $this->deal();

        // to private phase
        $this->phaseState = new InitPhaseState($this);
        $this->toNextPhase();
    }

    private function deal() {
        $dealResult = $this->getWall()->getDealResult();
        $acceptDeal = function (Area $area) use ($dealResult) {
            $initialTiles = $dealResult[$area->getSeatWind()->__toString()];
            $newHand = new Hand(new TileList($initialTiles), new MeldList(), Target::createNull());
            $area->setHand($newHand);
        };
        $this->areaList->walk($acceptDeal);
    }


    /**
     * @param SeatWind $initialSeatWind
     * @return Area
     */
    private function getInitialSeatWindArea(SeatWind $initialSeatWind) {
        $isInitialSeatWind = function (Area $area) use ($initialSeatWind) {
            return $area->getInitialSeatWind() == $initialSeatWind;
        };
        return $this->areaList->getSingle($isInitialSeatWind);
    }

    /**
     * @return Rule
     */
    function getRule() {
        return $this->rule;
    }

    /**
     * @return CommandProcessor
     */
    function getProcessor() {
        return $this->processor;
    }

    /**
     * Sugar method.
     * @param array ...$scripts
     */
    function process(... $scripts) {
        $this->getProcessor()->process(... $scripts);
    }

    /**
     * @return Prevailing
     */
    function getPrevailing() {
        return $this->prevailing;
    }

    /**
     * @return ArrayList
     */
    function getAreaList() {
        return $this->areaList;
    }

    /**
     * @param SeatWind $seatWind
     * @return Area
     */
    function getArea(SeatWind $seatWind) {
        $isSeatWind = function (Area $area) use ($seatWind) {
            return $area->getSeatWind() == $seatWind;
        };
        return $this->areaList->getSingle($isSeatWind);
    }

    /**
     * @return Area
     */
    function getDealerArea() {
        return $this->getArea(SeatWind::createEast());
    }

    /**
     * @return Area
     */
    function getCurrentArea() {
        return $this->getArea($this->getCurrentSeatWind());
    }

    /**
     * @return PointHolder
     */
    function getPointHolder() {
        return $this->pointHolder;
    }

    /**
     * @return RiichiHolder
     */
    function getRiichiHolder() {
        return $this->riichiHolder;
    }

    /**
     * @return Wall
     */
    function getWall() {
        return $this->wall;
    }

    /**
     * @return Turn
     */
    function getTurn() {
        return $this->turn;
    }

    /**
     * @return PhaseState|PrivatePhaseState|PublicPhaseState|OverPhaseState
     */
    function getPhaseState() {
        return $this->phaseState;
    }

    /**
     * @return Phase
     */
    function getPhase() {
        return $this->getPhaseState()->getPhase();
    }

    /**
     * @return SeatWind
     */
    function getCurrentSeatWind() {
        return $this->getTurn()->getSeatWind();
    }

    /**
     * @param SeatWind[] $excludes
     * @return SeatWind[]
     */
    function getOtherSeatWinds(array $excludes) {
        return SeatWind::createList($this->areaList->count())
            ->remove($excludes)
            ->toArray();
    }

    /**
     * Roll to $seatWind.
     * - If $seatWind is not current, handle CircleCount update.
     * - Do nothing otherwise.
     * @param SeatWind $seatWind
     */
    function toSeatWind(SeatWind $seatWind) {
        $this->turn = $this->turn->toNextSeatWind($seatWind);
    }

    /**
     * @return TargetHolder
     */
    function getTargetHolder() {
        return $this->targetHolder;
    }

    /**
     * @return OpenHistory
     */
    function getOpenHistory() {
        return $this->openHistory;
    }

    /**
     * @return ClaimHistory
     */
    function getClaimHistory() {
        return $this->claimHistory;
    }

    /**
     * @param SeatWind $actor
     * @return WinReport
     */
    function getWinReport(SeatWind $actor) {
        // WinTarget will assert valid player
        return $this->getRule()->getWinAnalyzer()
            ->analyze(new WinTarget($this, $actor));
    }

    /**
     * @param PhaseState|null $customPhaseState
     */
    function toNextPhase(PhaseState $customPhaseState = null) {
        if ($customPhaseState !== null) {
            $this->phaseState->setCustomNextState($customPhaseState);
        }

        $this->phaseState->leave();
        $this->phaseState = $this->phaseState->getNextState();
        $this->phaseState->enter();
    }

    /**
     * @param SeatWind $seatWind
     * @return bool
     */
    function isFirstTurnAndNoClaim(SeatWind $seatWind) {
        $fromTurn = new Turn(1, $seatWind);
        return $this->getTurn()->isFirstCircle()
            && !$this->getClaimHistory()->hasClaim($fromTurn);
    }
}