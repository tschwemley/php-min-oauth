<?php

namespace tschwemley\OAuth;

class OAuth {

    private $clientId;

    private $clientSecret;

    private $authorizeEndpoint;

    private $accessEndpoint;

    private $redirectUri;

    private $scopes;

    private $state;

    /**
     * Constructor
     *
     * @param mixed $clientId client id for oauth connection
     * @param mixed $authorizeEndpoint OAuth2 authorization endpoint
     * @param mixed $accessEndpoint OAuth2 access endoint
     */
    public function __construct($clientId, $clientSecret, $authorizeEndpoint, $accessEndpoint)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->authorizeEndpoint = $authorizeEndpoint;
        $this->accessEndpoint = $accessEndpoint;
    }

    /**
     * Get authorizatoin URL and either return it or redirect to it.
     *
     * @param boolean $redirect
     * @param array|null $optionalParams
     *
     * @return string|void
     */
    public function authorize($redirect=true, $optionalParams=null)
    {
        $params = array(
            'response_type' => 'code',
            'client_id' => $this->clientId,
        );
        $params = array_merge($params, $optionalParams);

        // Construct params string
        $paramsString = '?';
        foreach ($params as $key => $param) {
            $paramsString .= "$key=" . urlencode($param) . '&';
        }

        $authorizeUri = $this->authorizeEndpoint . $paramsString;

        if ($redirect) {
            header("Location: $authorizeUri");
        } else {
            return $authorizeUri;
        }
    }

    /**
     * Get access token.
     *
     * @param mixed $code
     * @param array $optionalParams
     */
    public function getAccessToken($code, $optionalParams=array())
    {
        $params = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        );
        $params = array_merge($params, $optionalParams);

        $accessTokenResponse = $this->_call('POST', $params, null);
        echo $accessTokenResponse;
        exit;
    }

    /**
     * Curls OAuth endpoint
     *
     * @param string $method
     * @param mixed $params
     * @param mixed $header
     */
    private function _call($method, $params=null, $header=null)
    {
        $url = $this->accessEndpoint;
        $ch = curl_init($url);
        $this->_setCurlOpts($ch, $method, $params);

        $result = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        // TODO: Handle errors
        if ($error || $errno || $httpCode != 200) {
            echo 'error';
            exit;
        }

        return substr($result, $headerSize);
    }

    /**
     * Sets curl options
     *
     * @param mixed $ch
     * @param string $method
     * @param mixed $params
     *
     * @return void
     */
    private function _setCurlOpts($ch, $method, $params)
    {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method != 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
    }
}
