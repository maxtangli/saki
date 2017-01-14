<?php

const t_1m = 1;
const t_2m = t_1m << 1;
const t_3m = t_1m << 2;
const t_4m = t_1m << 3;
const t_0m = t_1m << 9;
const t_5m = t_1m << 4 | t_0m;
const t_6m = t_1m << 5;
const t_7m = t_1m << 6;
const t_8m = t_1m << 7;
const t_9m = t_1m << 8;
const t_m = 0b1111111111;

const t_1p = t_1m << 10;
const t_2p = t_1m << 11;
const t_3p = t_1m << 12;
const t_4p = t_1m << 13;
const t_0p = t_1m << 19;
const t_5p = t_1m << 14 | t_0p;
const t_6p = t_1m << 15;
const t_7p = t_1m << 16;
const t_8p = t_1m << 17;
const t_9p = t_1m << 18;
const t_p = t_m << 10;

const t_1s = t_1m << 20;
const t_2s = t_1m << 21;
const t_3s = t_1m << 22;
const t_4s = t_1m << 23;
const t_0s = t_1m << 29;
const t_5s = t_1m << 24 | t_0s;
const t_6s = t_1m << 25;
const t_7s = t_1m << 26;
const t_8s = t_1m << 27;
const t_9s = t_1m << 28;
const t_s = t_m << 20;

const t_suit = t_m | t_p | t_s;
const t_red = t_0m | t_0p | t_0s;

const t_E = t_1m << 30;
const t_S = t_1m << 31;
const t_W = t_1m << 32;
const t_N = t_1m << 33;
const t_wind = t_E | t_S | t_W | t_N;

const t_C = t_1m << 34;
const t_P = t_1m << 35;
const t_F = t_1m << 36;
const t_dragon = t_C | t_P | t_F;

const t_honour = t_wind | t_dragon;

const t_all = t_suit | t_honour;
const t_term = t_1m | t_9m | t_1p | t_9p | t_1s | t_9s;
const t_termOrHonour = t_term | t_honour;
const t_simple = t_all & (~t_termOrHonour);

class BitTile {
    static function fromString(string $s) {
        $constant = constant('t_'. $s);
        if (is_null($constant)) {
            throw new \InvalidArgumentException();
        }
        return new self($constant);
    }

    /**
     * @param int $n
     */
    function __construct(int $n) {
        $this->n = $n;
    }

    private $n;

    /**
     * @param int $predicate
     * @return bool
     */
    function match(int $predicate) {
        return ($this->n & $predicate) != 0;
    }
}

class TilePredicateTest extends \SakiTestCase {
    /** @var BitTile */
    private $tile;

    function testBitTile() {
        $this->tile = BitTile::fromString('1m');
        $predicates = [t_1m, t_m, t_suit, t_term, t_termOrHonour];
        foreach ($predicates as $predicate) {
            $this->assertTile($predicate);
        }
    }

    private function assertTile(int $predicate) {
        $actual = $this->tile->match($predicate);
        $this->assertTrue($actual, "$predicate");
    }
}