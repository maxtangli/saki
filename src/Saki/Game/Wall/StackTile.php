<?php
//namespace Saki\Game\Wall;
//
//use Saki\Game\Tile\Tile;
//
///**
// * @package Saki\Game\Wall
// */
//class StackTile {
//    private $tile;
//    private $opened;
//
//    function __construct() {
//        $this->init();
//    }
//
//    function init() {
//        $this->tile = null;
//        $this->opened = false;
//    }
//
//    /**
//     * @return string
//     */
//    function __toString() {
//        if (!$this->exist()) return 'X';
//        if (!$this->isOpened()) return 'O';
//        return $this->getTile()->__toString();
//    }
//
//    /**
//     * @return bool
//     */
//    function exist() {
//        return isset($this->tile);
//    }
//
//    /**
//     * @return Tile
//     */
//    function getTile() {
//        return $this->tile;
//    }
//
//    /**
//     * @return bool
//     */
//    function isOpened() {
//        return $this->opened;
//    }
//
//    /**
//     * @param Tile|null $tile
//     */
//    function setTile(Tile $tile = null) {
//        $this->tile = $tile;
//    }
//
//    /**
//     * @return Tile
//     */
//    function pop() {
//        if (!$this->exist()) {
//            throw new \LogicException();
//        }
//        $tile = $this->tile;
//        $this->tile = null;
//        return $tile;
//    }
//
//    function open() {
//        if ($this->isOpened()) {
//            throw new \LogicException();
//        }
//        $this->opened = true;
//    }
//}