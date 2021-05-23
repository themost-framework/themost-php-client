<?php

namespace MostWebFramework\Client;
use MostWebFramework\Client\DataService;

class DataContext {
    private $url;

    private $service;

    /**
     * DataService class constructor.
     * @param string $url - A string that represents a remote URL that is going to be the target application.
     */
    public function __construct($url) {
        //set model
        $this->url = $url;
        $this->service = new DataService($this->url);
    }

    /**
     * Gets an instance of DataModel class based on the specified model name
     * @param string $name
     * @throws Exception
     * @return DataModel
     */
    function model($name) {
        Args::notNull($name, "Model name");
        return new DataModel($name,$this->service);
	}

	/**
	* Gets the instance of DataService which is associated with this data context.
	* @return DataService
	*/
	public function getService() {
		return $this->service;
    }
    
    public function setBearerAuthorization($value) {
        $headers = $this->getService()->headers;
        $headers["Authorization"] = "Bearer " . $value;
    }

    public function setBasicAuthorization($value) {
        $headers = $this->getService()->headers;
        $headers["Authorization"] = "Basic " .$value;
    }

}