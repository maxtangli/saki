<?php
namespace Saki\Win;

use Saki\Game\Result\ScoreLevel;
use Saki\Game\Result\ScoreTable;
use Saki\Yaku\YakuList;

class WinAnalyzerResult {
    /**
     * @var WinState
     */
    private $winState;

    /**
     * @var YakuList
     */
    private $yakuList;
    private $fuCount;

    function __construct(WinState $winState, YakuList $yakuList, $fuCount) {
        $this->winState = $winState;
        $this->yakuList = $yakuList;
        $this->fuCount = $fuCount;
    }

    function getWinState() {
        return $this->winState;
    }

    function getYakuList() {
        return $this->yakuList;
    }

    function getFuCount() {
        return $this->fuCount;
    }

    function getFanCount() {
        return $this->getYakuList()->getFanCount();
    }

    function getScoreLevel() {
        return ScoreLevel::fromFanAndFuCount($this->getFanCount(), $this->getFuCount());
    }

    function getScoreItem() {
        return ScoreTable::getInstance()->getScoreItem($this->getFanCount(), $this->getFuCount());
    }
}