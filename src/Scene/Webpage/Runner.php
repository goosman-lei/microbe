<?php
namespace Microbe\Scene\Webpage;
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
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Cgi\Chain\CanonicalUri($config->get('scene.webpage.canonicalUri')), '-canonical-uri');
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Cgi\Chain\Router($config->get('scene.webpage.router')), '-route');
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Cgi\Chain\TemplateEngine($config->get('scene.webpage.templateEngine')), '-template-engine');
        \Microbe\Microbe::$ins->appendChain(new \Microbe\Cgi\Chain\Dispatcher($config->get('scene.webpage.dispatcher')), '-dispatch');
        \Microbe\Microbe::$ins->installUserChain();
    }

    public function run() {
        $request  = new \Microbe\Cgi\Request();
        $response = new \Microbe\Cgi\Response();

        \Microbe\Microbe::$ins->doChain($request, $response);
    }
}
