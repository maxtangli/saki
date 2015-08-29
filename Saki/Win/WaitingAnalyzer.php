<?php
namespace Saki\Win;

use Saki\Game\RoundPhase;
use Saki\Tile\TileSortedList;
use Saki\Util\Singleton;

class WaitingAnalyzer extends Singleton {
    private $winAnalyzer;

    function __construct(WinAnalyzer $winAnalyzer) {
        $this->winAnalyzer = $winAnalyzer;
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
        foreach($uniqueHandTiles as $discardedTile) {
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
        $futureTiles = $target->getTileSet()->getUniqueTiles();
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
}