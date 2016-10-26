<?php
namespace Saki\Win\Fu;

use Saki\Game\Meld\Meld;
use Saki\Game\Meld\MeldList;
use Saki\Game\Tile\Tile;
use Saki\Win\Waiting\WaitingType;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\YakuItemList;

/**
 * @package Saki\Win\Fu
 */
class FuTarget {
    /** @var WinSubTarget */
    private $subTarget;
    private $yakuList;
    private $waitingType;

    /**
     * @param WinSubTarget $subTarget
     * @param YakuItemList $yakuList
     * @param WaitingType $waitingType
     */
    function __construct(WinSubTarget $subTarget, YakuItemList $yakuList, WaitingType $waitingType) {
        $this->subTarget = $subTarget;
        $this->yakuList = $yakuList;
        $this->waitingType = $waitingType;
    }

    /**
     * @return YakuItemList
     */
    function getYakuList() {
        return $this->yakuList;
    }

    /**
     * @return WaitingType
     */
    function getWaitingType() {
        return $this->waitingType;
    }

    /**
     * @return MeldList
     */
    function getAllMeldList() {
        return $this->subTarget->getAllMeldList();
    }

    /**
     * @return Meld
     */
    function getPairMeld() {
        $isPair = function (Meld $meld) {
            return $meld->isPair();
        };
        return $this->getAllMeldList()->getCopy()
            ->where($isPair)
            ->getFirst();
    }

    /**
     * @return bool
     */
    function isSelfPhase() {
        return $this->subTarget->getPhase()->isPrivate();
    }

    /**
     * @return bool
     */
    function isConcealed() {
        return $this->subTarget->getHand()->isConcealed();
    }

    /**
     * @return Tile
     */
    function getSeatWindTile() {
        return $this->subTarget->getActor()->getWindTile();
    }

    /**
     * @return Tile
     */
    function getPrevailingWindTile() {
        return $this->subTarget->getPrevailingWind()->getWindTile();
    }
}