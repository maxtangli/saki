<?php
namespace Saki\Win\Fu;

use Saki\Meld\Meld;
use Saki\Win\Waiting\WaitingType;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\YakuItemList;

class FuTarget {
    /** @var WinSubTarget */
    private $subTarget;
    private $yakuList;
    private $waitingType;

    function __construct(WinSubTarget $subTarget, YakuItemList $yakuList, WaitingType $waitingType) {
        $this->subTarget = $subTarget;
        $this->yakuList = $yakuList;
        $this->waitingType = $waitingType;
    }

    function getYakuList() {
        return $this->yakuList;
    }

    function getWaitingType() {
        return $this->waitingType;
    }

    function getAllMeldList() {
        return $this->subTarget->getAllMeldList();
    }

    function getPairMeld() {
        return $this->getAllMeldList()->getCopy()->where(function (Meld $meld) {
            return $meld->isPair();
        })->getFirst();
    }

    function isSelfPhase() {
        return $this->subTarget->isPrivatePhase();
    }

    function isConcealed() {
        return $this->subTarget->isConcealed();
    }

    function getSeatWindTile() {
        return $this->subTarget->getSeatWindTile();
    }

    function getPrevailingWindTile() {
        return $this->subTarget->getPrevailingWindTile();
    }
}