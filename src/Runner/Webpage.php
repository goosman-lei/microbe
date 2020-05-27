<?php
namespace Microbe\Runner;
class Webpage {
    protected static $runner;

    protected function __construct() {
    }

    public static function getInstance() {
        if (!isset(self::$runner)) {
            self::$runner = new self();
        }
        return self::$runner;
    }

    public function init(\Microbe\Config $config) {
        \Microbe\Microbe::init($config);

        \Microbe\Microbe::$ins->appendChain(new \Microbe\Chain\Root(), '-root');
        \Microbe\Microbe::$ins->installUserChain();
    }

    public function run() {
        $response = new \Microbe\Cgi\Response();
        $request  = new \Microbe\Cgi\Request();

        \Microbe\Microbe::$ins->doChain($request, $response);
    }
}
