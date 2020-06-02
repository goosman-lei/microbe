<?php
namespace Microbe\Scene\Webpage\Chain;
class ErrorTemplate extends \Microbe\Chain {
    public function exec($request, $response) {
        $response->regFailureHandler([$this, 'failureHandler']);

        $this->doNext($request, $response);
    }

    public function failureHandler($errInfo, $request, $response) {
        $module = $request->routeModule;
        $action = $request->routeAction;

        $tpl = $this->getTpl($module, $action);
        if (empty($tpl)) {
            return FALSE;
        }

        $response->templateEngine->assign($errInfo);
        $text = $response->templateEngine->render($tpl);

        $response->appendBody($text);

        return TRUE;
    }

    public function getTpl($module, $action) {
        /* 无规则配置 */
        if (empty($this->config['rules'])) {
            return '';
        }

        /* 无module配置, 采用全局默认 */
        if (empty($this->config['rules'][$module])) {
            return $this->config['rules']['*'];
        }
        $moduleConfig = $this->config['rules'][$module];

        /* 无action配置, 采用module默认 */
        if (empty($moduleConfig[$action])) {
            return $moduleConfig['*'];
        }

        /* 有具体配置 */
        return $moduleConfig[$action];
    }
}
