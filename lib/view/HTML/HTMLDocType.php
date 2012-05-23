<?php 
/**
 * This class assists in rendering a doctype heading 
 */
class HTMLDocType {

    private $render;

    public function __construct($topElement, $availability = null, $fpi = null, $uri = null) {
        $render = "<!DOCTYPE " . $topElement;
        if ($availability !== null) {
            $render .= " $availability";
        }
        if ($fpi !== null) {
            $render .= " \"$fpi\"";
        }
        if ($uri !== null) {
            $render .= " \"$uri\"";
        }
        $render .= ">";
        return $render;
    }

    public function render() {
        return $this->render;
    }

}
?>
