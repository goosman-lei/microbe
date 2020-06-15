<?php
namespace Microbe\Resource\Strategy;
/**
 * Weight 
 * $nodeConfig = ['weight' => int]; // weight 默认1
 * @author goosman.lei <goosman.lei@gmail.com> 
 */
class Weight {
    public static function select($nodes) {
        $sum = 0;
        foreach ($nodes as $node) {
            $sum += array_key_exists('weight', $node) ? intval($node['weight']) : 1;
        }

        $rand = rand(1, $sum);

        $now = 0;
        foreach ($nodes as $node) {
            $now += array_key_exists('weight', $node) ? intval($node['weight']) : 1;
            if ($now >= $rand) {
                return $node;
            }
        }
    }
}
