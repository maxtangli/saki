<?php
namespace Saki\Game;

use Saki\Tile\Tile;

/**
 * Implementation class
 */
class DiscardHistoryItem {
    private $globalTurn;
    private $selfWind;
    private $discardedTile;

    function __construct($globalTurn, Tile $selfWind, Tile $discardedTile) {
        if (!($globalTurn >= 1)) {
            throw new \InvalidArgumentException(
                sprintf('$globalTurn[%s] should >= 1.', $globalTurn)
            );
        }

        if (!$selfWind->getTileType()->isWind()) {
            throw new \InvalidArgumentException();
        }

        $this->globalTurn = $globalTurn;
        $this->selfWind = $selfWind;
        $this->discardedTile = $discardedTile;
    }

    function __toString() {
        return sprintf('turn [%s] player [%s] discarded [%s]',
            $this->getGlobalTurn(), $this->getSelfWind(), $this->getDiscardedTile());
    }

    function getGlobalTurn() {
        return $this->globalTurn;
    }

    function getSelfWind() {
        return $this->selfWind;
    }

    function getDiscardedTile() {
        return $this->discardedTile;
    }

    function validLaterItemOf(DiscardHistoryItem $priorItem, $allowSameTurnAndSelfWind = false) {
        if ($this->getGlobalTurn() > $priorItem->getGlobalTurn()) {
            return true;
        }

        if ($this->getGlobalTurn() == $priorItem->getGlobalTurn()) {
            $windOffset = $this->getSelfWind()->getWindOffset($priorItem->getSelfWind());
            $isLaterWind = $windOffset > 0;
            $isSameWind = $windOffset == 0;
            if ($isLaterWind || $allowSameTurnAndSelfWind && $isSameWind) {
                return true;
            }
        }

        return false;
    }
}