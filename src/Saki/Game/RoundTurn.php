<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ComparableTimeLine;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class RoundTurn implements Immutable {
    use ComparableTimeLine;

    function compareTo($other) {
        /** @var RoundTurn $other */
        $other = $other;
        $globalTurnDiff = $this->globalTurn <=> $other->globalTurn;
        if ($globalTurnDiff != 0) {
            return $globalTurnDiff;
        }

        // todo
        $selfWindDiff = $this->getPlayerWind()->getWindTile()->getWindOffsetFrom(
            $other->getPlayerWind()->getWindTile()
        );
        return $selfWindDiff;
    }

    /**
     * @param string $s
     * @return RoundTurn
     */
    static function fromString(string $s) {
        $globalTurn = intval(substr($s, 0, strlen($s) - 1));
        $playerWind = PlayerWind::fromString(substr($s, -1));
        return new self($globalTurn, $playerWind);
    }

    /**
     * @return RoundTurn
     */
    static function createFirst() {
        return new self(1, PlayerWind::createEast());
    }

    private $globalTurn;
    private $playerWind;

    /**
     * @param int $globalTurn
     * @param PlayerWind $playerWind
     */
    function __construct(int $globalTurn, PlayerWind $playerWind) {
        $valid = $globalTurn >= 1;
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid argument int $globalTurn[%s], PlayerWind $playerWind[%s].',
                    $globalTurn, $playerWind
                )
            );
        }

        $this->globalTurn = $globalTurn;
        $this->playerWind = $playerWind;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s%s', $this->globalTurn, $this->playerWind);
    }

    /**
     * @return int
     */
    function getGlobalTurn() {
        return $this->globalTurn;
    }

    /**
     * @return PlayerWind
     */
    function getPlayerWind() {
        return $this->playerWind;
    }

    /**
     * @return float
     */
    function getFloatGlobalTurn() {
        return $this->getGlobalTurn() +
        // todo
        0.25 * $this->getPlayerWind()->getWindTile()->getWindOffsetFrom(Tile::fromString('E'));
    }

    /**
     * @param RoundTurn $priorRoundTurn
     * @return float past float global turn in format like 0.25, 0.5, 0.75, 1.0, 1.25 etc.
     */
    function getPastFloatGlobalTurn(RoundTurn $priorRoundTurn) {
        // todo
        $result = $this->getFloatGlobalTurn() - $priorRoundTurn->getFloatGlobalTurn();
        if ($result <= 0) {
            throw new \InvalidArgumentException();
        }
        return $result;
    }
}