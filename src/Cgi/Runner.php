<?php
namespace Microbe\Cgi;
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

    public function run(\Microbe\Config $config) {
        $response = new \Microbe\Cgi\Response();
        $request  = new \Microbe\Cgi\Request();

        \Microbe\Microbe::init($config);

        \Microbe\Microbe::$ins->milestoneDefine('init', new \Microbe\Chain\Stub());
        \Microbe\Microbe::$ins->milestoneInstallChain('init');

        \Microbe\Microbe::$ins->chainHead->exec($request, $response);
    }
}
