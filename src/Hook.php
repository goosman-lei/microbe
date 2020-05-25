<?php
namespace Microbe;
class Hook {
    protected $config;
    public function __construct($config) {
        $this->config = $config;
    }

    public function afterInput($request) {
    }
    public function beforeOutput($response) {
    }
}
