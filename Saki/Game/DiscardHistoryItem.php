<?php
namespace Saki\Game;
use Saki\Tile\Tile;
use Saki\Util\Utils;

/**
 * Implementation class
 */
class DiscardHistoryItem {
    private $globalTurn;
    private $selfWind;
    private $discardedTile;

    public function __construct($globalTurn, Tile $selfWind, Tile $discardedTile) {
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
            $selfWindComparator = Utils::getComparatorByBestArray(Tile::getWindTiles());
            $compareResult = $selfWindComparator($this->getSelfWind(), $priorItem->getSelfWind());
            $isLaterWind = $compareResult < 0;
            $isSameWind = $compareResult == 0;
            if ($isLaterWind || $allowSameTurnAndSelfWind && $isSameWind) {
                return true;
            }
        }

        return false;
    }
}