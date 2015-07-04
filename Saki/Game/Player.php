<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class Player {
    private $no;
    private $score;
    private $selfWind;
    private $turn;
    private $playerArea;

    function __construct($no, $score, Tile $selfWind, $turn = 0, PlayerArea $playerArea = null) {
        $this->no = $no;
        $this->score = $score;
        $this->selfWind = $selfWind;
        $this->turn = $turn;
        $this->playerArea = $playerArea ?: new PlayerArea();
    }

    function __toString() {
        return sprintf('p%s %s score %s turn %s', $this->getNo(), $this->getSelfWind(), $this->getScore(), $this->getTurn());
    }

    function getNo() {
        return $this->no;
    }

    function getScore() {
        return $this->score;
    }

    function setScore($score) {
        $this->score = $score;
    }

    function getSelfWind() {
        return $this->selfWind;
    }

    function setSelfWind($selfWind) {
        $this->selfWind = $selfWind;
    }

    function getTurn() {
        return $this->turn;
    }

    function setTurn($turn) {
        $this->turn = $turn;
    }

    function addTurn() {
        $this->setTurn($this->getTurn() + 1);
    }

    function getPlayerArea() {
        return $this->playerArea;
    }

    function setPlayerArea($playerArea) {
        $this->playerArea = $playerArea;
    }
}