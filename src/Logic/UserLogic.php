<?php
namespace App\Logic;

use Slim\Container;

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

    public function login_with_username($username, $password) {
        $query = $this->connection->createQueryBuilder();

        $query->from('users')->where('username = :username')->setParameter('username', $username);
        $users = $query->execute()->fetchAll();

        $this->check_users_array($users);

        $user = $users[0];
        return $this->auth_step($user, $password);
    }

    public function login_with_email($email, $password) {
        $query = $this->connection->createQueryBuilder();

        $query->from('users')->where('email = :email')->setParameter('email', $email);
        $users = $query->execute()->fetchAll();

        $this->check_users_array($users);

        $user = $users[0];
        return $this->auth_step($user, $password);
    }

    public function change_password($email, $password, $new_password) {
        // TODO implement logic
        // 1. Authenticate user
        // 2.1 Change uuid
        // 2.2 Change password
    }

    private function check_users_array($users) {
        $users_count = count($users);
        if ($users_count === 0) {
            throw new Exception('You are not registered.');
        } else if ($users_count > 1) {
            throw new Exception('Something went wrong.');
            // TODO add logging ERROR
        }
    }

    private function auth_step($user, $password) {
        // TODO return JWT token
        if ($user['password'] === $this->hash_password($password)) {
            return true;
        } else {
            return false;
        }
    }

    private function hash_password($password) {
        return password_hash($password, PASSWORD_BCRYPT, [ 'salt' => $this->container->get('settings')['salt'] ]);
    }
}