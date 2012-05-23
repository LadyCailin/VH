<?php 
include_once(dirname(__FILE__)."/HTMLView.php");
/**
 * This class knows how to contain CDATA, including escaping enclosed CDATA
 * tags. It is not used directly usually, but script tags need their content
 * to be escaped, and this class is used for that purpose. 
 */
class HTMLCData extends HTMLView {

    private $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function render() {
        return "<![CDATA[" . preg_replace('/\]\]>/', ']]>', $this->data) . "]]>";
    }

}
?>
