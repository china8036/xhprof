<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Qqes\Xhprof;

/**
 * Description of Request
 *
 * @author wang
 */
class Request {

	private $_requestUri;
	private $_pathInfo;
	private $_scriptUrl;
	private $_hostInfo;
	private $_baseUrl;

    /**
     * Returns the relative URL for the application.
     * This is similar to {@link getScriptUrl scriptUrl} except that
     * it does not have the script file name, and the ending slashes are stripped off.
     * @param boolean $absolute whether to return an absolute URL. Defaults to false, meaning returning a relative one.
     * @return string the relative URL for the application
     * @see setScriptUrl
     */
    public function getBaseUrl($absolute = false) {
        if ($this->_baseUrl === null)
            $this->_baseUrl = rtrim(dirname($this->getScriptUrl()), '\\/');
        return $absolute ? $this->getHostInfo() . $this->_baseUrl : $this->_baseUrl;
    }

    /**
     * Returns the schema and host part of the application URL.
     * The returned URL does not have an ending slash.
     * By default this is determined based on the user request information.
     * You may explicitly specify it by setting the {@link setHostInfo hostInfo} property.
     * @param string $schema schema to use (e.g. http, https). If empty, the schema used for the current request will be used.
     * @return string schema and hostname part (with port number if needed) of the request URL (e.g. http://www.yiiframework.com)
     * @see setHostInfo
     */
    public function getHostInfo($schema = '') {
        if ($this->_hostInfo === null) {
            if ($secure = $this->getIsSecureConnection())
                $http = 'https';
            else
                $http = 'http';
            if (isset($_SERVER['HTTP_HOST']))
                $this->_hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
            else {
                $this->_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
                $port = $secure ? $this->getSecurePort() : $this->getPort();
                if (($port !== 80 && !$secure) || ($port !== 443 && $secure))
                    $this->_hostInfo .= ':' . $port;
            }
        }
        if ($schema !== '') {
            $secure = $this->getIsSecureConnection();
            if ($secure && $schema === 'https' || !$secure && $schema === 'http')
                return $this->_hostInfo;

            $port = $schema === 'https' ? $this->getSecurePort() : $this->getPort();
            if ($port !== 80 && $schema === 'http' || $port !== 443 && $schema === 'https')
                $port = ':' . $port;
            else
                $port = '';

            $pos = strpos($this->_hostInfo, ':');
            return $schema . substr($this->_hostInfo, $pos, strcspn($this->_hostInfo, ':', $pos + 1) + 1) . $port;
        } else
            return $this->_hostInfo;
    }

    /**
     * Returns the port to use for secure requests.
     * Defaults to 443, or the port specified by the server if the current
     * request is secure.
     * You may explicitly specify it by setting the {@link setSecurePort securePort} property.
     * @return integer port number for secure requests.
     * @see setSecurePort
     * @since 1.1.3
     */
    public function getSecurePort() {
        if ($this->_securePort === null)
            $this->_securePort = $this->getIsSecureConnection() && isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : 443;
        return $this->_securePort;
    }

    /**
     * Return if the request is sent via secure channel (https).
     * @return boolean if the request is sent via secure channel (https)
     */
    public function getIsSecureConnection() {
        return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0;
    }

