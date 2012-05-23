<?php 
include_once(dirname(__FILE__)."/HTMLTableCell.php");
class HTMLTableHeaderCell extends HTMLTableCell {

    protected function getTagName() {
        return "th";
    }

}
?>
