<?php

class PerformanceTest extends PHPUnit_Framework_TestCase {
    function testBenchmark() {
        $a = [

        ];

        if (!empty($a)) {
            $s = implode("\n", $a);
            echo $s;
            $this->writeLog($s);
        }
    }

    protected function writeLog($s) {
        $file = __DIR__ . '/PerformanceTestResult.md';
        $now = new DateTime();
        $content = sprintf("%s\n\n%s", $now->format(DateTime::ISO8601), $s);
        file_put_contents($file, $content);
    }
}