<?php

namespace Saki\Game;

use Saki\Meld\MeldList;
use Saki\Phase\PhaseState;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;
use Saki\Win\WinTarget;

/**
 * Provide collaborate operations on 1 Wall and 2-4 Area.
 * @package Saki\Game
 */
class Areas {
    // immutable
    private $gameData;
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
    private $phaseState;
    private $openHistory;
    private $claimHistory;
    private $targetHolder;

    /**
     * @param GameData $gameData
     * @param PlayerList $playerList
     */
    function __construct(GameData $gameData, PlayerList $playerList) {
        // immutable
        $this->gameData = $gameData;

        // variable
        $this->prevailingCurrent = PrevailingCurrent::createFirst($gameData->getPrevailingContext());
        $this->riichiHolder = new RiichiHolder($playerList->getPlayerType());
        $this->pointHolder = new PointHolder($gameData->getScoreStrategy()->getPointSetting());

        // round variable
        $this->wall = new Wall($gameData->getTileSet());
        $this->turn = Turn::createFirst();
        $this->openHistory = new OpenHistory();
        $this->claimHistory = new ClaimHistory();
        $this->targetHolder = new TargetHolder();

        // variable
        $this->areaList = $playerList->toArrayList(function (Player $player) {
            return new Area($player, $this);
        });
    }

    /**
     * @param bool $keepDealer
     * @param bool $isWin
     */
    function roll(bool $keepDealer, bool $isWin) {
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
        $this->openHistory->reset();
        $this->claimHistory->reset();
        $this->targetHolder->init();
    }

    function debugInit(PrevailingStatus $PrevailingStatus) {
        // variable
        $this->prevailingCurrent = $this->prevailingCurrent->toDebugInited($PrevailingStatus);
        $nextDealerInitialSeatWind = $this->getInitialSeatWindArea(
            $PrevailingStatus->getInitialSeatWindOfDealer()
        )->getPlayer()->getInitialSeatWind();
        $nextDealerArea = $this->getInitialSeatWindArea($nextDealerInitialSeatWind);
        $nextDealerSeatWind = $nextDealerArea->getSeatWind(); // todo simpler logic?
        $this->areaList->walk(function (Area $area) use ($nextDealerSeatWind) {
            $area->debugInit($area->getSeatWind()->toNextSelf($nextDealerSeatWind));
        });
        $this->pointHolder->init();
        $this->riichiHolder->init();

        // round variable
        $this->wall->reset(true);
        $this->turn = Turn::createFirst();
        $this->openHistory->reset();
        $this->claimHistory->reset();
        $this->targetHolder->init();
    }

    /**
     * @return GameData
     */
    function getGameData() {
        return $this->gameData;
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
     * @param PhaseState $phaseState
     */
    function setPhaseState(PhaseState $phaseState) {
        $this->phaseState = $phaseState;
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
}