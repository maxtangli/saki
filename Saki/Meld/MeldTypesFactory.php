<?php

namespace Saki\Meld;

class MeldTypesFactory extends \Saki\Util\Singleton {

    /**
     * @return MeldType[]
     */
    function getAllMeldTypes() {
        return [
            // common
            PairMeldType::getInstance(),
            RunMeldType::getInstance(),
            TripleMeldType::getInstance(),
            QuadMeldType::getInstance(),
            // weak
            SingleMeldType::getInstance(),
            WeakRunMeldType::getInstance(),
        ];
    }

    /**
     * @param bool $includeWeak
     * @return MeldType[]
     */
    function getHandMeldTypes($includeWeak = false) {
        $r = [
            PairMeldType::getInstance(),
            RunMeldType::getInstance(),
            TripleMeldType::getInstance(),
        ];
        if ($includeWeak) {
            $r = array_merge($r, [
                SingleMeldType::getInstance(),
                WeakRunMeldType::getInstance(),
            ]);
        }
        return $r;
    }

    function getHandAndDeclaredMeldTypes($includeWeak = false) {
        $r = [
            PairMeldType::getInstance(),
            RunMeldType::getInstance(),
            TripleMeldType::getInstance(),
            QuadMeldType::getInstance(),
        ];
        if ($includeWeak) {
            $r = array_merge($r, [
                SingleMeldType::getInstance(),
                WeakRunMeldType::getInstance(),
            ]);
        }
        return $r;
    }

    /**
     * @return MeldTypesFactory
     */
    static function getInstance() {
        return parent::getInstance();
    }
}