<?php

namespace Saki\Meld;

use Saki\Tile\TileSortedList;

class MeldTypeAnalyzer {
    private $candidateMeldTypes;

    /**
     * @param MeldType[] $candidateMeldTypes
     */
    function __construct(array $candidateMeldTypes = null) {
        $this->candidateMeldTypes = $candidateMeldTypes !== null ?
            $candidateMeldTypes : MeldTypesFactory::getInstance()->getAllMeldTypes();
    }

    /**
     * @return MeldType[]
     */
    function getCandidateMeldTypes() {
        return $this->candidateMeldTypes;
    }

    /**
     * @param TileSortedList $tileSortedList
     * @param bool $allowNoMatch
     * @return false|MeldType
     */
    function analyzeMeldType(TileSortedList $tileSortedList, $allowNoMatch = false) {
        $candidateMeldTypes = $this->getCandidateMeldTypes();
        foreach ($candidateMeldTypes as $meldType) {
            if ($meldType->valid($tileSortedList)) {
                return $meldType;
            }
        }
        if ($allowNoMatch) {
            return false;
        } else {
            $candidateMeldTypesString = implode(',', $candidateMeldTypes);
            throw new \InvalidArgumentException("No matched meldType for \$tileList[$tileSortedList] within \$candidateMeldTypes[$candidateMeldTypesString].");
        }
    }
}