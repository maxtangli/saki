<?php
namespace Saki;

use Saki\Meld\EyesMeldType;
use Saki\Meld\SequenceMeldType;
use Saki\TileList;

class PinfuYaku {
    function count(TileList $hand) {
        $aryMelds = $hand->getMeldCompositions([
            new SequenceMeldType(),
            new EyesMeldType()
        ]);
    }
}