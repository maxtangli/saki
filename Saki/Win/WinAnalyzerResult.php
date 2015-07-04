<?php
namespace Saki\Win;

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

    function __construct(WinState $winState, YakuList $yakuList) {
        $this->winState = $winState;
        $this->yakuList = $yakuList;
    }

    function getWinState() {
        return $this->winState;
    }

    function getYakuList() {
        return $this->yakuList;
    }
}