<?php
namespace Saki\Win;

use Saki\Tile\TileSortedList;
use Saki\Win\Yaku\YakuItemList;

class WinResult extends WinSubResult {
    function __construct(WinState $winState, YakuItemList $yakuList, $fuCount) {
        parent::__construct($winState, $yakuList, $fuCount);
    }
}