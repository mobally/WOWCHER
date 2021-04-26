<?php
namespace Rvs\SubscribePostApi\Api;
interface PostManagementInterface {
    /**
     * GET for Post api
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @param string $dob
     * @param string $optin_url
     * @param string $postcode
     * @param string $co_sponsor
     * @param string $living_social
     * @param string $c_firstname
     * @param string $c_lastname
     * @param string $storeid
     * @param string $gender
     * @param string $competition
     * @return string
     */
    public function customPostMethod($storeid,$email,$dob,$optin_url,$postcode,$co_sponsor,$c_firstname,$c_lastname,$gender,$competition);
}
