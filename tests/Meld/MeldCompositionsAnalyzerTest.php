<?php

class MeldCompositionsAnalyzerTest extends PHPUnit_Framework_TestCase {
    /**
     * @dataProvider getMeldCompositionsProvider
     */
    function testGetMeldCompositions($expectedMeldListStrings, $tilesStr, $meldTypes) {
        $r = new \Saki\Meld\MeldCompositionsAnalyzer();
        $meldLists = $r->analyzeMeldCompositions(\Saki\Tile\TileSortedList::fromString($tilesStr), $meldTypes);
        $actualMeldListStrings = array_map(function ($v) {
            return $v->__toString();
        }, $meldLists);
        $meldTypesStr = implode(',', $meldTypes);
        $msg = "\$tilesStr[$tilesStr] \$meldTypes[$meldTypesStr]";
        $this->assertSame($expectedMeldListStrings, $actualMeldListStrings, $msg);
    }

    function getMeldCompositionsProvider() {
        $default = \Saki\Meld\MeldTypesFactory::getInstance()->getHandAndDeclaredMeldTypes();
        return [
            // empty case
            [[], '', $default],
            [[], '1s', $default],
            [[], '12s', $default],
            [[], '1s1p', $default],
            // one possibility
            [['123s'], '123s', $default],
            [['123m,123p'], '123m123p', $default],
            [['123m,123p,123s'], '123m123p123s', $default],
            // multiple possibility
            [['11s,11s', '1111s'], '1111s', $default],
            [['11s,123s,44s,44s', '11s,123s,4444s', '111s,234s,444s'], '111234444s', $default],
        ];
    }
}