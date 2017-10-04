<?php

namespace Saki\Play;

/**
 * @package Saki\Play
 */
interface UserProxy {
    function getId();
//    function send(Response $response);
    function sendRound(array $json);
    function sendOk();
    function sendError(string $message);
}