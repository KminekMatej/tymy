<?php

namespace Tapi;

/**
 * Description of User
 *
 * @author kminekmatej
 */
class User extends TapiEntity {
    
    private $login;
    private $canLogin;
    private $status;
    private $roles;
    private $firstName;
    private $lastName;
    private $callName;
    private $language;
    private $email;
    private $jerseyNumber;
    private $gender;
    private $street;
    private $city;
    private $zipCode;
    private $phone;
    private $phone2;
    private $birthDate;
    private $nameDayMonth;
    private $nameDayDay;
    private $pictureUrl;
    private $fullName;
    private $displayName;
    private $webName;
    private $errCnt;
    private $errFls;
    
    public function getLogin() {
        return $this->login;
    }

    public function getCanLogin() {
        return $this->canLogin;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getRoles() {
        return $this->roles;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getCallName() {
        return $this->callName;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getJerseyNumber() {
        return $this->jerseyNumber;
    }

    public function getGender() {
        return $this->gender;
    }

    public function getStreet() {
        return $this->street;
    }

    public function getCity() {
        return $this->city;
    }

    public function getZipCode() {
        return $this->zipCode;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getPhone2() {
        return $this->phone2;
    }

    public function getBirthDate() {
        return $this->birthDate;
    }

    public function getNameDayMonth() {
        return $this->nameDayMonth;
    }

    public function getNameDayDay() {
        return $this->nameDayDay;
    }

    public function getPictureUrl() {
        return $this->pictureUrl;
    }

    public function getFullName() {
        return $this->fullName;
    }

    public function getDisplayName() {
        return $this->displayName;
    }

    public function getWebName() {
        return $this->webName;
    }

    public function getErrCnt() {
        return $this->errCnt;
    }

    public function getErrFls() {
        return $this->errFls;
    }

    public function setLogin($login) {
        $this->login = $login;
        return $this;
    }

    public function setCanLogin($canLogin) {
        $this->canLogin = $canLogin;
        return $this;
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function setRoles($roles) {
        $this->roles = $roles;
        return $this;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
        return $this;
    }

    public function setCallName($callName) {
        $this->callName = $callName;
        return $this;
    }

    public function setLanguage($language) {
        $this->language = $language;
        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function setJerseyNumber($jerseyNumber) {
        $this->jerseyNumber = $jerseyNumber;
        return $this;
    }

    public function setGender($gender) {
        $this->gender = $gender;
        return $this;
    }

    public function setStreet($street) {
        $this->street = $street;
        return $this;
    }

    public function setCity($city) {
        $this->city = $city;
        return $this;
    }

    public function setZipCode($zipCode) {
        $this->zipCode = $zipCode;
        return $this;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
        return $this;
    }

    public function setPhone2($phone2) {
        $this->phone2 = $phone2;
        return $this;
    }

    public function setBirthDate($birthDate) {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function setNameDayMonth($nameDayMonth) {
        $this->nameDayMonth = $nameDayMonth;
        return $this;
    }

    public function setNameDayDay($nameDayDay) {
        $this->nameDayDay = $nameDayDay;
        return $this;
    }

    public function setPictureUrl($pictureUrl) {
        $this->pictureUrl = $pictureUrl;
        return $this;
    }

    public function setFullName($fullName) {
        $this->fullName = $fullName;
        return $this;
    }

    public function setDisplayName($displayName) {
        $this->displayName = $displayName;
        return $this;
    }

    public function setWebName($webName) {
        $this->webName = $webName;
        return $this;
    }

    public function setErrCnt($errCnt) {
        $this->errCnt = $errCnt;
        return $this;
    }

    public function setErrFls($errFls) {
        $this->errFls = $errFls;
        return $this;
    }


    
    
    
}
