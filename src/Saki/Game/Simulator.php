<?php

namespace Saki\Game;

use Saki\Command\Command;

class Simulator {
    function run() {
        $round = new Round();

        while (true) { // todo move into proper class
            // echo round info
            echo $round->__toString() . "\n";

            // get executable
            $executableList = $round->getProcessor()->getProvider()
                ->getAllExecutableList()
                ->select(function (Command $command) {
                    return $command->__toString();
                });
            if ($round->getPhase()->isPublic()) {
                $executableList->insertLast('passAll');
            }

            // echo executable info
            $executableListString = "executableList:\n";
            foreach ($executableList as $k => $v) {
                $executableListString .= sprintf("[%s]%s\n", $k, $v);
            }
            echo $executableListString;

            // process user input
            echo "input>";
            $line = trim(fgets(STDIN));
            $command = isset($executableList[$line]) ? $executableList[$line]: $line;
            if (in_array($line, ['cls', 'clear'])) {
                system($command);
            } else {
                try {
                    $round->process($command);
                } catch (\Exception $e) {
                    echo 'error:'.$e->getMessage()."\n";
                }
            }
        }
    }
}