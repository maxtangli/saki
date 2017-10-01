<?php

namespace Saki\Play;

/**
 * @package Saki\Play
 */
interface UserProxy {
    function getId();
    function sendRound(array $json);
    function sendOk();
    function sendError(string $message);
}