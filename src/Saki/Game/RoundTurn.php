<?php
namespace Saki\Game;

use Saki\Util\ComparableSequence;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class RoundTurn implements Immutable {
    use ComparableSequence;

    function compareTo($other) {
        /** @var RoundTurn $other */
        $other = $other;
        $globalTurnDiff = $this->globalTurn <=> $other->globalTurn;
        if ($globalTurnDiff != 0) {
            return $globalTurnDiff;
        }

        // todo simplify offset
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
     * Note that here it's more clear to provide a factory method rather than default constructor.
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
}