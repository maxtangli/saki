<?php
namespace Saki\Meld;

use Saki\Meld\MeldType;
use Saki;
use Saki\Util\ArrayReadonlyWrapper;

class MeldList extends ArrayReadonlyWrapper {
    function __construct(array $melds) {
        parent::__construct($melds);
    }

    function getFilteredMelds(MeldType $meldType) {

    }

    function plusKong(Tile $tile) {
        $melds = $this->getInnerArray();
    }
}