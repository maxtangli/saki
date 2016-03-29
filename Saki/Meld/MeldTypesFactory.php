<?php

namespace Saki\Meld;

class MeldTypesFactory extends \Saki\Util\Singleton {

    /**
     * @return MeldType[]
     */
    function getAllMeldTypes() {
        return [
            // hand win set
            RunMeldType::getInstance(),
            TripleMeldType::getInstance(),
            // declare win set
            QuadMeldType::getInstance(),
            // pair
            PairMeldType::getInstance(),
            // weak
            WeakPairMeldType::getInstance(),
            WeakRunMeldType::getInstance(),
        ];
    }
}