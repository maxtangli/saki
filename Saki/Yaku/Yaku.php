<?php

namespace Saki\Yaku;

use Saki\Util\Singleton;

abstract class Yaku extends Singleton {
    final function getFansu($isExposed) {
            return $isExposed ? $this->getExposedFansu() : $this->getConcealedFansu();
    }

    abstract function getConcealedFansu();
    abstract function getExposedFansu();

    final function requireConcealed() {
        return $this->getExposedFansu() == 0;
    }

    abstract function require4WinSetAnd1Pair();

    final function existIn(YakuAnalyzerSubTarget $subTarget) {
        return (!$this->requireConcealed() || $subTarget->isConcealed())
            && (!$this->require4WinSetAnd1Pair() || $subTarget->is4WinSetAnd1Pair())
            && $this->existInImpl($subTarget);
    }

    abstract protected function existInImpl(YakuAnalyzerSubTarget $subTarget);

    /**
     * @return Yaku
     */
    static function getInstance() {
        return parent::getInstance();
    }


}

/*

// melds-concerned
abstract class MixedOutsideHandYaku extends Yaku {
    function getFansu() {
        return 2; // -> 1
    }

    function existIn($round, $player) {
        // allMelds = playerArea.declaredMeldList merge playerArea.onHandTiles.analyzeMeldList
        //  .filter RunMeldType. count > 1
        //  .all is19orHonor
    }
}

abstract class PureOutsideHandYaku extends Yaku {
    function getFansu() {
        return 3; // -> 2
    }

    function existIn($round, $player) {
        // allMelds = playerArea.declaredMeldList merge playerArea.onHandTiles.analyzeMeldList
        //  .filter RunMeldType. count > 1
        //  .all is19
    }
}

abstract class FullStraightRunsYaku extends Yaku {
    function getFansu() {
        return 2; // -> 1
    }

    function existIn($round, $player) {
        // playerArea.declaredMeldList merge playerArea.onHandTiles.analyzeMeldList
        // melds.filter RunMeldType.group by numberAlign.exist 123/456/789
    }
}

abstract class SevenPairsYaku extends Yaku {
    function getFansu() {
        return 2;
    }

    function existIn($round, $player) {
        // playerArea.onHandTiles.analyzeMeldList
        //  .filter EyeMeldType.count is 7
    }
}

abstract class ThreeColorRunsYaku extends Yaku {
    function getFansu() {
        return 2; // -> 1
    }

    function existIn($round, $player) {
        // playerArea.declaredMeldList merge playerArea.onHandTiles.analyzeMeldList
        // melds.filter RunMeldType.group by numberAlign.any count($member)==3
    }
}

abstract class AllTriplesYaku extends Yaku {
    function getFansu() {
        return 2;
    }

    function existIn($round, $player) {
        // playerArea.declaredMeldList merge playerArea.onHandTiles.analyzeMeldList
        // melds.map TripleType.all is Triple
    }
}

abstract class ThreeConcealedTriplesYaku extends Yaku {
    function getFansu() {
        return 2;
    }

    function existIn($round, $player) {
        // playerArea.onHandTiles.analyzeMeldList
        // melds.filter TripleMeldType.count > 3
    }
}

abstract class DoubleRunYaku extends Yaku {
    function getFansu() {
        return 1;
    }

    function existIn($round, $player) {
        // playerArea.declaredMeldList.empty
        // playerArea.onHandTiles.analyzeMeldList
        //  .filter Sequence.group by equality.where count($member) == 2. self count==1
    }
}

abstract class ValueTilesYaku extends Yaku { // Red White Green SelfWind RoundWind
    function getFansu() {
        return 1;
    }

    function existIn($round, $player) {
        // targetMeldTypes = Pong/Kang
        // playerArea.onHandTiles.analyzeMeldList merge playerArea.declaredMeldList
        // exist MeldType
    }

    abstract function getTargetTile();
}

// tiles-concerned

abstract class HalfFlushYaku extends Yaku {
    function getFansu() {
        return 3; // ->2
    }

    function existIn($round, $player) {
        // playerArea.onHandTileList.tiles merge playerArea.declaredMeldList.tiles
        // tiles.filter isSuit.map tileType.all same
        // tiles.filter isHonor.not empty
    }
}

abstract class FullFlushYaku extends Yaku {
    function getFansu() {
        return 6; // ->5
    }

    function existIn($round, $player) {
        // playerArea.onHandTileList.tiles merge playerArea.declaredMeldList.tiles
        // tiles.filter isSuit.map tileType.all same
        // tiles.filter isHonor.empty
    }
}

abstract class AllSimplesYaku extends Yaku {
    function getFansu() {
        return 1;
    }

    function existIn($round, $player) {
        // playerArea.onHandTileList. all is simpleTile
    }
}

// tiles-not-concerned

class ReachYaku extends Yaku {
    function getFansu() {
        return 1;
    }

    function existIn($round, $player) {
        // $round->playerArea->isReach
    }
}

abstract class ConcealedSelfDrawYaku extends Yaku {
    function getFansu() {
        return 1;
    }

    function existIn($round, $player) {
        // playerArea.declaredMeldList.empty
        // winInfo.winningTileGetWay is SelfDraw
    }
}

abstract class FirstTurnWinYaku extends Yaku {
    function getFansu() {
        return 1;
    }

    function existIn($round, $player) {
        // winInfo.winningTurn = playerArea.reachTurn + 1
    }
}

abstract class FinalTileWinYaku extends Yaku {
    function getFansu() {
        return 1;
    }

    function existIn($round, $player) {
        // wall.remainTileCount = 0
    }
}

*/