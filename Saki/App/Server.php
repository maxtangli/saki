<?php

namespace Saki\App;

use Saki\Game\Game;
use Saki\Command\Command;
use Saki\Tile;
use Saki\TileSortedList;

class Server {
    private $data;

    function __construct() {
        session_start();
//        $_SESSION['data'] = null;
        if (!isset($_SESSION['data'])) {
            $this->reset();
        }
        $this->data = $_SESSION['data'];
    }

    function reset() {
        $game = new Game(4, 25000);
        $_SESSION['data'] = $game;
        $this->data = $_SESSION['data'];
    }

    function process() {
        if (isset($_GET['command'])) {
            $commandString = $_GET['command'];
            $game = $this->getData();
            $command = $game->getCommandFactory()->createCommand($commandString);
            $game->getCurrentRound()->acceptCommand($command);
        } elseif (isset($_GET['reset'])) {
            $this->reset();
        }
    }

    /**
     * @return Game
     */
    function getData() {
        return $this->data;
    }
}