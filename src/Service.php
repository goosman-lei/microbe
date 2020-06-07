<?php
namespace Microbe;
abstract class Service {
    const CODE_SUCCESS   = 0;
    const CODE_MODULE_NOT_FOUND = 1001;
    const CODE_SERVICE_NOT_FOUND = 1002;
    const CODE_SERVICE_EXCEPTION = 1003;

    public function isSuccess($res) {
        return $res['code'] === self::CODE_SUCCESS;
    }
}
