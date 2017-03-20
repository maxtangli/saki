<?php
namespace Saki\Command;

/**
 * @package Saki\Command
 */
interface CommandDecider {
    function clear();

    /**
     * @return bool
     */
    function decided();

    /**
     * @return Command
     */
    function getDecided();

    /**
     * @param Command $command
     * @return bool
     */
    function isDecidedCommand(Command $command);

    /**
     * @param Command $command
     * @return bool
     */
    function allowSubmit(Command $command);

    /**
     * @param Command $command
     * @return
     */
    function submit(Command $command);
}