<?php
namespace Saki\Yaku;

use Saki\Util\Enum;

class YakuAnalyzerResult {
    /**
     * @var WinState
     */
    private $winState;

    /**
     * @var YakuList
     */
    private $yakuList;

    function __construct($winState, $yakuList) {
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