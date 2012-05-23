<?php 
include_once(dirname(__FILE__)."/HTMLContainer.php");
/**
 * This class represents a meta tag, and has special handling for adding meta content. 
 */
class HTMLMeta extends HTMLContainer {

    public function __construct($content, $name = null, $http_equiv = null, $scheme = null) {
        $this->setAttribute("content", $content);
        if ($name !== null) {
            $this->setAttribute("name", $name);
        }
        if ($http_equiv !== null) {
            $this->setAttribute("http-equiv", $http_equiv);
        }
        if ($scheme !== null) {
            $this->setAttribute("scheme", $scheme);
        }
    }

    public function appendAttribute($name, $content) {
        switch ($name) {
            case "name":
                if ($this->getAttribute("http-equiv") != null) {
                    trigger_error("Adding 'name' attribute to HTMLMeta, when 'http-equiv' attribute already exists", E_USER_WARNING);
                }
                $this->removeAttribute("name");
                break;
            case "http-equiv":
                if ($this->getAttribute("name") != null) {
                    trigger_error("Adding 'http-equiv' attribute to HTMLMeta, when 'name' attribute already exists", E_USER_WARNING);
                }
                $this->removeAttribute("http-equiv");
                break;
            case "scheme":
                $this->removeAttribute("scheme");
                break;
            default:
                break;
        }
        parent::appendAttribute($name, $content);
        return $this;
    }

    protected function getContent() {
        if ($this->getAttribute("http-equiv") !== null && in_array($this->getAttribute("http-equiv"), self::$strictHttpEquiv)) {
            $this->inspectHttpEquiv($this->getAttribute("http-equiv"), $this->getAttribute("content"));
        }
        if ($this->getAttribute("name") !== null && in_array($this->getAttribute("name"), self::$strictName)) {
            $this->inspectName($this->getAttribute("name"), $this->getAttribute("content"));
        }
        return null;
    }

    private static $strictHttpEquiv = array("cache-control", "refresh");
    private static $validCacheControl = array("public", "private", "no-cache", "no-store");

    private function inspectHttpEquiv($equiv, $content) {
        switch ($equiv) {
            case "cache-control":
                if (!in_array($content, self::$validCacheControl)) {
                    trigger_error("Invalid content set for meta tag 'cache-control'.", E_USER_WARNING);
                }
                break;
            case "refresh":
                if (!is_integer($content)) {
                    trigger_error("Invalid content set for meta tag 'refresh'. Expected integral value.", E_USER_WARNING);
                }
                break;
            default:
                break;
        }
    }

    private static $strictName = array("distribution", "googlebot", "robots");
    private static $validDistribution = array("web", "intranet");
    private static $validGooglebot = array("noarchive", "nofollow", "noindex", "nosnippet");
    private static $validRobots = array("ALL", "FOLLOW", "INDEX", "NOARCHIVE", "NOINDEX", "NOFOLLOW", "NONE");

    private function inspectName($name, $content) {
        switch ($name) {
            case "distribution":
                if (!in_array($content, self::$validDistribution)) {
                    trigger_error("Invalid content in 'distribution' meta tag.", E_USER_WARNING);
                }
                break;
            case "googlebot":
                if (!in_array($content, self::$validGooglebot)) {
                    trigger_error("Invalid content in 'googlebot' meta tag.", E_USER_WARNING);
                }
                break;
            case "robots":
                $split = preg_split("/,/", $content);
                foreach ($split as $item) {
                    if (!in_array($item, self::$validRobots)) {
                        trigger_error("Invalid content '$item' in 'robots' meta tag.", E_USER_WARNING);
                    }
                }
                break;
            default:
                break;
        }
    }

    protected function getTagName() {
        return "meta";
    }

}
?>
