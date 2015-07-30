<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Game\RoundData;

class WinAnalyzerSubTarget extends WinAnalyzerTarget {
    private $handMeldList;

    function __construct(MeldList $handMeldList, Player $player, RoundData $roundData) {
        parent::__construct($player, $roundData);
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

    function is4WinSetAnd1Pair() {
        $meldList = $this->getAllMeldList();
        $winSetList = $meldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isWinSet();
        });
        $pairList = $meldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isPair();
        });
        return count($winSetList) == 4 && count($pairList) == 1;
    }

    function is4RunAnd1Pair() {
        $meldList = $this->getAllMeldList();
        $runList = $meldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isRun();
        });
        $pairList = $meldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isPair();
        });
        return count($runList) == 4 && count($pairList) == 1;
    }

    function is4TripleOrQuadAnd1Pair($concealed = null) {
        $meldList = $this->getAllMeldList();
        $tripleList = $meldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isTripleOrQuad();
        });
        $pairList = $meldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isPair();
        });
        $matchConcealed = $concealed === null || $tripleList->isAll(function (Meld $meld) use ($concealed) {
                return $meld->isConcealed() == $concealed;
            });
        return count($tripleList) == 4 && count($pairList) == 1 && $matchConcealed;
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
        $runMeldList = $this->getAllMeldList()->getFilteredMeldList(function (Meld $meld) {
            return $meld->isRun();
        });
        $winTile = $this->getWinTile();
        return $runMeldList->isAny(function (Meld $runMeld) use ($winTile) {
            return $runMeld->getFirst() == $winTile || $runMeld->getLast() == $winTile;
        });
    }

    function getFuCount() {
        return 20; // todo
    }
}