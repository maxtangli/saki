<?php
namespace Saki\Game;

use Saki\Command\Command;
use Saki\Command\CommandProcessor;
use Saki\Command\CommandSet;
use Saki\Command\InvalidCommandException;
use Saki\Command\PlayerCommand;
use Saki\Meld\MeldList;
use Saki\Phase\NullPhaseState;
use Saki\Phase\OverPhaseState;
use Saki\Phase\PhaseState;
use Saki\Phase\PrivatePhaseState;
use Saki\Phase\PublicPhaseState;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\WinReport;
use Saki\Win\WinTarget;

/** todo simplify roll(),debugInit(),toNextPhase()
 * @package Saki\Game
 */
class Round {
    // immutable
    private $rule;
    private $processor;
    // variable
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
    /** @var PhaseState */
    private $phaseState;
    private $openHistory;
    private $claimHistory;
    private $targetHolder;

    function __construct() {
        $rule = new Rule();

        // immutable
        $this->rule = $rule;
        $this->processor = new CommandProcessor($this, CommandSet::createStandard());

        // variable
        $this->prevailing = Prevailing::createFirst($rule->getPrevailingContext());
        $this->riichiHolder = new RiichiHolder($rule->getPlayerType());
        $this->pointHolder = new PointHolder($rule->getScoreStrategy()->getPointSetting());

        // round variable
        $this->wall = new Wall($rule->getTileSet());
        $this->turn = Turn::createFirst();
        $this->phaseState = new NullPhaseState();
        $this->openHistory = new OpenHistory();
        $this->claimHistory = new ClaimHistory();
        $this->targetHolder = new TargetHolder();

        // variable
        $toArea = function (SeatWind $initialSeatWind) {
            return new Area($initialSeatWind, $this);
        };
        $this->areaList = $rule->getPlayerType()->getSeatWindList($toArea);

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    /**
     * @return string
     */
    function __toString() {
        return RoundSerializer::create()->toString($this);
    }

    /**
     * @param SeatWind $viewer
     * @return array
     */
    function toJson(SeatWind $viewer = null) {
        return RoundSerializer::create()->toJson($this, $viewer);
    }

    /**
     * @param bool $keepDealer
     * @param bool $isWin
     */
    function roll(bool $keepDealer, bool $isWin = false) {
        // variable
        $this->prevailing = $this->prevailing->toRolled($keepDealer);
        $roll = function (Area $area) use ($keepDealer) {
            $area->roll($area->getSeatWind()->toRolled($keepDealer));
        };
        $this->areaList->walk($roll);
        // $this->pointHolder no change
        $this->riichiHolder->roll($isWin);

        // round variable
        $this->wall->reset();
        $this->turn = Turn::createFirst();
        $this->phaseState = new NullPhaseState();
        $this->openHistory->reset();
        $this->claimHistory->reset();
        $this->targetHolder->init();

        // to private phase
        $this->toNextPhase();
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
        $this->prevailing = $this->prevailing->toDebugInitialized($prevailingStatus);
        $this->areaList->walk(function (Area $area) use ($nextDealerSeatWind) {
            $area->debugInit($area->getSeatWind()->toNextSelf($nextDealerSeatWind));
        });
        $this->pointHolder->init();
        $this->riichiHolder->init();

        // round variable
        $this->wall->reset();
        $this->turn = Turn::createFirst();
        $this->phaseState = new NullPhaseState();
        $this->openHistory->reset();
        $this->claimHistory->reset();
        $this->targetHolder->init();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase();
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
     * @param string $scriptLine
     * @return Command|PlayerCommand
     */
    function parse(string $scriptLine) {
        return $this->getProcessor()->getParser()
            ->parseLine($scriptLine);
    }

    /**
     * @param array ...$scripts
     */
    function process(... $scripts) {
        $this->getProcessor()->process(... $scripts);
    }

    /**
     * @param string $scriptLine
     * @param SeatWind|null $requireActor
     * @return Command
     * @throws InvalidCommandException
     */
    function processLine(string $scriptLine, SeatWind $requireActor = null) {
        $command = $this->getProcessor()->getParser()
            ->parseLine($scriptLine);
        if ($requireActor) {
            if (!$command instanceof PlayerCommand) {
                throw new InvalidCommandException($scriptLine, 'not PlayerCommand.');
            }

            if (!$command->getActor() == $requireActor) {
                throw new InvalidCommandException($scriptLine, 'not actor.');
            }
        }
        $command->execute();
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
     * @param SeatWind $initialSeatWind
     * @return Area
     */
    function getInitialSeatWindArea(SeatWind $initialSeatWind) {
        $isInitialSeatWind = function (Area $area) use ($initialSeatWind) {
            return $area->getInitialSeatWind() == $initialSeatWind;
        };
        return $this->areaList->getSingle($isInitialSeatWind);
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

    function deal() {
        $playerType = PlayerType::create($this->areaList->count());
        $deal = $this->getWall()->deal($playerType);
        $acceptDeal = function (Area $area) use ($deal) {
            $initialTiles = $deal[$area->getSeatWind()->__toString()];
            $newHand = new Hand(new TileList($initialTiles), new MeldList(), Target::createNull());
            $area->setHand($newHand);
        };
        $this->areaList->walk($acceptDeal);
    }

    /**
     * @param PhaseState|null $customPhaseState
     */
    function toNextPhase(PhaseState $customPhaseState = null) {
        if ($customPhaseState !== null) {
            $this->phaseState->setCustomNextState($customPhaseState);
        }

        $this->phaseState->leave($this);
        $this->phaseState = $this->phaseState->getNextState($this);
        $this->phaseState->enter($this);
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
    
    /**
     * @return bool
     */
    function isGameOver() {
        /** @var OverPhaseState $phaseState */
        $phaseState = $this->phaseState;
        return $phaseState->getPhase()->isOver()
            && $phaseState->isGameOver($this);
    }
    
    // todo move into OverPhase?
    function toNextRound() {
        /** @var OverPhaseState $overPhaseState */
        $overPhaseState = $this->phaseState;
        if (!$overPhaseState->getPhase()->isOver()) {
            throw new \InvalidArgumentException('Not over phase.');
        }

        if ($overPhaseState->isGameOver($this)) {
            throw new \InvalidArgumentException('Game is over.');
        }

        $keepDealer = $overPhaseState->getResult()->isKeepDealer();
        $isWin = $overPhaseState->getResult()->getResultType()->isWin();
        $this->roll($keepDealer, $isWin);
    }
}