    /**
     * Returns the relative URL of the entry script.
     * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
     * @throws CException when it is unable to determine the entry script URL.
     * @return string the relative URL of the entry script.
     */
    public function getScriptUrl() {
        if ($this->_scriptUrl === null) {
            $scriptName = basename($_SERVER['SCRIPT_FILENAME']);
            if (basename($_SERVER['SCRIPT_NAME']) === $scriptName)
                $this->_scriptUrl = $_SERVER['SCRIPT_NAME'];
            elseif (basename($_SERVER['PHP_SELF']) === $scriptName)
                $this->_scriptUrl = $_SERVER['PHP_SELF'];
            elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName)
                $this->_scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
            elseif (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false)
                $this->_scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
            elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0)
                $this->_scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
            else
                return 'CHttpRequest is unable to determine the entry script URL.';
        }
        return $this->_scriptUrl;
    }

    /**
     * Returns the request URI portion for the currently requested URL.
     * This refers to the portion that is after the {@link hostInfo host info} part.
     * It includes the {@link queryString query string} part if any.
     * The implementation of this method referenced Zend_Controller_Request_Http in Zend Framework.
     * @return string the request URI portion for the currently requested URL.
     * @throws CException if the request URI cannot be determined due to improper server configuration
     */
    public function getRequestUri() {
        if ($this->_requestUri === null) {
            if (isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS
                $this->_requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
            elseif (isset($_SERVER['REQUEST_URI'])) {
                $this->_requestUri = $_SERVER['REQUEST_URI'];
                if (!empty($_SERVER['HTTP_HOST'])) {
                    if (strpos($this->_requestUri, $_SERVER['HTTP_HOST']) !== false)
                        $this->_requestUri = preg_replace('/^\w+:\/\/[^\/]+/', '', $this->_requestUri);
                } else
                    $this->_requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $this->_requestUri);
            }
            elseif (isset($_SERVER['ORIG_PATH_INFO'])) {  // IIS 5.0 CGI
                $this->_requestUri = $_SERVER['ORIG_PATH_INFO'];
                if (!empty($_SERVER['QUERY_STRING']))
                    $this->_requestUri .= '?' . $_SERVER['QUERY_STRING'];
            } else
                return 'CHttpRequest is unable to determine the request URI.';
        }

        return $this->_requestUri;
    }

    /**
     * Returns the path info of the currently requested URL.
     * This refers to the part that is after the entry script and before the question mark.
     * The starting and ending slashes are stripped off.
     * @return string part of the request URL that is after the entry script and before the question mark.
     * Note, the returned pathinfo is decoded starting from 1.1.4.
     * Prior to 1.1.4, whether it is decoded or not depends on the server configuration
     * (in most cases it is not decoded).
     * @throws CException if the request URI cannot be determined due to improper server configuration
     */
    public function getPathInfo() {
        if ($this->_pathInfo === null) {
            $pathInfo = $this->getRequestUri();

            if (($pos = strpos($pathInfo, '?')) !== false)
                $pathInfo = substr($pathInfo, 0, $pos);

            $pathInfo = $this->decodePathInfo($pathInfo);

            $scriptUrl = $this->getScriptUrl();
            $baseUrl = $this->getBaseUrl();
            if (strpos($pathInfo, $scriptUrl) === 0)
                $pathInfo = substr($pathInfo, strlen($scriptUrl));
            elseif ($baseUrl === '' || strpos($pathInfo, $baseUrl) === 0)
                $pathInfo = substr($pathInfo, strlen($baseUrl));
            elseif (strpos($_SERVER['PHP_SELF'], $scriptUrl) === 0)
                $pathInfo = substr($_SERVER['PHP_SELF'], strlen($scriptUrl));
            else
                return 'CHttpRequest is unable to determine the path info of the request.';

            if ($pathInfo === '/' || $pathInfo === false)
                $pathInfo = '';
            elseif ($pathInfo !== '' && $pathInfo[0] === '/')
                $pathInfo = substr($pathInfo, 1);

            if (($posEnd = strlen($pathInfo) - 1) > 0 && $pathInfo[$posEnd] === '/')
                $pathInfo = substr($pathInfo, 0, $posEnd);

            $this->_pathInfo = $pathInfo;
        }
        return $this->_pathInfo;
    }

    /**
     * Decodes the path info.
     * This method is an improved variant of the native urldecode() function and used in {@link getPathInfo getPathInfo()} to
     * decode the path part of the request URI. You may override this method to change the way the path info is being decoded.
     * @param string $pathInfo encoded path info
     * @return string decoded path info
     * @since 1.1.10
     */
    protected function decodePathInfo($pathInfo) {
        $pathInfo = urldecode($pathInfo);

        // is it UTF-8?
        // http://w3.org/International/questions/qa-forms-utf-8.html
        if (preg_match('%^(?:
		   [\x09\x0A\x0D\x20-\x7E]            # ASCII
		 | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		 | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
		 | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		 | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
		 | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
		 | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		 | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
		)*$%xs', $pathInfo)) {
            return $pathInfo;
        } else {
            return utf8_encode($pathInfo);
        }
    }

    /**
     * 
     * @return array
     */
    public function getPostData() {
        return $_POST;
    }

}
