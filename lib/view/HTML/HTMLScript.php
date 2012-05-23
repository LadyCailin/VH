<?php 
include_once(dirname(__FILE__)."/HTMLContainer.php");
/**
 * This class represents a script tag. In most cases, it is more appropriate to
 * use HTMLJavaScript. 
 */
class HTMLScript extends HTMLContainer {

    protected $script;

    /**
     * Creates a new inline script. The tag is never self
     * closed, even if $script is empty.
     * @param type $script 
     */
    public function __construct($script) {
        $this->selfClosed = false;
        $this->script = $script;
    }

    protected function getContent() {
        if (trim($this->script) != "") {
            $cdata = new HTMLCData("//><!--\n" . $this->script . "\n//--><");
            return "<!--//-->" . $cdata->render();
        } else {
            return "";
        }
    }

    protected function getTagName() {
        return "script";
    }

}
?>
