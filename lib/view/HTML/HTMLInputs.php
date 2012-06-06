<?php 
include_once(dirname(__FILE__)."/HTMLInline.php");
/**
 * Represents an input of some sort. Subclasses provide more specific functionality, and this
 * class should normally not be instantiated directly.
 */
abstract class HTMLInput extends HTMLInline {

    const BUTTON = "button";
    const CHECKBOX = "checkbox";
    const FILE = "file";
    const HIDDEN = "hidden";
    const IMAGE = "image";
    const PASSWORD = "password";
    const RADIO = "radio";
    const RESET = "reset";
    const SUBMIT = "submit";
    const TEXT = "text";

    public function __construct($type, $name, $value) {
        $this->setAttribute("type", $type);
        $this->setAttribute("name", $name);
        $this->setAttribute("value", $value);
    }

    public function setDisabled($disabled) {
        if ($disabled) {
            $this->setAttribute("disabled", "disabled");
        } else {
            $this->removeAttribute("disabled");
        }
        return $this;
    }

    public function appendAttribute($name, $content) {
        if ($name == "type") {
            $this->removeAttribute("type");
            switch ($content) {
                case self::BUTTON:
                case self::CHECKBOX:
                case self::FILE:
                case self::HIDDEN:
                case self::IMAGE:
                case self::PASSWORD:
                case self::RADIO:
                case self::RESET:
                case self::SUBMIT:
                case self::TEXT:
                    break;
                default:
                    trigger_error("Input type '$content' is not recognized as a valid type.", E_USER_WARNING);
                    break;
            }
        }
        switch ($name) {
            case "accept":
            case "align":
            case "alt":
            case "checked":
            case "src":
                if ($this->acceptableAttributes() === null || !in_array($name, $this->acceptableAttributes())) {
                    trigger_error("Attribute '$name' is not allowed in this input type.", E_USER_WARNING);
                }
                break;
            default:
                break;
        }
        parent::appendAttribute($name, $content);
        return $this;
    }

    protected function getCompositeTagName() {
        return "input";
    }

    /**
     * By default, the following attribute types are restricted to just a few input types:
     * accept, align, alt, checked, and src. Unless the subtype returns a list of acceptable
     * types to override this check, those attributes are not allowed to be added.
     * @return null 
     */
    protected function acceptableAttributes() {
        return null;
    }

}

//These classes represent the trivial input types
class HTMLHiddenInput extends HTMLInput {

    public function __construct($name, $value) {
        parent::__construct(HTMLInput::HIDDEN, $name, $value);
    }

}

class HTMLTextInput extends HTMLInput {

    public function __construct($name, $value = "") {
        parent::__construct(HTMLInput::TEXT, $name, $value);
    }

}

class HTMLButtonInput extends HTMLInput {

    public function __construct($name, $value) {
        parent::__construct(HTMLInput::BUTTON, $name, $value);
    }

}

class HTMLPasswordInput extends HTMLInput {

    public function __construct($name, $value = "") {
        parent::__construct(HTMLInput::PASSWORD, $name, $value);
    }

}

class HTMLFileInput extends HTMLInput {

    private static $acceptable = array("accept");

    public function __construct($name) {
        parent::__construct(HTMLInput::FILE, $name, "");
    }

    protected function acceptableAttributes() {
        return self::$acceptable;
    }

}

class HTMLImageInput extends HTMLInput {

    private static $acceptable = array("align", "alt", "src");

    public function __construct($name, $value) {
        parent::__construct(HTMLInput::IMAGE, $name, $value);
    }

    protected function acceptableAttributes() {
        return self::$acceptable;
    }

}

class HTMLResetInput extends HTMLInput {

    public function __construct($name) {
        parent::__construct(HTMLInput::RESET, $name, "");
        $this->removeAttribute("value");
    }

}

class HTMLSubmitInput extends HTMLInput {

    public static $SubmitText = "Submit";

