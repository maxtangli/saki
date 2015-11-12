<?php
//namespace Saki\Game;
//
//use Saki\Command\Command;
//
//class PublicPhaseCommandPoller {
//    private $mapWaitingPlayerNo2priority;
//    private $decidedCommands;
//
//    /**
//     * @param Command[] $commands
//     */
//    function __construct(array $commands) {
//        $this->init($commands);
//    }
//
//    /**
//     * @param Command[] $commands
//     */
//    function reset(array $commands) {
//        // record each player's highest priority
//        $mapWaitingPlayerNo2priority = [];
//        foreach ($commands as $command) {
//            $playerNo = $command->getPlayer()->getNo();
//            $priority = $this->getCommandPriority($command);
//            $mapWaitingPlayerNo2priority[$playerNo] = isset($mapWaitingPlayerNo2priority[$playerNo]) ?
//                max($mapWaitingPlayerNo2priority[$playerNo], $priority) : $priority;
//        }
//        $this->mapWaitingPlayerNo2priority = $mapWaitingPlayerNo2priority;
//        $this->decidedCommands = [];
//    }
//
//    /**
//     * @param Command $command
//     */
//    function acceptCommand(Command $command) {
//        // possible commands: pass, chow, pong/kang, ron
//        // handle decidedCommands
//        if ($command instanceof PassCommand) {
//            // ignore
//        } elseif (empty($this->decidedCommands)) {
//            $this->decidedCommands = [$command];
//        } else {
//            $priorityDiff = $this->getCommandPriority($command) - $this->getCommandPriority($this->decidedCommands[0]);
//            if ($priorityDiff > 0) { // replace
//                $this->decidedCommands = [$command];
//            } elseif ($priorityDiff == 0) { // add
//                $this->decidedCommands[] = $command;
//            } else {
//                // ignore
//            }
//        }
//
//        // clear waitingList of command owner
//        unset($this->mapWaitingPlayerNo2priority[$command->getPlayer()->getNo()]);
//    }
//
//    /**
//     * @return bool
//     */
//    function decided() {
//        return empty($this->mapWaitingPlayerNo2priority);
//    }
//
//    /**
//     * @return Command[]
//     */
//    function getDecidedCommands() {
//        if (!$this->decided()) {
//            throw new \InvalidArgumentException();
//        }
//        return $this->decidedCommands;
//    }
//
//    /**
//     * @param Command $command
//     * @return int
//     */
//    function getCommandPriority(Command $command) {
//        // ron = ron
//        // > pon/kang (one player only)
//        // > chow (one player only)
//        // > pass
//        $m = ['RonCommand' => 4,
//            'KangCommand' => 3,
//            'PongCommand' => 2,
//            'ChowCommand' => 1,
//            'PassCommand' => 0];
//        $key = get_class($command); // WARNING: assert no subclass
//        if (!isset($m[$key])) {
//            throw new \InvalidArgumentException();
//        }
//        return $m[$key];
//    }
//}