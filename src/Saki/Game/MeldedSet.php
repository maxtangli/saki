<?php

namespace Saki\Game;

use Saki\Meld\Meld;

/**
 * @package Saki\Game
 */
class MeldedSet {
    private $meld;
    private $sourceList;

    /**
     * @param Meld $meld
     * @param array $sources
     * @param Turn $claimedTurn
     */
    function __construct(Meld $meld, array $sources) {
        if (!$meld->getWinSetType()->isDeclareWinSet()) {
            throw new \InvalidArgumentException();
        }
        
        $this->meld = $meld;
        $this->sourceList = null; // todo
    }

    /**
     * @return Meld
     */
    function getMeld() {
        return $this->meld;
    }
}