<?php

namespace Saki\App;

use Saki\Tile;
use Saki\TileSortedList;

class Server {
    private $data;

    function __construct() {
        session_start();
        if (!isset($_SESSION['data']) || count($_SESSION['data']) === 1) {
            $_SESSION['data'] = TileSortedList::fromString('123m123s123pEEEWW', false);
        }
        $this->data = $_SESSION['data'];
    }

    function process() {
        if (isset($_GET['tile'])) {
            $tileString = $_GET['tile'];
            $tile = Tile::fromString($tileString);
            $tileList = $this->data;
            $tileList->remove([$tileList->toFirstIndex($tile)]);
        }
    }

    /**
     * @return TileSortedList
     */
    function getData() {
        return $this->data;
    }
}