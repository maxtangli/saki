<?php
namespace Saki\Game;

use Saki\Command\CommandParser;
use Saki\Command\CommandProcessor;
use Saki\Command\CommandSet;
use Saki\Meld\MeldList;
use Saki\Phase\NullPhaseState;
use Saki\Phase\OverPhaseState;
use Saki\Phase\PhaseState;
use Saki\Phase\PrivatePhaseState;
use Saki\Phase\PublicPhaseState;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\WinTarget;

/**
 * @package Saki\Game
 */
class Round {
    // immutable
    private $gameData;
    private $processor;
    // variable
    private $prevailingCurrent;
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
        $gameData = new GameData();
        $playerList = new PlayerList($gameData->getPlayerType(), $gameData->getScoreStrategy()->getPointSetting()->getInitialPoint());

        // immutable
        $this->gameData = $gameData;
        $this->processor = new CommandProcessor(
            new CommandParser($this, CommandSet::createStandard())
        );

        // variable
        $this->prevailingCurrent = PrevailingCurrent::createFirst($gameData->getPrevailingContext());
        $this->riichiHolder = new RiichiHolder($playerList->getPlayerType());
        $this->pointHolder = new PointHolder($gameData->getScoreStrategy()->getPointSetting());

        // round variable
        $this->wall = new Wall($gameData->getTileSet());
        $this->turn = Turn::createFirst();
        $this->phaseState = new NullPhaseState();
        $this->openHistory = new OpenHistory();
        $this->claimHistory = new ClaimHistory();
        $this->targetHolder = new TargetHolder();

        // variable
        $this->areaList = $playerList->toArrayList(function (Player $player) {
            return new Area($player, $this);
        });

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    /**
     * @param bool $keepDealer
     * @param bool $isWin
     */
    function roll(bool $keepDealer, bool $isWin = false) {
        // variable
        $this->prevailingCurrent = $this->prevailingCurrent->toRolled($keepDealer);
        $this->areaList->walk(function (Area $area) use ($keepDealer) {
            $area->roll($area->getSeatWind()->toRolled($keepDealer));
        });
        // $this->pointHolder no change
        $this->riichiHolder->roll($isWin);

        // round variable
        $this->wall->reset(true);
        $this->turn = Turn::createFirst();
        $this->phaseState = new NullPhaseState();
        $this->openHistory->reset();
        $this->claimHistory->reset();
        $this->targetHolder->init();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    /**
     * @param PrevailingStatus $prevailingStatus
     */
    function debugInit(PrevailingStatus $prevailingStatus) {
        $nextDealerInitialSeatWind = $this->getInitialSeatWindArea(
            $prevailingStatus->getInitialSeatWindOfDealer()
        )->getPlayer()->getInitialSeatWind();
        $nextDealerArea = $this->getInitialSeatWindArea($nextDealerInitialSeatWind);
        $nextDealerSeatWind = $nextDealerArea->getSeatWind(); // todo simpler logic?

        // variable
        $this->prevailingCurrent = $this->prevailingCurrent->toDebugInitialized($prevailingStatus);
        $this->areaList->walk(function (Area $area) use ($nextDealerSeatWind) {
            $area->debugInit($area->getSeatWind()->toNextSelf($nextDealerSeatWind));
        });
        $this->pointHolder->init();
        $this->riichiHolder->init();

        // round variable
        $this->wall->reset(true);
        $this->turn = Turn::createFirst();
        $this->phaseState = new NullPhaseState();
        $this->openHistory->reset();
        $this->claimHistory->reset();
        $this->targetHolder->init();

        // to private phase
        $this->toNextPhase();
        $this->toNextPhase(); // todo better way?
    }

    /**
     * @return GameData
     */
    function getGameData() {
        return $this->gameData;
    }

    /**
     * @return CommandProcessor
     */
    function getProcessor() {
        return $this->processor;
    }

    /**
     * @param array ...$scripts
     */
    function process(... $scripts) {
        $this->getProcessor()->process(... $scripts);
    }

    /**
     * @return PrevailingCurrent
     */
    function getPrevailingCurrent() {
        return $this->prevailingCurrent;
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
        return $this->areaList->getSingle(function (Area $area) use ($seatWind) {
            return $area->getSeatWind() == $seatWind;
        });
    }

    /**
     * @param SeatWind $initialSeatWind
     * @return Area
     */
    function getInitialSeatWindArea(SeatWind $initialSeatWind) {
        return $this->areaList->getSingle(function (Area $area) use ($initialSeatWind) {
            return $area->getPlayer()->getInitialSeatWind() == $initialSeatWind;
        });
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
            ->remove($excludes)->toArray();
    }

    /**
     * Roll to $seatWind.
     * If $seatWind is not current, handle CircleCount update.
     * Do nothing otherwise.
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
     * @return \Saki\Win\WinReport
     */
    function getWinReport(SeatWind $actor) {
        // WinTarget will assert valid player
        return $this->getGameData()->getWinAnalyzer()
            ->analyze(new WinTarget($actor, $this));
    }

    function deal() {
        $playerType = PlayerType::create($this->areaList->count());
        $deal = $this->getWall()->deal($playerType);
        $this->areaList->walk(function (Area $area) use ($deal) {
            $initialTiles = $deal[$area->getSeatWind()->__toString()];
            $newHand = new Hand(new TileList($initialTiles), new MeldList(), Target::createNull());
            $area->setHand($newHand);
        });
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