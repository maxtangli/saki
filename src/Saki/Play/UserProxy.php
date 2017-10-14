<?php

namespace Saki\Play;

/**
 * @package Saki\Play
 */
interface UserProxy {
    /**
     * @return string
     */
    function getId();

    /**
     * @param Response $response
     */
    function send(Response $response);
}