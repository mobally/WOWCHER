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
     * @return string
     */
    public function customPostMethod($storeid,$email,$dob,$optin_url,$postcode,$co_sponsor,$living_social,$c_firstname,$c_lastname);
}