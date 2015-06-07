<?php

namespace Saki\Meld;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Saki\TileList;

class MeldTypeAnalyzer {
    /**
     * @return MeldType[]
     */
    static function getDefaultCandidateMeldTypes() {
        return [
            EyesMeldType::getInstance(),
            SequenceMeldType::getInstance(),
            TripletMeldType::getInstance(),
            KongMeldType::getInstance(),
        ];
    }

    private $candidateMeldTypes;

    /**
     * @param MeldType[] $candidateMeldTypes
     */
    function __construct(array $candidateMeldTypes = null) {
        $this->candidateMeldTypes = $candidateMeldTypes !== null ? $candidateMeldTypes : self::getDefaultCandidateMeldTypes();
    }

    /**
     * @return MeldType[]
     */
    function getCandidateMeldTypes() {
        return $this->candidateMeldTypes;
    }

    /**
     * @param TileList $tileList
     * @return MeldType|false
     */
    function analyzeMeldType(TileList $tileList, $allowNoMatch = false) {
        $candidateMeldTypes = $this->getCandidateMeldTypes();
        foreach($candidateMeldTypes as $meldType) {
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