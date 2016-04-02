<?php
namespace Saki\Command\ParamDeclaration;

use Saki\Command\CommandContext;

/**
 * to support Command's fromString(),toString().
 * @package Saki\Command\ParamDeclaration
 */
abstract class ParamDeclaration {
    private $context;
    private $paramString;

    function __construct(CommandContext $context, string $paramString) {
        $this->context = $context;
        $this->paramString = $paramString;
    }

    function __toString() {
        return $this->getParamString();
    }

    function getContext() {
        return $this->context;
    }

    function getParamString() {
        return $this->paramString;
    }

    abstract function toObject();
}