<?php
namespace Microbe\Chain;
class Stub extends \Microbe\Chain {
    public function exec($request, $response) {
        $this->doNext($request, $response);
    }
}
