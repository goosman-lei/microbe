<?php
namespace Microbe\Scene\Daemon;
class Runner {
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
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Cli\Chain\Router($config->get('scene.daemon.router')), '-route');
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Cli\Chain\Dispatcher($config->get('scene.daemon.dispatcher')), '-dispatch');
        \Microbe\Microbe::$ins->installUserChain();
    }

    public function run() {
        $request  = new \Microbe\Cli\Request();
        $response = new \Microbe\Cli\Response();

        \Microbe\Microbe::$ins->doChain($request, $response);
    }
}
