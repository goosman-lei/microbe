<?php
namespace \Microbe\Cgi\Chain;
/**
 * CanonicalUri 
 * 增强$request
    $request->canonicalUri = 规范化的URI
 * @author goosman.lei <goosman.lei@gmail.com> 
 */
class CanonicalUri extends \Microbe\Chain {
    public function exec($request, $response) {
        $canonicalUri = $request->getOriginalUri();
        // strip query_string and fragment. only remain path
        if (($position = strpos($canonicalUri, '?')) !== FALSE) {
            $canonicalUri = substr($canonicalUri, 0, $position);
        }

        // strip repeat "/"
        $canonicalUri = preg_replace(';/{2,};', '/', $canonicalUri);
        $canonicalUri = '/' . trim($canonicalUri, '/');

        $baseUri = '/' . trim(\Microbe\Microbe::$ins->config->get('app.base_uri'), '/');
        if ($baseUri != '/' && strpos($canonicalUri, $baseUri) === 0) {
            $canonicalUri = substr($canonicalUri, strlen($baseUri));
        }

        $request->regExtProperty('canonicalUri', $canonicalUri);

        $this->doNext($request, $response);
    }
}