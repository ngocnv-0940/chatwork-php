<?php

namespace SunAsterisk\Chatwork;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use SunAsterisk\Chatwork\Auth\Auth;
use SunAsterisk\Chatwork\Exceptions\APIException;
use function GuzzleHttp\json_decode;

/**
 * @method array me()
 */
class Chatwork
{
    /** @Client */
    protected $client;

    const API_URI = 'https://api.chatwork.com';
    const API_VERSION = 'v2';

    /**
     * Create an API client
     *
     * @param string $apiToken
     * @param array $options
     */
    public function __construct(Auth $auth)
    {
        $this->client = new Client([
            'base_uri' => static::API_URI.'/'.static::API_VERSION.'/',
            'timeout' => 30,
            'headers' => array_merge([
                'Accept' => 'application/json',
            ], $auth->getHeaders()),
        ]);
    }

    public function __call($name, $arguments)
    {
        $endpointClass = __NAMESPACE__.'\\Endpoints\\'.ucfirst($name);
        $endpoint = new $endpointClass($this, ...$arguments);

        return is_callable($endpoint) ? $endpoint() : $endpoint;
    }

    /**
     * @param  string $uri
     * @param  array $query
     * @return array
     */
    public function get(string $uri, array $query = [])
    {
        return $this->request('GET', $uri, [
            'query' => $query,
        ]);
    }

    /**
     * @param  string $uri
     * @param  array $data
     * @return array
     */
    public function post(string $uri, array $data = [])
    {
        return $this->request('POST', $uri, [
            'form_params' => $data,
        ]);
    }

    /**
     * @param  string $uri
     * @param  array $data
     * @return array
     */
    public function put(string $uri, array $data = [])
    {
        return $this->request('PUT', $uri, [
            'form_params' => $data,
        ]);
    }

    /**
     * @param  string $uri
     * @param  array $data
     * @return array
     */
    public function patch(string $uri, array $data = [])
    {
        return $this->request('PATCH', $uri, [
            'form_params' => $data,
        ]);
    }

    /**
     * @param  string $uri
     * @param  array $query
     */
    public function delete(string $uri, array $query = [])
    {
        $this->request('DELETE', $uri, [
            'query' => $query,
        ]);
    }

    /**
     * @param  string $method
     * @param  string $uri
     * @param  array $options
     * @return array
     */
    public function request(string $method, $uri = '', array $options = [])
    {
        try {
            $res = $this->client->request($method, $uri, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = json_decode($response->getBody()->getContents(), true);

            throw new APIException($response->getStatusCode(), $body);
        }

        return json_decode($res->getBody()->getContents(), true);
    }
}
