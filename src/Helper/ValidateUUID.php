<?php
namespace App\Helper;

use Respect\Validation\Validator;

class ValidateUUID {
    public static function assert($user_uuid) {
        Validator::stringType()->length(36)->assert($user_uuid);
    }
}