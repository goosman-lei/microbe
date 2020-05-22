<?php
namespace Microbe;
interface TemplateEngine {
    public function assign($name, $value = null);
    public function fetch();
}
