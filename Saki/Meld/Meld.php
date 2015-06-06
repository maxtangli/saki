<?php
namespace Saki\Meld;

use Saki\TileList;

class Meld extends TileList {
    private $meldType;

    /**
     * @param TileList $tileList
     * @param $meldType
     */
    function __construct(TileList $tileList, $meldType) {;
        parent::__construct($tileList->getInnerArray());
        if (!$meldType->valid($this)) {
            throw new \InvalidArgumentException();
        }
        $this->meldType = $meldType;
    }

    function getMeldType() {
        return $this->meldType;
    }
}

