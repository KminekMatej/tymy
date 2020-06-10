<?php

namespace App\Model;

use Nette;
use Nette\Security\Identity;
use Tapi\Exception\APIException;
use Tapi\LoginResource;
use Tapi\LoginTkResource;
use Tapi\TapiService;

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
        return new Identity($loginObj->id, $loginObj->roles, $loginObj);
    }
    
    /**
     * @throws APIException When something goes wrong
     * @param array $credentials
     * @return LoginResource
     */
    public function reAuthenticate(array $credentials){
        list($username, $password) = $credentials;
        $loginResource = new LoginResource($this->supplier, NULL, $this->tapiService);
        return $loginResource->setLogin($username)->setPassword($password)->getData();
    }
    
    /**
     * @throws APIException When something goes wrong
     * @param string $tk Transfer key
     * @return Identity
     */
    public function tkAuthenticate($tk){
        $loginTkResource = new LoginTkResource($this->supplier, NULL, $this->tapiService);
        $loginObj = $loginTkResource->setTk($tk)->getData();
        $loginObj->user->tapi_config = $this->supplier->getTapi_config();
        return new Identity($loginObj->user->id, $loginObj->user->roles, $loginObj->user );
    }

}