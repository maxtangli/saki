<?php
namespace Saki\Win\Fu;

use Saki\Meld\Meld;
use Saki\Win\WaitingType;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\YakuList;

class FuCountTarget {
    private $subTarget;
    private $yakuList;
    private $waitingType;

    function __construct(WinSubTarget $subTarget, YakuList $yakuList, WaitingType $waitingType) {
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
        return $this->getAllMeldList()->toFilteredMeldList(function (Meld $meld) {
            return $meld->isPair();
        })->getFirst();
    }

    function isSelfPhase() {
        return $this->subTarget->isPrivatePhase();
    }

    function isConcealed() {
        return $this->subTarget->isConcealed();
    }

    function getSelfWind() {
        return $this->subTarget->getSelfWind();
    }

    function getRoundWind() {
        return $this->subTarget->getRoundWind();
    }
}