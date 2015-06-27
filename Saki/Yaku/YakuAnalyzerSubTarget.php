<?php
namespace Saki\Yaku;

use Saki\Game\PlayerArea;
use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Tile;

class YakuAnalyzerSubTarget extends YakuAnalyzerTarget {
    private $handMeldList;

    function __construct(MeldList $handMeldList, PlayerArea $playerArea) {
        parent::__construct($playerArea);
        $this->handMeldList = $handMeldList;
    }

    function getHandMeldList() {
        return $this->handMeldList;
    }

    function getAllMeldList() {
        $allMeldList = MeldList::fromString('');
        $allMeldList->push($this->getHandMeldList()->toArray());
        $allMeldList->push($this->getDeclaredMeldList()->toArray());
        return $allMeldList;
    }

    /**
     * 門前清
     */
    function isConcealed() {
        return count($this->getDeclaredMeldList())==0;
    }

    function is4WinSetAnd1Pair() {
        $meldList = $this->getAllMeldList();
        $winSetList = $meldList->getFilteredMeldList(function(Meld $meld){return $meld->isWinSet();});
        $pairList = $meldList->getFilteredMeldList(function(Meld $meld){return $meld->isPair();});
        return count($winSetList) == 4 && count($pairList) == 1;
    }

    function is4RunAnd1Pair() {
        $meldList = $this->getAllMeldList();
        $runList = $meldList->getFilteredMeldList(function(Meld $meld){return $meld->isRun();});
        $pairList = $meldList->getFilteredMeldList(function(Meld $meld){return $meld->isPair();});
        return count($runList) == 4 && count($pairList) == 1;
    }

    function isAllSuit() {
        return $this->getHandTileSortedList()->isAll(function(Tile $tile){return $tile->isSuit();});
    }

    function isReach() {

    }

    /**
     * 単騎待ち 1 -> 11
     */
    function isOnePairWaiting() {

    }

    /**
     * 嵌張待ち 7 9 -> 789
     */
    function isMiddleRunWaiting() {
    }

    /**
     * 辺張待ち 89 -> 789
     */
    function isOneSideRunWaiting() {

    }

    /**
     * 両面待ち 78 -> 678 or 789
     */
    function isTwoSidesRunWaiting() {
        $runMeldList = $this->getAllMeldList()->getFilteredMeldList(function(Meld $meld){return $meld->isRun();});
        $winTile = $this->getWinTile();
        return $runMeldList->isAny(function(Meld $runMeld)use($winTile){return $runMeld->getFirst()==$winTile || $runMeld->getLast()==$winTile;});
    }
}