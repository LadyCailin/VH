<?php 
include_once(dirname(__FILE__)."/HTMLBlock.php");
/**
 * This class wraps an HTML Table. You may send any HTMLView (or text) in the 2d array
 * passed to the constructor, but if you need more flexibility (for instance when
 * defining your own rowspan or colspan attributes) you should pass in HTMLTableCell
 * elements instead. 
 */
class HTMLTable extends HTMLBlock {

    private $cells; //Initialized by constructor to contain a 2d array of Views
    private $bodyAttributes = null;
    private $headers = null;
    private $headerRowAttributes = null;
    private $headerCellAttributes = null;
    private $footers = null;
    private $footerRowAttributes = null;
    private $footerCellAttributes = null;
    private $rowRenderer = null;

    /** @param array $cells - A 2D array of View objects */
    public function __construct($cells) {
        $this->cells = $cells;
        if (!is_array($this->cells)) {
            throw new Exception("Expecting an array of arrays to the HTMLTable constructor");
        }
        foreach ($this->cells as $rowKey => $row) {
            if (!is_array($row)) {
                throw new Exception("Expecting an array of arrays to the HTMLTable constructor");
            }
            foreach ($row as $cellKey => $cell) {
                if (!($cell instanceof HTMLTableCell)) {
                    $cell = new HTMLTableCell($cell);
                }
                $this->cells[$rowKey][$cellKey] = $cell;
                $this->addView($cell);
            }
        }
    }

    protected function getCompositeTagName() {
        return "table";
    }

    public function enableBorder($bool) {
        $this->appendAttribute("border", $bool);
        return $this;
    }

    public function setSummary($summary) {
        $this->setAttribute("summary", $summary);
        return $this;
    }

    public function appendAttribute($name, $content) {
        $name = trim($name);
        switch (trim($name)) {
            case "border":
                if ($content == 0) {
                    $this->removeAttribute("border");
                } else {
                    $this->setAttribute("border", 1);
                }
                break;
            case "summary":
                $this->setAttribute("summary", $content);
            default:
                parent::appendAttribute($name, $content);
        }
        return $this;
    }

    protected function getContent() {
        $rendered = "";
        if ($this->headers !== null) {
            $rendered .= "<thead><tr" . ($this->headerRowAttributes !== null ? " " . $this->renderAttributes($this->headerRowAttributes) : "") . ">";
            foreach ($this->headers as $header) {
                if ($this->headerCellAttributes !== null) {
                    $header->addAttributeArray($this->headerCellAttributes);
                }
                $rendered .= $header->render();
            }
            $rendered .= "</tr></thead>";
        }
        if ($this->footers !== null) {
            $rendered .= "<tfoot><tr" . ($this->footerRowAttributes !== null ? " " . $this->renderAttributes($this->headerRowAttributes) : "") . ">";
            foreach ($this->footers as $footer) {
                if ($this->footerCellAttributes !== null) {
                    $footer->addAttributeArray($this->footerCellAttributes);
                }
                $rendered .= $footer->render();
            }
            $rendered .= "</tr></tfoot>";
        }

        if ($this->cells !== null) {
            if ($this->headers !== null || $this->footers !== null) {
                $rendered .= "<tbody>";
            }
            $rowNum = 0;
            foreach ($this->cells as $row) {
                $rendered .= "<tr";
                if ($this->rowRenderer !== null) {
                    $rowRenderer = $this->rowRenderer;
                    $rowAttributes = $rowRenderer($rowNum);
                    if ($rowAttributes != null) {
                        $rendered .= " " . $this->renderAttributes($rowAttributes);
                    }
                }
                $rendered .= ">";
                foreach ($row as $cell) {
                    $rendered .= $cell->render(); //Contains whatever it is this view returns
                }
                $rendered .= "</tr>";
                $rowNum++;
            }
            if ($this->headers !== null || $this->footers !== null) {
                $rendered .= "</tbody>";
            }
        }

        return $rendered;
    }

    /**
     * This accepts an array of HTMLView objects, which is added to the header of the table.
     * @param array[HTMLView] $header 
     */
    public function addHeaderArray(array $header) {
        $this->headers = $header;
        foreach ($header as $key => $cell) {
            if (!($cell instanceof HTMLTableHeaderCell)) {
                $cell = new HTMLTableHeaderCell($cell);
            }
            $this->headers[$key] = $cell;
            $this->addView($cell);
        }
        return $this;
    }

    /**
     * Adds custom attributes to the table header row
     * @param array $headerRowAttributes 
     */
    public function addHeaderRowAttributes(array $headerRowAttributes) {
        if (count($headerRowAttributes) === 0) {
            $this->headerRowAttributes = null;
        } else {
            $this->headerRowAttributes = $headerRowAttributes;
        }
        return $this;
    }

    /**
     * Adds custom attributes to each cell in the header. Usually, you would only use this
     * for style information that is one off for this table, but a css selector in <style> tags in the head
     * is preferred, and for very complex tables, you're better off adding custom HTMLTableHeaderCells yourself.
     * @param array $headerCellAttributes 
     */
    public function addHeaderCellAttributes(array $headerCellAttributes) {
        if (count($headerCellAttributes) === 0) {
            $this->headerCellAttributes = null;
        } else {
            $this->headerCellAttributes = $headerCellAttributes;
        }
        return $this;
    }

    /**
     * Adds custom attributes to the table body
     * @param array $bodyAttributes 
     */
    public function addBodyAttributes(array $bodyAttributes) {
        if (count($bodyAttributes) === 0) {
            $this->bodyAttributes = null;
        } else {
            $this->bodyAttributes = $bodyAttributes;
        }
        return $this;
    }

    /**
     * Adds the footer cells to the table. May be an array of HTMLTableHeaderCells, which are used directly,
     * but if a non-HTMLTableHeaderCell is added, it will be wrapped in a cell for you.
     * @param array $footer 
     */
    public function addFooterArray(array $footer) {
        $this->footers = $footer;
        foreach ($footer as $key => $cell) {
            if (!($cell instanceof HTMLTableHeaderCell)) {
                $cell = new HTMLTableHeaderCell($cell);
            }
            $this->footers[$key] = $cell;
            $this->addView($cell);
        }
        return $this;
    }

    public function addFooterRowAttributes(array $footerAttributes) {
        if (count($footerAttributes) === 0) {
            $this->footerRowAttributes = null;
        } else {
            $this->footerRowAttributes = $footerAttributes;
        }
        return $this;
    }

    /**
     * Adds custom attributes to each cell in the footer. Usually, you would only use this
     * for style information that is one off for this table, but a css selector in <style> tags in the head
     * is preferred, and for very complex tables, you're better off adding custom HTMLTableHeaderCells yourself.
     * @param array $headerCellAttributes 
     */
    public function addFooterCellAttributes(array $footerCellAttributes) {
        if (count($footerCellAttributes) === 0) {
            $this->footerCellAttributes = null;
        } else {
            $this->footerCellAttributes = $footerCellAttributes;
        }
        return $this;
    }

    /**
     * Registers a function that will render attributes for table rows. When a table row
     * is about to be rendered, this function is called and passed the row number (starting with 0). It should
     * then return an associative array with the attributes this row should have. For instance,
     * <pre>
     * function($row){
     *     if($row % 2 == 0) return array("class" => "even");
     * 	   else return array("class" => "odd");
     * }
     * </pre>
     * @param callable $function 
     * @return array
     */
    public function addRowRenderer($function) {
        if (!is_callable($function)) {
            throw new Exception("\$function sent to addRowRenderer is not a callable function!");
        }
        $this->rowRenderer = $function;
        return $this;
    }

}
?>
