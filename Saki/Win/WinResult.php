<?php
namespace Saki\Win;

use Saki\Tile\TileSortedList;
use Saki\Win\Yaku\YakuList;

class WinResult extends WinSubResult {
    function __construct(WinState $winState, YakuList $yakuList, $fuCount) {
        parent::__construct($winState, $yakuList, $fuCount);
    }
}