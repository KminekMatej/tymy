<?php

namespace App\Model;

use Nette;

/**
 * Users management.
 */
class UserManager implements Nette\Security\IAuthenticator {

    use Nette\SmartObject;

    const
            TABLE_NAME = 'users',
            COLUMN_ID = 'id',
            COLUMN_NAME = 'user_name',
            COLUMN_PASSWORD_HASH = 'password',
            COLUMN_ROLE = 'status';

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($username, $password) = $credentials;

        $row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        } elseif (!$this->pswdcmp($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        }

        $arr = $row->toArray();
        unset($arr[self::COLUMN_PASSWORD_HASH]);
        return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
    }

    private function pswdcmp($userpswd, $cryptedpswd) {
        if (preg_match('/^\$1\$.{8}\$/', $cryptedpswd)) {
            //MD5 password
            return strcmp(crypt($userpswd, substr($cryptedpswd, 0, 12)), $cryptedpswd);
        } else {
            //8 chars DES
            return strcmp(crypt($userpswd, substr($cryptedpswd, 0, 2)), $cryptedpswd);
        }
    }
    
    private function makesalt($type = CRYPT_SALT_LENGTH) {
        switch ($type) {
            case 12:
                $saltlen = 9;
                $saltprefix = '$1$';
                $saltsuffix = '$';
                break;
            case 2:
            default: // by default, fall back on Standard DES (should work everywhere)
                $saltlen = 2;
                $saltprefix = '';
                $saltsuffix = '';
                break;
            #
        }
        $salt = '';
        while (strlen($salt) < $saltlen)
            $salt.=chr(rand(64, 126));
        return $saltprefix . $salt . $saltsuffix;
    }

    /**
     * Adds new user.
     * @param  string
     * @param  string
     * @param  string
     * @return void
     * @throws DuplicateNameException
     */
    public function add($username, $password) {
        try {
            SRand(time());
            $this->database->table(self::TABLE_NAME)->insert([
                self::COLUMN_NAME => $username,
                self::COLUMN_PASSWORD_HASH => crypt($password, makesalt()),
            ]);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }
    }

}

class DuplicateNameException extends \Exception {
    
}
