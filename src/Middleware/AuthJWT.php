<?php
namespace App\Middleware;

use App\Helper\LogicException;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Middleware is used to authenticate users using their JWT token
 * TODO: Add expire date on tokens
 */
class AuthJWT {

    protected $settings;

    public function __construct($settings) {
        $this->settings = $settings;
    }

    private function validate_token($token, $jwt_settings) {
        $data = new ValidationData();
        $data->setIssuer($jwt_settings['issuer']);
        $data->setAudience($jwt_settings['audience']);

        return $token->validate($data);
    }

    private function verify_token($token, $jwt_settings) {
        $signer = new Sha256();
        return $token->verify($signer, $jwt_settings['secret']);
    }

    public function __invoke(Request $request, Response $response, $next) {
        $token = $request->getHeader('Authorization')[0];

        // Check if token exists
        if (empty($token)) {
            throw new LogicException('"Authorization" http request header not found', 401);
        }

        // Load jwt settings
        $jwt_settings = $this->settings;

        // Parse token
        $token = (new Parser())->parse((string) $token);

        // Verify token signature
        if (! $this->verify_token($token, $jwt_settings)) {
            throw new LogicException('Invalid "Authorization" token', 403);
        }

        // Validate token
        if (! $this->validate_token($token, $jwt_settings)) {
            throw new LogicException('Expired "Authorization" token', 403);
        }

        // Fill user_uuid attribute
        $request = $request->withAttribute('user_uuid', $token->getClaim('uuid'));

        $response = $next($request, $response);

        return $response;
    }
}