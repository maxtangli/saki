<?php
namespace Saki\Win;

use Saki\Game\Player;
use Saki\Game\RoundData;
use Saki\Meld\MeldList;

class WinSubTarget extends WinTarget {
    private $handMeldList;

    function __construct(MeldList $handMeldList, Player $player, RoundData $roundData) {
        parent::__construct($player, $roundData);
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