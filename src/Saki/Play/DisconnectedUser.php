<?php

namespace Saki\Play;

/**
 * @package Saki\Play
 */
class DisconnectedUser implements UserProxy {
    private $id;

    /**
     * @param string $id
     */
    function __construct(string $id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('DisconnectedUser[%s]', $this->getId());
    }

    //region UserProxy impl
    function getId() {
        return $this->id;
    }

    function send(Response $response) {
        // do nothing
    }
    //endregion
}