<?php
namespace Microbe\Scene\Webpage\Chain;
class TemplateEngine extends \Microbe\Chain {
    protected $templateEngine;

    public function exec($request, $response) {
        $module = $request->routeModule;
        $action = $request->routeAction;

        \Microbe\Scene\Webpage\TemplateEngine\Factory::init($this->config);
        $templateEngine = \Microbe\Scene\Webpage\TemplateEngine\Factory::$factory->getTemplateEngine($module, $action);

        if (!isset($templateEngine) || !($templateEngine instanceof \Microbe\Scene\Webpage\TemplateEngine\Abs)) {
            throw new RuntimeException('get template engine failure');
        }

        $response->regExtProperty('templateEngine', $templateEngine);

        $this->templateEngine = $templateEngine;

        $response->regFailureHandler([$this, 'failureHandler']);

        $this->doNext($request, $response);

        $text = $templateEngine->render($module . '/' . $action);

        $response->appendBody($text);
    }

    public function failureHandler($request, $response, $failureInfo) {
        if (!empty($this->config['error_tpl'])) {
            $this->templateEngine->assign($failureInfo);
            $text = $this->templateEngine->render($this->config['error_tpl']);
            $response->appendBody($text);
            return TRUE;
        }
    }
}