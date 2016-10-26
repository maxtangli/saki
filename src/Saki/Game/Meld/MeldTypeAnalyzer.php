<?php

namespace Saki\Game\Meld;

use Saki\Game\Tile\TileList;

/**
 * Analyze given TileList's MeldType in preset MeldType set.
 * @package Saki\Game\Meld
 */
class MeldTypeAnalyzer {
    private $candidateMeldTypes;

    /**
     * @param MeldType[] $candidateMeldTypes
     */
    function __construct(array $candidateMeldTypes) {
        $this->candidateMeldTypes = $candidateMeldTypes;
    }

    /**
     * @return MeldType[]
     */
    function getCandidateMeldTypes() {
        return $this->candidateMeldTypes;
    }

    /**
     * @param TileList $tileList
     * @param bool $allowNoMatch
     * @return false|MeldType
     */
    function analyzeMeldType(TileList $tileList, $allowNoMatch = false) {
        $candidateMeldTypes = $this->getCandidateMeldTypes();
        foreach ($candidateMeldTypes as $meldType) {
            if ($meldType->valid($tileList)) {
                return $meldType;
            }
        }

        if ($allowNoMatch) {
            return false;
        } else {
            throw new \InvalidArgumentException(
                sprintf('No matched meldType for $tileList[%s] in $candidateMeldTypes[%s].'
                    , $tileList, implode(',', $candidateMeldTypes))
            );
        }
    }
}