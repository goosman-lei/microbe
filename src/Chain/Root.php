<?php
namespace Microbe\Chain;
class Root extends \Microbe\Chain {
    public function exec($request, $response) {
        $this->doNext($request, $response);

        $response->success();
    }
}
