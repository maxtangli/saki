<?php
namespace Saki\Win;

use Saki\Game\RoundPhase;
use Saki\Meld\MeldCompositionsAnalyzer;
use Saki\Meld\MeldTypesFactory;
use Saki\Meld\PairMeldType;
use Saki\Meld\SingleMeldType;
use Saki\Meld\WeakRunMeldType;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;

class WaitingAnalyzer {
    private $winAnalyzer;
    private $meldCompositionsAnalyzer;

    function __construct(WinAnalyzer $winAnalyzer) {
        $this->winAnalyzer = $winAnalyzer;
        $this->meldCompositionsAnalyzer = new MeldCompositionsAnalyzer();
    }

    /**
     * 18-tiles-style target
     * @param WinTarget $target
     * @return FutureWaitingList
     */
    function analyzePrivatePhaseFutureWaitingList(WinTarget $target) {
        if ($target->getStubHandTileList() !== null || $target->getStubRoundPhase() !== null) {
            throw new \InvalidArgumentException('not implemented');
        }

        if (!$target->isPrivatePhase()) {
            throw new \InvalidArgumentException('should be private phase');
        }

        $futureWaitingList = new FutureWaitingList([]);

        $originHandTileList = $target->getHandTileSortedList(false);
        $uniqueHandTiles = array_unique($originHandTileList->toArray());
        $handTileListAfterDiscard = new TileSortedList([]);
        foreach ($uniqueHandTiles as $discardedTile) { // 0.06s a loop
            $handTileListAfterDiscard->setInnerArray($originHandTileList->toArray());
            $handTileListAfterDiscard->removeByValue($discardedTile);

            $target->setStubHandTileList($handTileListAfterDiscard);
            $target->setStubRoundPhase(RoundPhase::getPublicPhaseInstance());

            $waitingTileList = $this->analyzePublicPhaseWaitingTileList($target);
            if ($waitingTileList->count() > 0) {
                $futureWaitingList->push(new FutureWaiting($discardedTile, $waitingTileList));
            }
        }
        $target->setStubHandTileList(null);
        $target->setStubRoundPhase(null);

        return $futureWaitingList;
    }

    /**
     * 17-tiles-style target
     * @param WinTarget $target
     * @return WaitingTileList
     */
    function analyzePublicPhaseWaitingTileList(WinTarget $target) {
        if ($target->getStubWinTile() !== null) {
            throw new \InvalidArgumentException("not implemented");
        }

        if (!$target->isPubicPhase()) {
            throw new \InvalidArgumentException("should be public phase");
        }

        $waitingList = new WaitingTileList([]);

        $winAnalyzer = $this->winAnalyzer;
        $futureTiles = $this->getFutureTiles($target);
        foreach ($futureTiles as $futureTile) {
            $target->setStubWinTile($futureTile);
            $futureWinResult = $winAnalyzer->analyzeTarget($target);
            $winState = $futureWinResult->getWinState();

            // echo $futureTile.' '.$winState. ' '.$target->getAllTileSortedList(). ' '. $target->getWinTile(). "\n"; // debug

            if ($winState->isTrueOrFalseWin()) {
                $remainAmount = $target->getTileRemainAmount($futureTile);
                $waitingList->push(new WaitingTile($futureTile, $winState, $remainAmount));
            }
        }
        $target->setStubWinTile(null);

        return $waitingList;
    }

    function getFutureTiles(WinTarget $target) {
//        return $this->getFutureTilesByTileSet($target);
        return $this->getFutureTilesByWeakMelds($target);
    }

    function getFutureTilesByTileSet(WinTarget $target) { // old slow ver: analyzePrivate() 700ms
        return $target->getTileSet()->getUniqueTiles();
    }

    function getFutureTilesByWeakMelds(WinTarget $target) { // fast ver1: analyzePrivate() 120ms, about 6 times faster
        $handTileList = $target->getHandTileSortedList(false);
        if (!$handTileList->validPublicPhaseCount()) {
            throw new \InvalidArgumentException();
        }

        $meldCompositionAnalyzer = $this->meldCompositionsAnalyzer;
        $meldTypes = MeldTypesFactory::getInstance()->getHandMeldTypes(true);
        $meldLists = $meldCompositionAnalyzer->analyzeMeldCompositions($handTileList, $meldTypes, 1);

        $waitingTileList = new TileSortedList([]);
        foreach ($meldLists as $meldList) {
            $pairMeldList = $meldList->getFilteredTypesMeldList([PairMeldType::getInstance()]);
            $singleOrWeakRunMeldList = $meldList->getFilteredTypesMeldList([SingleMeldType::getInstance(), WeakRunMeldType::getInstance()]);
            if (count($pairMeldList) == 2) {
                $targetMeldList = $pairMeldList;
            } elseif (count($singleOrWeakRunMeldList) == 1) {
                $targetMeldList = $singleOrWeakRunMeldList;
            } else {
                throw new \LogicException(
                    sprintf('Invalid implementation. $meldList[%s]', $meldList)
                );
            }

            foreach ($targetMeldList as $meld) {
                $waitingTiles = $meld->getMeldType()->getWaitingTiles(new TileList($meld->toArray())); // todo simplify
                $waitingTileList->push($waitingTiles);
            }
        }
        $waitingTileList->unique();

        return $waitingTileList->toArray();
    }
}