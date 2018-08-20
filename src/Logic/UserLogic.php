<?php
namespace App\Logic;

use Slim\Container;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use App\Helper\LogicException;

/**
 * Helper function that is used to return a uuid v4 token
 * @return String uuid v4 token
 */
function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

/**
 * Used to contain all the database logic about User model
 */
class UserLogic extends AbstractLogic {

    public function register($username, $email, $password) {
        $query = $this->connection->createQueryBuilder();

        $query
        ->insert('users')
        ->values([
            'username' => ':username',
            'uuid' => ':uuid',
            'email' => ':email',
            'password' => ':password'
        ])
        ->setParameter('username', $username)
        ->setParameter('uuid', gen_uuid())
        ->setParameter('email', $email)
        ->setParameter('password', $this->hash_password($password));

        return $query->execute();
    }

    // TODO use function
    public function login_with_username($username, $password) {
        $user = $this->get_user_from_username($username);

        return $this->auth_step($user, $password);
    }

    public function login_with_email($email, $password) {
        $user = $this->get_user_from_email($email);

        return $this->auth_step($user, $password);
    }

    private function get_user_from(string $field, $value) {
        $query = $this->connection->createQueryBuilder();

        $query->select('*')->from('users')->where($field . ' = ?')->setParameter(0, $value);

        $users = $query->execute()->fetchAll();

        $this->assert_users_array($users);

        $user = $users[0];

        return $user;
    }

    public function get_user_uuid($user_uuid) {
        return $this->get_user_from('uuid', $user_uuid);
    }

    public function get_user_from_email($email) {
        return $this->get_user_from('email', $email);
    }

    public function get_user_from_username($username) {
        return $this->get_user_from('username', $username);
    }

    public function change_password($email, $old_password, $new_password) {
        // Authenticate user
        $user = $this->get_user_from_email($email);

        if (!$this->authenticate($user, $old_password)) {
            throw new LogicException('Invalid email/password combination.', 401);
        }

        // Verify that passwords are different
        if ($old_password === $new_password) {
            throw new LogicException('New password cannot be the same as old password!', 400);
        }

        // update password
        $query = $this->connection->createQueryBuilder();

        $query
        ->update('users')
        ->where('email = :email')
        ->set('password', ':password')
        ->setParameter('email', $email)
        ->setParameter('password', $this->hash_password($new_password));

        if(!$query->execute()) {
            throw new LogicException('Cannot change password \nSomething went wrong, please try again latter.', 500);
        }

        // TODO Change uuid to invalidate tokens
    }

    public function email_verify() {
        // TODO add email verification method
    }

    public function send_verification_email() {
        // TODO
    }

    public function delete_user($email, $password) {

    }

    private function assert_users_array($users) {
        $users_count = count($users);
        if ($users_count === 0) {
            throw new LogicException('You are not registered!', 401);
        } else if ($users_count > 1) {
            throw new LogicException('Something went wrong.', 500);
            // TODO add logging ERROR
        }
    }

    public function authenticate($user, $password) {
        return $user['password'] === $this->hash_password($password);
    }

    private function auth_step($user, $password) {
        if ($this->authenticate($user, $password)) {
            $jwt_settings = $this->container->get('settings')['jwt'];
            $signer = new Sha256();

            $token = (new Builder())
                        ->setIssuer($jwt_settings['issuer']) // Configures the issuer (iss claim)
                        ->setAudience($jwt_settings['audience']) // Configures the audience (aud claim)
                        ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
                        ->setNotBefore(time()) // Configures the time that the token can be used (nbf claim)
                        ->setExpiration(time() + 60 * 60 * 24 * 7) // Configures the expiration time of the token (exp claim) for one week
                        ->set('uuid', $user['uuid']) // Configures a new claim, called "uuid"
                        ->sign($signer, $jwt_settings['secret'])
                        ->getToken(); // Retrieves the generated token

            return $token;
        } else {
            throw new LogicException('Invalid email/password combination.', 401);
        }
    }

    private function hash_password($password) {
        return password_hash($password, PASSWORD_BCRYPT, [ 'salt' => $this->container->get('settings')['salt'] ]);
    }
}