<?php 
include_once(dirname(__FILE__)."/HTMLBlock.php");
include_once(dirname(__FILE__)."/HTMLInline.php");
class HTMLLegend extends HTMLInline {

    protected function getCompositeTagName() {
        return "legend";
    }

}

class HTMLFieldset extends HTMLBlock {

    private $fieldsetName = null;

    public function __construct($content, $name = null) {
        parent::__construct($content);
        $this->fieldsetName = $name;
    }

    public function setFieldsetName($name) {
        $this->fieldsetName = $name;
        return $this;
    }

    public function getContent() {
        if ($this->fieldsetName !== null) {
            $this->addView(new HTMLLegend($this->fieldsetName));
        }
        return parent::getContent();
    }

    protected function getCompositeTagName() {
        return "fieldset";
    }

}
?>
