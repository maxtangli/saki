<?php
namespace Saki\Game;

class RoundWindData {
    // immutable
    private $playerCount;
    private $gameLengthType;
    // game variable
    private $roundWind; // [東] 1 局
    private $roundWindTurn; // 東 [1] 局 // todo remove duplicate with TileArea.PlayerWind
    private $selfWindTurn; // [0] 本場

    function __construct(int $playerCount, GameLengthType $gameLengthType) {
        $this->playerCount = $playerCount;
        $this->gameLengthType = $gameLengthType;

        $this->roundWind = RoundWind::createEast();
        $this->roundWindTurn = 1;
        $this->selfWindTurn = 0;
    }

    function reset(bool $keepDealer) {
        if ($keepDealer) {
            // keep roundWind
            // keep roundWindTurn
            $this->selfWindTurn += 1;
        } else { // not keep dealer
            if ($this->isCurrentRoundWindLastTurn()) {
                $this->roundWind = $this->roundWind->toNext();
                $this->roundWindTurn = 1;
                $this->selfWindTurn = 0;
            } else {
                // keep roundWind
                $this->roundWindTurn += 1;
                $this->selfWindTurn = 0;
            }
        }
    }

    function debugReset(RoundWind $roundWind = null, $roundWindTurn = null, $selfWindTurn = null) {
        $this->roundWind = $roundWind ?? RoundWind::createEast();
        $this->roundWindTurn = $roundWindTurn ?? 1;
        $this->selfWindTurn = $selfWindTurn ?? 0;
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
     * @return RoundWind
     */
    function getRoundWind() {
        return $this->roundWind;
    }

    /**
     * @return int
     */
    function getRoundWindTurn() {
        return $this->roundWindTurn;
    }

    /**
     * @return int
     */
    function getSelfWindTurn() {
        return $this->selfWindTurn;
    }

    function isCurrentRoundWindLastTurn() {
        return $this->getRoundWindTurn() == $this->getPlayerCount();
    }

    // 游戏长度定下的最后一局,分不出胜负时进入延长局。
    function isLastOrExtraRound() {
        $isLastRoundWind = $this->getRoundWind() == $this->getGameLengthType()->getLastRoundWind();
        $isLastRound = $isLastRoundWind && $this->isCurrentRoundWindLastTurn();
        return $isLastRound || $this->isExtraRound();
    }

    // 延长局？
    function isExtraRound() {
        return !$this->getGameLengthType()->inLength($this->getRoundWind());
    }

    // 最终局，完局后游戏结束
    function isFinalRound() {
        // 最多延长一个场风
        if ($this->getGameLengthType()->getValue() == GameLengthType::FULL) {
            throw new \BadMethodCallException('todo');
        }

        $final = $this->getGameLengthType()->getLastRoundWind()->toNext();
        $isFinal = $this->getRoundWind() == $final;
        return $isFinal && $this->isCurrentRoundWindLastTurn();
    }
}