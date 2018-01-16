<?php

namespace App\Model;

use Nette;
use Tapi\LoginResource;
use Tapi\TapiService;
use Tapi\Exception\APIException;
use Nette\Security\Identity;
use InvalidArgumentException;

/**
 * Users management.
 */
class TapiAuthenticator implements Nette\Security\IAuthenticator {

    /** @var Supplier */
    private $supplier;
    
    /** @var TapiService */
    private $tapiService;
    
    public function __construct(Supplier $supplier) {
        $this->supplier = $supplier;
    }

    public function getTapiService() {
        return $this->tapiService;
    }

    public function setTapiService(TapiService $tapiService) {
        $this->tapiService = $tapiService;
        return $this;
    }
    
    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
        $credentials[1] = md5($credentials[1]); // first login recodes password to md5 hash
        $loginObj = $this->reAuthenticate($credentials);
        $loginObj->hash = $credentials[1];
        $loginObj->tapi_config = $this->supplier->getTapi_config();
        return new Identity($loginObj->id, $loginObj->roles, $loginObj );
    }
    
    /**
     * @throws APIException When something goes wrong
     * @param array $credentials
     * @return LoginResource
     */
    public function reAuthenticate(array $credentials){
        list($username, $password) = $credentials;
        $loginResource = new LoginResource($this->supplier, NULL, NULL, $this->tapiService);
        return $loginResource->setLogin($username)->setPassword($password)->getData();
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
        try {
            $this->registerService
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