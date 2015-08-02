<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class RoundWindData {
    private $playerCount;
    /**
     * @var GameLengthType
     */
    private $gameLengthType;

    /**
     * @var Tile
     */
    private $roundWind; // [東] 1 局
    private $roundWindTurn; // 東 [1] 局
    private $selfWindTurn; // [0] 本場

    function __construct($playerCount, GameLengthType $gameLengthType) {
        $this->playerCount = $playerCount;
        $this->gameLengthType = $gameLengthType;

        $this->roundWind = Tile::fromString('E');
        $this->roundWindTurn = 1;
        $this->selfWindTurn = 0;
    }

    function reset($keepDealer) {
        if ($keepDealer) {
            $this->selfWindTurn += 1;
        } else {
            if ($this->isCurrentRoundWindLastTurn()) {
                $this->roundWind = $this->roundWind->toNextTile();
                $this->roundWindTurn = 1;
            } else {
                $this->roundWindTurn += 1;
            }
            $this->selfWindTurn = 0;
        }
    }

    function getPlayerCount() {
        return $this->playerCount;
    }

    function getGameLengthType() {
        return $this->gameLengthType;
    }

    function getRoundWind() {
        return $this->roundWind;
    }

    function getRoundWindTurn() {
        return $this->roundWindTurn;
    }

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
        return !$this->getGameLengthType()->isInLengthRoundWind($this->getRoundWind());
    }

    // 最终局，完局后游戏结束
    function isFinalRound() {
        // 最多延长一个场风
        if ($this->getGameLengthType()->getValue() == GameLengthType::FULL) {
            throw new \LogicException('un implemented.');
        }
        $finalRoundWind = $this->getGameLengthType()->getLastRoundWind()->toNextTile();
        $isFinalRoundWind = $this->getRoundWind() == $finalRoundWind;
        return $isFinalRoundWind && $this->isCurrentRoundWindLastTurn();
    }
}