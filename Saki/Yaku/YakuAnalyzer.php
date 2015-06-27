<?php
namespace Saki\Yaku;

class YakuAnalyzer {
    private $yakus;

    static function getDefaultYakus() {
        return [
            AllRunsYaku::getInstance(),
        ];
    }

    function __construct(array $yakus = null) {
        $this->yakus = $yakus !== null ? $yakus : static::getDefaultYakus();
    }

    function getYakus() {
        return $this->yakus;
    }

    /**
     * @param YakuAnalyzerTarget $target
     * @return YakuAnalyzerResult
     */
    function analyzeTarget(YakuAnalyzerTarget $target) {
        // handTiles target -> handMelds[] subTarget

        // get analyzerResult[]

        // return where yakuCount is max

        $result = new YakuAnalyzerResult();
        $result->setYakuList(new YakuList([]));
        return $result;
    }

    /**
     * @param YakuAnalyzerSubTarget[] $subTargets
     * @return YakuAnalyzerResult[]
     */
    function analyzeSubTargets(array $subTargets) {
        $results = [];
        foreach ($subTargets as $subTarget) {
            $result = $this->analyzeSubTarget($subTarget);
            $results[] = $result;
        }
        return $results;
    }

    /**
     * @param YakuAnalyzerSubTarget $subTarget
     * @return YakuAnalyzerResult
     */
    function analyzeSubTarget(YakuAnalyzerSubTarget $subTarget) {
        $yakuList = new YakuList([]);
        foreach ($this->yakus as $yaku) {
            if ($yaku->existIn($subTarget)) {
                $yakuList->push($yaku); // yakuList onChange hook: remove mutually-excluded yaku. or removeExcludedMethod.
            }
        }
        $result = new YakuAnalyzerResult();
        $result->setYakuList($yakuList);
        return $result;
    }
}