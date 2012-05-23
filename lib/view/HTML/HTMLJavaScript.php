<?php 
include_once(dirname(__FILE__)."/HTMLScript.php");
/**
 * This class represents a script tag, but it is specific for javascript. 
 */
class HTMLJavaScript extends HTMLScript {

    /**
     * Creates a new HTMLJavascript element, which can be either internal or external 
     */
    public function __construct() {
        parent::__construct(null);
        $this->setAttribute("type", "text/javascript");
    }

    /**
     * Sets the script to be an external script. If an internal script was already
     * added, it is removed, and a warning is raised.
     * @param type $url 
     */
    public function setExternal($url) {
        if ($this->script != null) {
            trigger_error("Adding external script to element that already has an internal script!", E_USER_WARNING);
        }
        $this->script = null;
        if ($url instanceof URL) {
            $url = $url->build();
        }
        $this->setAttribute("src", $url);
        return $this;
    }

    /**
     * Sets the script to be inline. If an external script was already added, it is removed,
     * and a warning is raised.
     * @param type $script 
     */
    public function setInline($script) {
        if ($this->getAttribute("src") !== null) {
            trigger_error("Adding inline script to element that already has an external script!", E_USER_WARNING);
        }
        $this->script = $script;
        $this->removeAttribute("src");
        return $this;
    }

}
?>
