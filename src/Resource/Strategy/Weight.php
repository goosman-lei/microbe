<?php
namespace Microbe\Resource\Strategy;
class Weight {
    public static function select($nodes) {
        $sum = 0;
        foreach ($nodes as $node) {
            $sum += array_key_exists('weight', $node) ? $node['weight'] : 1;
        }

        $num = rand(1, $sum);

        $passed  = 0;
        foreach ($nodes as $node) {
            $weight = array_key_exists('weight', $node) ? $node['weight'] : 1;
            $passed += $weight;
            if ($passed >= $num) {
                return $node;
            }
        }
    }
}
