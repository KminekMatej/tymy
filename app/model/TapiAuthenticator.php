<?php

namespace App\Model;

use Nette;
use Tapi\LoginResource;
use Tapi\UserRegisterResource;
use Tymy\Exception\APIException;
use Nette\Security\Identity;
use InvalidArgumentException;

/**
 * Users management.
 */
class TapiAuthenticator implements Nette\Security\IAuthenticator {

    /** @var Supplier */
    private $supplier;
    
    public function __construct(Supplier $supplier) {
        $this->supplier = $supplier;
    }
    
    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
        $credentials[1] = md5($credentials[1]); // first login recodes password to md5 hash
        try {
            $loginObj = $this->reAuthenticate($credentials);
        } catch (APIException $ex) {
            return null;
        }

        $arr = (array) $loginObj->result;
        $arr["hash"] = $credentials[1];
        $arr["tapi_config"] = $this->supplier->getTapi_config();
        return new Identity($loginObj->result->data->id, $loginObj->result->data->roles, $arr );
    }
    
    /**
     * @throws APIException When something goes wrong
     * @param array $credentials
     * @return LoginResource
     */
    public function reAuthenticate(array $credentials){
        list($username, $password) = $credentials;
        $login = new LoginResource($this->supplier);
        return $login->setLogin($username)->setPassword($password)->getData();
    }
    
    /**
     * Adds new user.
     * @param  string
     * @param  string
     * @param  string
     * @param  string
     * @return void
     * @throws \Nette\InvalidArgumentException
     */
    public function add($login, $password, $email, $firstName = NULL, $lastName = NULL, $adminNote = NULL) {
        $register = new UserRegisterResource($this->supplier);
        try {
            $register
                    ->setLogin($login)
                    ->setPassword($password)
                    ->setEmail($email)
                    ->setFirstName($firstName)
                    ->setLastName($lastName)
                    ->setNote($adminNote)
                    ->perform();
        } catch (APIException $exc) {
            throw new InvalidArgumentException($exc->getMessage(), self::FAILURE);
        }
    }

}