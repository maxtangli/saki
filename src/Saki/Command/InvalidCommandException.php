<?php
namespace Saki\Command;

use Exception;

/**
 * @package Saki\Command
 */
class InvalidCommandException extends \Exception {
    /**
     * @param string $commandLine
     * @param string $subMessage
     */
    function __construct($commandLine = '', $subMessage = '') {
        $message = sprintf('Invalid command%s%s.',
            $commandLine ? "[$commandLine]" : '',
            $subMessage ? ": $subMessage" : '');
        parent::__construct($message);
    }
}