<?php 

/**
 * This class wraps the parse_url function provided by PHP, and gives an OO approach
 * to manipulating a URL. 
 */
class URL {

    public $scheme;
    public $host;
    public $port;
    public $user;
    public $pass;
    public $path;
    public $fragment;
    //Use get/setQuery
    private $query;

    public function __construct($url = null) {
        if ($url == null) {
            return; //They may be building it on their own
        }
        $parsed = parse_url($url);
        if ($parsed === false) {
            throw new Exception("Seriously malformed url, and cannot build URL object: $url");
        }
        $this->scheme = $parsed['scheme'];
        $this->host = $parsed['host'];
        $this->port = $parsed['port'];
        $this->user = $parsed['user'];
        $this->pass = $parsed['pass'];
        $this->path = $parsed['path'];
        parse_str($parsed['query'], $this->query);
        $this->fragment = $parsed['fragment'];
    }

    public function getQuery() {
        return $this->query;
    }

    /**
     * Sets the query parameter, overriding the old one(s) if needed. The value is
     * url encoded, so you can send unescaped text. (You may disable this by setting
     * encode to false.)
     * @param type $name
     * @param type $value 
     */
    public function setQueryParam($name, $value, $encode = true) {
        if ($encode) {
            $value = urlencode($value);
        }
        $this->query[$name] = $value;
        return $this;
    }

    /**
     * Adds a query parameter, turning it into an array if needed.
     * @param type $name
     * @param type $value 
     */
//	public function addQueryParam($name, $value) {
//		//TODO: Finish this, if finer granularity is desired
//	}

    /**
     * Returns the value of a query parameter, which could be an array, or null, if nothing is set
     * @param type $name 
     */
    public function getQueryParams($name) {
        if (isset($this->query[$name])) {
            return $this->query[$name];
        } else {
            return null;
        }
    }

    public function build() {
        $render = "";
        $scheme = "";
        if (trim($this->scheme) != "") {
            $scheme = trim($this->scheme);
        }
        if ($scheme != null) {
            $render .= $scheme . '://';
        }
        if ($scheme == "file") {
            $render .= '/';
        }
        if (trim($this->user) != "") {
            $render .= $this->user;
            if (trim($this->pass) != "") {
                $render .= ':' . $this->pass;
            }
            $render .= '@';
        }
        if (trim($this->host) != "") {
            $render .= $this->host;
        }
        if (trim($this->path) != "") {
            $render .= $this->path;
        }
        if (count($this->query) != 0) {
            $render .= "?";
            $first = true;
            foreach ($this->query as $name => $value) {
                if (!$first) {
                    $render .= "&";
                }
                $first = false;
                $render .= $name . '=' . $value;
            }
        }
        if (trim($this->fragment) != "") {
            $render .= '#' . $this->fragment;
        }
        return $render;
    }

}
?>