    public function __construct($name = null, $value = null) {
        if ($value === null) {
            $value = self::$SubmitText;
        }
        parent::__construct(HTMLInput::SUBMIT, $name, $value);
    }

}

/**
 * You probably want to use the HTMLRadioGroup instead of this, however, if you need
 * more flexibility in specifying the radio group, you may use this class directly.
 */
class HTMLRadioInput extends HTMLInput {

    private static $acceptable = array("checked");

    public function __construct($name, $value, $selected = false) {
        parent::__construct(HTMLInput::RADIO, $name, $value);
        $this->setSelected($selected);
    }

    public function setSelected($selected) {
        if ($selected) {
            $this->setAttribute("checked", "checked");
        } else {
            $this->removeAttribute("checked");
        }
    }

    protected function acceptableAttributes() {
        return self::$acceptable;
    }

}

class HTMLRadioGroup extends HTMLFlatComposite {

    /**
     * Constructs a logical radio button group, with the name given, using the options
     * array as $value => $text. If $useFor is true, a random id will be generated and
     * used on the input as well, to associate the label with the radio button. If
     * $blockLevel is true, it will put each radio button into it's own div as well.
     * @param type $groupName
     * @param array $options 
     */
    public function __construct($groupName, array $options, $selectedValue = null, $useFor = true, $blockLevel = true) {

        foreach ($options as $value => $text) {
            $radio = new HTMLRadioInput($groupName, $value);
            if ($value == $selectedValue) {
                $radio->setSelected(true);
            }
            if ($useFor) {
                $id = $this->getRandomId();
                $radio->setId($id);
                $label = new HTMLLabel($id, $text);
            } else {
                $label = new HTMLText(" " . $text);
            }
            if ($blockLevel) {
                $block = new HTMLDiv(array($radio, $label));
                $this->addView($block);
            } else {
                $this->addView($radio);
                $this->addView($label);
            }
        }
    }

}

//These are the more complex input types
/**
 * This class automatically adds a label to the checkbox, using the <label> tag,
 * which will make the user experience better. Using this label feature
 * will automatically set the id of the checkbox to $name, since an id is
 * required by label. If the label is null, no
 * label tag will be generated, and you are free to do your own label, 
 * for instance, if multiple forms with the same name exist on the page.
 */
class HTMLCheckboxInput extends HTMLInput {

    private static $acceptable = array("checked");
    private $alignRight = true;
    private $label = null;

    protected function acceptableAttributes() {
        return self::$acceptable;
    }

    public function __construct($name, $label, $checked = false) {
        parent::__construct(HTMLInput::CHECKBOX, $name, "");
        $this->label = $label;
        if ($this->label !== null) {
            $this->setId($name);
        }
        $this->removeAttribute("value");
        if ($checked) {
            $this->setAttribute("checked", "checked");
        }
    }

    public function setLabel($label, $alignRight = true) {
        $this->label = $label;
        $this->alignRight = $alignRight;
        return $this;
    }

    public function render() {
        $rendered = "";
        $label = "";
        if ($this->label !== null) {
            $label = new HTMLLabel($this->getAttribute("name"), $this->label);
            $label = $label->render();
        }
        if ($this->alignRight) {
            $rendered .= parent::render();
            $rendered .= $label;
        } else {
            $rendered .= $label;
            $rendered .= parent::render();
        }
        return $rendered;
    }

}

final class HTMLLabel extends HTMLInline {

    public function __construct($for, $content) {
        parent::__construct($content);
        $this->setFor($for);
    }

    public function setFor($for) {
        $this->setAttribute("for", $for);
        return $this;
    }

    public function getFor() {
        return $this->getAttribute("for");
    }

    public function appendAttribute($name, $content) {
        if ($name == "for") {
            $this->removeAttribute("for");
        }
        parent::appendAttribute($name, $content);
        return $this;
    }

    protected function getCompositeTagName() {
        return "label";
    }

}
?>
