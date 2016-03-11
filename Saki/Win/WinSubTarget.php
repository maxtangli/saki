<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;

class WinSubTarget extends WinTarget {
    private $handMeldList;

    function __construct(MeldList $handMeldList, Player $player, Round $round) {
        parent::__construct($player, $round);
        $this->handMeldList = $handMeldList;
    }

    function getHandMeldList() {
        return $this->handMeldList;
    }

    function getAllMeldList() {
        $allMeldList = MeldList::fromString('');
        $allMeldList->push($this->getHandMeldList()->toArray());
        $allMeldList->push($this->getDeclaredMeldList()->toArray());
        return $allMeldList;
    }
}