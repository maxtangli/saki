<?php

namespace Saki\Meld;

use Saki\Tile\TileList;

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
            $candidateMeldTypesString = implode(',', $candidateMeldTypes);
            throw new \InvalidArgumentException("No matched meldType for \$tileList[$tileList] within \$candidateMeldTypes[$candidateMeldTypesString].");
        }
    }
}