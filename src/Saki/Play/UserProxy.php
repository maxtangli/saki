<?php

namespace Saki\Play;

/**
 * @package Saki\Play
 */
interface UserProxy {
    function getId();

    function sendJson(array $data);
}