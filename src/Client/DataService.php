<?php

namespace MostWebFramework\Client;
use MostWebFramework\Client\HttpClientException;
use MostWebFramework\Client\DynamicObject;
use Exception;

class DataService {

    public $headers;
    private $url;
    /**
     * @param string $url - A string that represents a remote URL that is going to be the target application.
     */
    public function __construct($url = null) {
        //set model
        $this->url = $url;
        $this->headers = array();
    }

    public  function getBase() {
        return $this->url;
    }

    /**
     * @param string $relativeUrl - A string that represents the relative URL of the target application.
     * @return array|stdClass|*
     * @throws Exception
     */
    public function get($relativeUrl) {
        return $this->execute('GET',$relativeUrl, null);
    }

    /**
     * @param string $relativeUrl - A string that represents the relative URL of the target application.
     * @param array|stdClass|* $data
     * @return array|stdClass|*
     * @throws Exception
     */
    public function post($relativeUrl, $data) {
        return $this->execute('POST',$relativeUrl, $data);
    }

    /**
     * @param string $relativeUrl - A string that represents the relative URL of the target application.
     * @param array|* $data
     * @return array|stdClass|*
     * @throws Exception
     */
    public function put($relativeUrl, $data) {
        return $this->execute('PUT',$relativeUrl, $data);
    }

    /**
     * @param string $relativeUrl - A string that represents the relative URL of the target application.
     * @param array|* $data
     * @return array|stdClass|*
     * @throws Exception
     */
    public function remove($relativeUrl, $data) {
        return $this->execute('DELETE',$relativeUrl, $data);
    }

    /**
     * @param string $relativeUrl - A string that represents the relative URL of the target application.
     * @param string $method
     * @param array|* $data
     * @return array|stdClass|*
     * @throws Exception
     */
    public function execute($method, $relativeUrl, $data) {
        try {
            if (is_null($this->url)) {
                throw new Exception('Target application base URL cannot be empty at this context.');
            }
            if (is_null($relativeUrl)) {
                throw new Exception('URL cannot be empty at this context.');
            }
            //build target url
            $url = $this->url . $relativeUrl;
            //initialize request
            $request = new HTTP_Request2($url, $method);
            try {
                $request->setHeader('Content-Type','application/json');
                if (!is_null($data))
                    $request->setBody(json_encode($data));
                $response = $request->send();
                if (200 == $response->getStatus()) {
                    //validate content type
                    $contentType = $response->getHeader('content-type');
                    if (strpos($contentType,'application/json')!=-1) {
                        //try to decode json
                        $res = json_decode($response->getBody());
                        return $res;
                    }
                    else {
                        return new DynamicObject();
                    }

                } else {
                    throw new HttpClientException($response->getReasonPhrase(),$response->getStatus());
                }
            } catch (HTTP_Request2_Exception $e) {
                throw new HttpClientException($e->getMessage(),$e->getCode());
            }
        }
        catch(HttpException $e) {
            throw $e;
        }
        catch(Exception $e) {
            print $e;
            throw new HttpClientException('Internal Server Error',500);
        }
    }

}