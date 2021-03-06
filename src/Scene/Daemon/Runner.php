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
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Scene\Daemon\Chain\Router($config->get('scene.daemon.systemChains.router')), '-route');
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Scene\Daemon\Chain\Dispatcher($config->get('scene.daemon.systemChains.dispatcher')), '-dispatch');
        \Microbe\Microbe::$ins->installUserChain($config->get('scene.daemon.userChains'));
    }

    public function run() {
        $request  = new \Microbe\Cli\Request();
        $response = new \Microbe\Cli\Response();

        \Microbe\Microbe::$ins->doChain($request, $response);
    }
}
