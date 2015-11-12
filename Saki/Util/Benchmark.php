<?php
namespace Saki\Util;

class Benchmark {
    private $name;
    private $items;

    function __construct($name) {
        $this->name = $name;
        $this->items = new ArrayLikeObject([]);
    }

    function __toString() {
        $itemsString = implode("\n", $this->items->toArray());
        return sprintf("# %s\n%s", $this->getName(), $itemsString);
    }

    function getName() {
        return $this->name;
    }

    /**
     * @param BenchmarkItem $item
     */
    function add(BenchmarkItem $item) {
        $this->items->push($item);
    }
}