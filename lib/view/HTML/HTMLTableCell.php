<?php 
include_once(dirname(__FILE__)."/HTMLContainer.php");
class HTMLTableCell extends HTMLContainer {

    public function __construct($content) {
        if ($content instanceof HTMLView) {
            $this->content = $content;
        } else {
            $this->content = new HTMLText($content);
        }
    }

    protected function getTagName() {
        return "td";
    }

    public function setColspan($span) {
        $this->setAttribute("colspan", $span);
        return $this;
    }

    public function setRowspan($span) {
        $this->setAttribute("rowspan", $span);
        return $this;
    }

    public function appendAttribute($name, $content) {
        switch (trim(strtolower($name))) {
            case "colspan":
                $this->removeAttribute("colspan");
                break;
            case "rowspan":
                $this->removeAttribute("rowspan");
                break;
        }
        parent::appendAttribute($name, $content);
        return $this;
    }

}
?>
