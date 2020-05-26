<?php
namespace Microbe\Chain;
class Stub extends \Microbe\Chain {
    public function exec(\Microbe\Request $request, \Microbe\Response $response) {
        $this->doNext($request, $response);
    }
}
