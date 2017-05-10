<?php

namespace App\Model;

use Nette;
use Nette\Utils\Json;
use Nette\SmartObject;

/**
 * Users management.
 */
class TymyUserManager implements Nette\Security\IAuthenticator {

    private $tym;

    public function __construct($tym) {
        $this->tym = $tym;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($username, $password) = $credentials;
        \Tracy\Debugger::barDump($this);
        $loginObj = new \Tymy\Login(NULL);
        $loginObj->setUsername($username)
                 ->setPassword($password)
                ->fetch();
        $stop();
        $user = $loginObj;
        
        if ($user->status != "OK") {
            throw new Nette\Security\AuthenticationException('Login failed.', self::INVALID_CREDENTIAL);
        }
        
        $arr = (array)$user->data;
        $arr["tsid"] = $user->sessionKey;
        $arr["tym"] = $this->tym;

        return new Nette\Security\Identity($user->data->id, $user->data->status, $arr );
    }
    
    private function execute($url) {
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        curl_close($ch);  
        return $output;
    }
    
    //TODO register new user
    //TODO password lost
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