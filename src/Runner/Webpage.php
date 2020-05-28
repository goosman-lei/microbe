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
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Cgi\Chain\CanonicalUri(), '-canonical-uri');
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Cgi\Chain\Router(), '-route');
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Cgi\Chain\Dispatcher(), '-dispatch');
        \Microbe\Microbe::$ins->installUserChain();
    }

    public function run() {
        $request  = new \Microbe\Cgi\Request();
        $response = new \Microbe\Cgi\Response();

        \Microbe\Microbe::$ins->doChain($request, $response);
    }
}
