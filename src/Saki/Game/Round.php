<?php

namespace Saki\Game;

use Saki\Command\CommandProcessor;
use Saki\Command\CommandSet;
use Saki\Game\Phase\InitPhaseState;
use Saki\Game\Phase\OverPhaseState;
use Saki\Game\Phase\PhaseState;
use Saki\Game\Phase\PrivatePhaseState;
use Saki\Game\Phase\PublicPhaseState;
use Saki\Win\WinReport;
use Saki\Win\WinTarget;

/**
 * @package Saki\Game
 */
class Round {
    // immutable config
    private $rule;
    // mutable config
    private $debugConfig;
    // variable
    private $processor;
    private $prevailing;
    private $pointHolder;
    private $riichiHolder;
    // round variable
    private $turnHolder;
    private $targetHolder;
    private $wall;
    private $areaList;
    /** @var PhaseState */
    private $phaseState;

    function __construct(Rule $customRule = null) {
        $rule = $customRule ?? new Rule();

        // immutable config
        $this->rule = $rule;

        // mutable config
        $this->debugConfig = new DebugConfig();

        // variable
        $this->processor = new CommandProcessor($this, CommandSet::createStandard());
        $this->prevailing = Prevailing::createFirst($rule->getPrevailingContext());
        $this->riichiHolder = new RiichiHolder($rule->getPlayerType());
        $this->pointHolder = new PointHolder($rule->getScoreStrategy()->getPointSetting());

        // round variable
        $this->turnHolder = new TurnHolder();
        $this->targetHolder = new TargetHolder();
        $this->wall = new Wall($rule->getTileSet(), $rule->getPlayerType());
        $this->areaList = AreaList::create($this);
        $this->deal();

        // to private phase
        $this->phaseState = new InitPhaseState($this);
        $this->toNextPhase();
    }

    /**
     * @return string
     */
    function __toString() {
        return 'Round.';
    }

    /**
     * @param bool $keepDealer
     * @param bool $isWin
     * @param int $count
     */
    function roll(bool $keepDealer, bool $isWin = false, int $count = 1) {
        $todo = $count;
        while ($todo--) {
            // variable
            $this->processor->init();
            $this->prevailing = $this->prevailing->toRolled($keepDealer);
            // $this->pointHolder no change
            $this->riichiHolder->roll($isWin);

            // round variable
            $this->turnHolder->init();
            $this->targetHolder->init();
            $this->wall->init();
            $this->areaList->roll($keepDealer);
            $this->deal();

            // to private phase
            $this->phaseState = new InitPhaseState($this);
            $this->toNextPhase();
        }
    }

    /**
     * @param PrevailingStatus $prevailingStatus
     */
    function debugInit(PrevailingStatus $prevailingStatus) {
        // variable
        $this->processor->init();
        $this->prevailing = $this->prevailing->toDebugInitialized($prevailingStatus);
        $this->pointHolder->init();
        $this->riichiHolder->init();

        // round variable
        $this->turnHolder->init();
        $this->targetHolder->init();
        $this->wall->init();
        $this->areaList->debugInit($prevailingStatus);
        $this->deal();

        // to private phase
        $this->phaseState = new InitPhaseState($this);
        $this->toNextPhase();
    }

    private function deal() {
        $this->areaList->deal($this->getWall()->getDealResult());
    }

    /**
     * @return Rule
     */
    function getRule() {
        return $this->rule;
    }

    /**
     * @return DebugConfig
     */
    function getDebugConfig() {
        return $this->debugConfig;
    }

    /**
     * @return CommandProcessor
     */
    function getProcessor() {
        return $this->processor;
    }

    /**
     * @return Prevailing
     */
    function getPrevailing() {
        return $this->prevailing;
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
     * @return TurnHolder
     */
    function getTurnHolder() {
        return $this->turnHolder;
    }

    /**
     * @return TargetHolder
     */
    function getTargetHolder() {
        return $this->targetHolder;
    }

    /**
     * @return Wall
     */
    function getWall() {
        return $this->wall;
    }

    /**
     * @return AreaList
     */
    function getAreaList() {
        return $this->areaList;
    }

    /**
     * @return PhaseState|PrivatePhaseState|PublicPhaseState|OverPhaseState
     */
    function getPhaseState() {
        return $this->phaseState;
    }

    /**
     * Sugar method.
     * @param SeatWind $seatWind
     * @return Area
     */
    function getArea(SeatWind $seatWind) {
        return $this->getAreaList()->getArea($seatWind);
    }

    /**
     * Sugar method.
     * @return Phase
     */
    function getPhase() {
        return $this->getPhaseState()->getPhase();
    }

    /**
     * Sugar method.
     * @return SeatWind
     */
    function getCurrentSeatWind() {
        return $this->getTurnHolder()->getTurn()->getSeatWind();
    }

    /**
     * sugar method.
     * @return \Saki\Util\ArrayList
     */
    function getNotCurrentSeatWindList() {
        return $this->getRule()->getPlayerType()
            ->getSeatWindList(null, [$this->getCurrentSeatWind()]);
    }

    /**
     * Sugar method.
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
     * Sugar method.
     * @param array ...$scripts
     */
    function process(... $scripts) {
        $this->getProcessor()->process(... $scripts);
    }
}