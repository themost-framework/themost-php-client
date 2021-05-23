<?php

namespace MostWebFramework\Client;

use MostWebFramework\Client\Args;
use MostWebFramework\Client\DataQueryable;

class DataModel {
    private $name;
    private $url;
    private $service;
    /**
     * ClientDataModel class constructor.
     * @param string $name - A string which represents the name of this model.
     * @param DataService $service - An instance of DataService that is going to be used in data requests.
     */
    public function __construct($name, $service) {
        Args::notNull($name, "Model name");
        $this->name = $name;
        $this->url = "/$name/index.json";
        $this->service = $service;
    }

    /**
     * Gets the name of this data model
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Gets the URL which is associated with this data model
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Sets the URL for this data model
     * @param string $url
     * @return string
     */
    public function setUrl($url) {
        Args::notNull($url,"Model URL");
        Args::check(preg_match("/^https?:\\/\\//i",$url),"Request URL may not be an absolute URI");
        if (preg_match("/^\\//i", $url))
            $this->url = $url;
        else
        {
            $this->url = "/".$this->getName()."/".$url;
        }
    }

    /**
     * Gets the instance of DataService which is associated with this data model.
     * @return DataService
     */
    public function getService() {
        return $this->service;
    }

    /**
     * Gets the schema of this data model
     * @return stdClass
     * @throws HttpClientException
     * @throws HttpException
     */
    public function getSchema() {
        $model = $this->getName();
        return $this->getService()->execute("GET", "/$model/schema.json", null);
    }

    /**
     * @param string $field
     * @return DataQueryable
     */
    public function where($field) {
        Args::notNull($field,"Field");
        $res = new DataQueryable($this->getName());
        $res->setService($this->getService());
        return $res->where($field);
    }

    /**
     * @param ...string $field
     * @return DataQueryable
     */
    public function select($field) {
        Args::notNull($field,"Field");
        $res = new DataQueryable($this->getName());
        $res->setService($this->getService());
        return call_user_func_array(array($res, "select"), func_get_args());
    }

    /**
     * @param ...string $field
     * @return DataQueryable
     */
    public function expand($field) {
        Args::notNull($field,"Field");
        $res = new DataQueryable($this->getName());
        $res->setService($this->getService());
        return call_user_func_array(array($res, "expand"), func_get_args());
    }

    /**
     * @param ...string $field
     * @return DataQueryable
     */
    public function orderBy($field) {
        Args::notNull($field,"Field");
        $res = new DataQueryable($this->getName());
        $res->setService($this->getService());
        return call_user_func_array(array($res, "orderBy"), func_get_args());
    }

    /**
     * @param ...string $field
     * @return DataQueryable
     */
    public function orderByDescending($field) {
        Args::notNull($field,"Field");
        $res = new DataQueryable($this->getName());
        $res->setService($this->getService());
        return call_user_func_array(array($res, "orderByDescending"), func_get_args());
    }

    /**
     * @param int $num
     * @return DataQueryable
     */
    public function skip($num) {
        Args::notNull($num,"Skip argument");
        $res = new DataQueryable($this->getName());
        $res->setService($this->getService());
        return $res->skip($num);
    }

    /**
     * @param int $num
     * @return DataQueryable
     */
    public function take($num) {
        Args::notNull($num,"Skip argument");
        $res = new DataQueryable($this->getName());
        $res->setService($this->getService());
        return $res->take($num);
    }

    /**
     * @return stdClass[]
     */
    public function getItems() {
        $res = new DataQueryable($this->getName());
        $res->setService($this->getService());
        return $res->getItems();
    }

    /**
     * @param * $data
     * @return array|stdClass|DynamicObject
     * @throws Exception
     * @throws HttpClientException
     * @throws HttpException
     */
    public function save($data) {
        Args::notNull($this->getService(),"Client service");
        return $this->getService()->execute("POST", $this->getUrl(), $data);
    }

    /**
     * @param * $data
     * @return array|stdClass|DynamicObject
     * @throws Exception
     * @throws HttpClientException
     * @throws HttpException
     */
    public function remove($data) {
        Args::notNull($this->getService(),"Client service");
        return $this->getService()->execute("DELETE", $this->getUrl(), $data);
    }
}