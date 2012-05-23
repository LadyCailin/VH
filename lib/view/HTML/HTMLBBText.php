<?php 
include_once(dirname(__FILE__)."/HTMLText.php");
/** 
 * HTMLBBText extends HTMLText, but also parses BB Code. 
 */
class HTMLBBText extends HTMLText {

    public function __construct($text) {
        parent::__construct($text);
    }

    /** @Override */
    public function render() {
        $rendered = parent::render();
        //Now parse out the BBCode
        $rendered = $this->parseBBCode($rendered);
        return $rendered;
    }

    private function parseBBCode($text) {
        return "This would be BB Parsed: " . $text;
    }

}
?>
