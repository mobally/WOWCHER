<?php
namespace Rvs\CustomerApi\Api;
interface PostManagementInterface {
    /**
     * GET for Post api
     * @param string $storeid
     * @param string $name
     * @return string
     */
    public function customGetMethod($pagesize,$currentpage);
    /**
     * GET for Post api
     * @param string $storeid
     * @param string $name
     * @param string $city
     * @return string
     */
    
}
