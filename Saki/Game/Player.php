<?php
namespace Saki\Game;

use Saki\Tile\Tile;

class Player {
    private $no;

    private $score;
    private $selfWind;

    private $playerArea;

    function __construct($no, $score, Tile $selfWind) {
        $this->no = $no;
        $this->score = $score;
        $this->selfWind = $selfWind;
        $this->playerArea = new TileArea();
    }

    function reset(Tile $selfWind) {
        $this->selfWind = $selfWind;
        $this->playerArea->reset();
    }

    function __toString() {
        return sprintf('p%s %s score %s', $this->getNo(), $this->getSelfWind(), $this->getScore());
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

    function setSelfWind(Tile $selfWind) {
        $this->selfWind = $selfWind;
    }

    function isSelfWind(Tile $tile) {
        return $tile == $this->getSelfWind();
    }

    function isDealer() {
        return $this->getSelfWind() == Tile::fromString('E');
    }

    function getPlayerArea() {
        return $this->playerArea;
    }

    function setPlayerArea($playerArea) {
        if ($playerArea === null) {
            throw new \InvalidArgumentException();
        }
        $this->playerArea = $playerArea;
    }
}