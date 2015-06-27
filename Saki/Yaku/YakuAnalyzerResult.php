<?php
namespace Saki\Yaku;

class YakuAnalyzerResult {
    /**
     * @var YakuList
     */
    private $yakuList;

    function getYakuList() {
        return $this->yakuList;
    }

    function setYakuList($yakuList) {
        $this->yakuList = $yakuList;
    }
}