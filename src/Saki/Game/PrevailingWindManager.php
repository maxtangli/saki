<?php
namespace Saki\Game;

class PrevailingWindManager {
    // immutable
    private $playerCount;
    private $gameLengthType;
    // game variable
    private $prevailingWind; // [東] 1 局
    private $prevailingWindTurn; // 東 [1] 局 // todo remove duplicate with TileArea.SeatWind
    private $seatWindTurn; // [0] 本場

    function __construct(int $playerCount, GameLengthType $gameLengthType) {
        $this->playerCount = $playerCount;
        $this->gameLengthType = $gameLengthType;

        $this->prevailingWind = PrevailingWind::createEast();
        $this->prevailingWindTurn = 1;
        $this->seatWindTurn = 0;
    }

    function reset(bool $keepDealer) {
        if ($keepDealer) {
            // keep prevailingWind
            // keep prevailingWindTurn
            $this->seatWindTurn += 1;
        } else { // not keep dealer
            if ($this->isCurrentPrevailingWindLastTurn()) {
                $this->prevailingWind = $this->prevailingWind->toNext();
                $this->prevailingWindTurn = 1;
                $this->seatWindTurn = 0;
            } else {
                // keep prevailingWind
                $this->prevailingWindTurn += 1;
                $this->seatWindTurn = 0;
            }
        }
    }

    function debugReset(PrevailingWind $prevailingWind = null, $prevailingWindTurn = null, $seatWindTurn = null) {
        $this->prevailingWind = $prevailingWind ?? PrevailingWind::createEast();
        $this->prevailingWindTurn = $prevailingWindTurn ?? 1;
        $this->seatWindTurn = $seatWindTurn ?? 0;
    }

    /**
     * @return int
     */
    protected function getPlayerCount() {
        return $this->playerCount;
    }

    /**
     * @return GameLengthType
     */
    protected function getGameLengthType() {
        return $this->gameLengthType;
    }

    /**
     * @return PrevailingWind
     */
    function getPrevailingWind() {
        return $this->prevailingWind;
    }

    /**
     * @return int
     */
    function getPrevailingWindTurn() {
        return $this->prevailingWindTurn;
    }

    /**
     * @return int
     */
    function getSeatWindTurn() {
        return $this->seatWindTurn;
    }

    function isCurrentPrevailingWindLastTurn() {
        return $this->getPrevailingWindTurn() == $this->getPlayerCount();
    }

    // 游戏长度定下的最后一局,分不出胜负时进入延长局。
    function isLastOrExtraRound() {
        $isLastPrevailingWind = $this->getPrevailingWind() == $this->getGameLengthType()->getLastPrevailingWind();
        $isLastRound = $isLastPrevailingWind && $this->isCurrentPrevailingWindLastTurn();
        return $isLastRound || $this->isExtraRound();
    }

    // 延长局？
    function isExtraRound() {
        return !$this->getGameLengthType()->inLength($this->getPrevailingWind());
    }

    // 最终局，完局后游戏结束
    function isFinalRound() {
        // 最多延长一个场风
        if ($this->getGameLengthType()->getValue() == GameLengthType::FULL) {
            throw new \BadMethodCallException('todo');
        }

        $final = $this->getGameLengthType()->getLastPrevailingWind()->toNext();
        $isFinal = $this->getPrevailingWind() == $final;
        return $isFinal && $this->isCurrentPrevailingWindLastTurn();
    }
}