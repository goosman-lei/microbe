<?php
namespace Microbe\Scene\Webpage\Chain;
class TemplateEngine extends \Microbe\Chain {
    public function exec($request, $response) {
        $moudle = $request->routeModule;
        $action = $request->routeAction;
        $templateEngine = \Microbe\Scene\Webpage\TemplateEngine\Factory::getInstance()->getTemplateEngine($module, $action);

        if (!isset($templateEngine) || !($templateEngine instanceof \Microbe\Scene\Webpage\TemplateEngine\Abs)) {
            throw new RuntimeException('get template engine failure');
        }

        $response->regExtProperty('templateEngine', $templateEngine);

        $this->doNext($request, $response);

        $text = $templateEngine->render($module . '/' . $action);

        $response->appendBody($text);
    }
}