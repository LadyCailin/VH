<?php 
include_once(dirname(__FILE__)."/HTMLContainer.php");
include_once(dirname(__FILE__)."/HTMLComposite.php");
class HTMLLegend extends HTMLContainer {

    protected function getTagName() {
        return "legend";
    }

}

class HTMLFieldset extends HTMLComposite {

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
