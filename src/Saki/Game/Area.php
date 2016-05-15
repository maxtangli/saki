<?php
namespace Saki\Game;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Tile\TileList;

/**
 * A roundly reset area own by a player.
 * @package Saki\Game
 */
class Area {
    // immutable
    private $player;
    // shared variable
    private $areas;
    // game variable
    private $seatWind;
    private $seatWindTurn;
    private $point;
    // round variable
    private $handHolder;

    /**
     * @param Player $player
     * @param Areas $areas
     */
    function __construct(Player $player, Areas $areas) {
        // immutable
        $this->player = $player;
        // shared variable
        $this->areas = $areas;
        // round variable: new
        $this->handHolder = new HandHolder($areas->getTargetHolder(), $player->getInitialSeatWind());
        // game variable, round variable
        $this->resetImpl($player->getInitialSeatWind(), 0, $player->getInitialPoint());
    }

    /**
     * @param SeatWind $seatWind
     */
    function roll(SeatWind $seatWind) {
        $keepDealer = $this->seatWind->isDealer() && $seatWind->isDealer();
        $seatWindTurn = $keepDealer ? $this->seatWindTurn + 1 : 0;
        $this->resetImpl($seatWind, $seatWindTurn, $this->point);
    }

    /**
     * @param SeatWind $seatWind
     */
    function debugInit(SeatWind $seatWind) {
        $this->resetImpl($seatWind, 0, $this->getPlayer()->getInitialPoint());
    }

    /**
     * @param SeatWind $seatWind
     * @param int $seatWindTurn
     * @param int $point
     */
    protected function resetImpl(SeatWind $seatWind, int $seatWindTurn, int $point) {
        // game variable
        $this->seatWind = $seatWind;
        $this->seatWindTurn = $seatWindTurn;
        $this->point = $point;
        // round variable
        $this->handHolder->init($seatWind);
    }

    /**
     * @return Player
     */
    function getPlayer() {
        return $this->player;
    }

    /**
     * @return Areas
     */
    function getAreas() {
        return $this->areas;
    }

    /**
     * @return SeatWind
     */
    function getSeatWind() {
        return $this->seatWind;
    }

    /**
     * @return int
     */
    function getSeatWindTurn() {
        if (!$this->getSeatWind()->isDealer()) {
            throw new \BadMethodCallException();
        }
        return $this->seatWindTurn;
    }

    /**
     * @return int
     */
    function getPoint() {
        return $this->point;
    }

    /**
     * @param int $point
     */
    function setPoint(int $point) {
        $this->point = $point;
    }

    /**
     * @return Hand
     */
    function getHand() {
        return $this->handHolder->getHand();
    }

    /**
     * @param Hand $hand
     */
    function setHand(Hand $hand) {
        $this->handHolder->setHand($hand);
    }

    // todo remove?
    function debugSet(TileList $public, MeldList $declare = null, Tile $targetTile = null) {
        $newHand = $this->getHand()->toHand($public, $declare, $targetTile);
        $this->setHand($newHand);
    }

    /**
     * @return RiichiStatus
     */
    function getRiichiStatus() {
        return $this->getAreas()->getRiichiHolder()
            ->getRiichiStatus($this->getSeatWind());
    }

    /**
     * @return bool
     */
    function isFirstTurnWin() {
        $riichiStatus = $this->getRiichiStatus();
        $currentTurn = $this->getAreas()->getTurn();

        if (!$riichiStatus->isFirstTurn($currentTurn)) {
            return false;
        }

        $claimHistory = $this->getAreas()->getClaimHistory();
        $noDeclareSinceRiichi = !$claimHistory->hasClaim($riichiStatus->getRiichiTurn());
        return $noDeclareSinceRiichi;
    }

    /**
     * @return TileList
     */
    function getDiscard() {
        return $this->getAreas()->getOpenHistory()
            ->getSelfDiscard($this->getSeatWind());
    }

    //region operations
    function draw() {
        $newTile = $this->getAreas()->getWall()
            ->draw();
        $newTarget = new Target($newTile, TargetType::create(TargetType::DRAW), $this->getSeatWind());
        $newHand = $this->getHand()->toSetTarget($newTarget);
        $this->setHand($newHand);
    }

    // todo remove
    function extendKongAfter() {
        // todo better way?
        $targetTile = $this->getAreas()->getOpenHistory()
            ->getSelfOpen($this->getSeatWind())
            ->getLast();
        $newTarget = new Target($targetTile, TargetType::create(TargetType::KEEP), $this->getSeatWind());
        $this->getAreas()->getTargetHolder()
            ->setTarget($newTarget);

        $fromMelded = new Meld([$targetTile, $targetTile, $targetTile], null, false);
        $claim = Claim::createFromMelded($this->getSeatWind(), $this->getAreas()->getTurn(),
            $targetTile, $fromMelded);

        $claim->apply($this);
    }
    //endregion
}