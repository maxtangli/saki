<?php
namespace Saki\Win;

use Saki\Util\Singleton;

class WaitingTileAnalyzer extends Singleton {
    private $winAnalyzer;

    function __construct(WinAnalyzer $winAnalyzer) {
        $this->winAnalyzer = $winAnalyzer;
    }

    /**
     * 18-tiles-style target
     * @param WinTarget $target
     * @return WaitingTileList
     */
    function analyzePrivatePhaseWaitingList(WinTarget $target) {
        // todo
    }

    /**
     * 17-tiles-style target
     * @param WinTarget $target
     * @return WaitingTileList
     */
    function analyzePublicPhaseWaitingList(WinTarget $target) {
        if ($target->getStubWinTile() !== null) {
            throw new \InvalidArgumentException("not implemented");
        }

        if (!$target->isPubicPhase()) {
            throw new \InvalidArgumentException("should be public phase");
        }

        $winAnalyzer = $this->winAnalyzer;
        $waitingList = new WaitingTileList([]);
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