<?php
namespace App\Helper;

/**
 * Class used to indicate a business logic Exception
 * Will be properly handled by Exception handler of Slim
 * And displayed in user friendly format, followed with the
 * correct error_code => http status_code
 */
class LogicException extends \Exception {
}