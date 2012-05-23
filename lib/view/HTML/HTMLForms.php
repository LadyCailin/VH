<?php 
include_once(dirname(__FILE__)."/HTMLComposite.php");
include_once(dirname(__FILE__)."/HTMLFlatComposite.php");
/**
 * An HTML form contains various user input mechanisms, which are sent back to the server to be processed.
 * This class makes formatting the form quick and easy. 
 */
class HTMLForm extends HTMLComposite {

    const POST = "post";
    const GET = "get";

    private $fieldsetName = null;

    /**
     * Send an associative array of form name-value pairs, and they will automatically be added
     * to the form.
     * @param array $inputs 
     */
    public function addHiddenInputs(array $inputs) {
        foreach ($inputs as $name => $value) {
            $this->addView(new HTMLHiddenInput($name, $value));
        }
        return $this;
    }

    public function addViews($view, $_) {
        foreach (func_get_args() as $arg) {
            if (is_array($arg)) {
                foreach ($arg as $a) {
                    $this->addViews($a);
                }
            } else {
                if (!($arg instanceof HTMLView)) {
                    $arg = new HTMLText($arg);
                }
                $this->addView($arg);
            }
        }
        return $this;
    }

    public function addView(HTMLView $view) {
        parent::addView($view);
        return $this;
    }

    /**
     * Creates a new form, which will submit to the specified page, defaulting to the current page.
     * @param type $action 
     */
    public function __construct($action = "") {
        $this->setAttribute("action", $action);
    }

    public function setMethod($method) {
        $this->setAttribute("method", $method);
        return $this;
    }

    public function getMethod() {
        return $this->getAttribute("method");
    }

    public function setAction($action) {
        $this->setAttribute("action", $action);
        return $this;
    }

    public function getAction() {
        return $this->getAttribute("action");
    }

    public function getContent() {
        $content = parent::getContent();
        if ($this->fieldsetName !== null) {
            $fieldset = new HTMLFieldset($content, $this->fieldsetName);
            return $fieldset->render();
        } else {
            return new HTMLBlock($content);
        }
    }

    /**
     * Puts a fieldset around an entire form. This does not
     * preclude you from adding your own fieldset elements, this
     * is just a convenience mechanism for setting the fieldset if
     * this form is one single fieldset.
     * @param type $name 
     */
    public function setFieldsetName($name) {
        $this->fieldsetName = $name;
        return $this;
    }

    public function appendAttribute($name, $content) {
        if ($name == "action") {
            $this->removeAttribute("action");
        }
        if ($content instanceof URL) {
            $content = $content->build();
        }
        if ($name == "method") {
            if ($content != self::GET && $content != self::POST) {
                trigger_error("Setting method to invalid value, only 'get' and 'post' are acceptable.", E_USER_WARNING);
            }
        }
        parent::appendAttribute($name, $content);
        return $this;
    }

    /**
     * Returns all the input objects contained in this form.
     * @return \HTMLInput 
     */
    public function getAllInputs() {
        $inputs = array();
        foreach ($this->getAllViews() as $view) {
            if ($view instanceof HTMLInput) {
                $inputs[] = $view;
            }
        }
        return $inputs;
    }

    /**
     * Returns an array of name=>type, indicating all the inputs inside of this form. This can be used to auto-fill
     * missing form elements with the default condition, should the parameter not be sent, such as what happens
     * with checkboxes that are not checked.
     * @return type 
     */
    public function getAllInputNames() {
        $inputNames = array();
        foreach ($this->getAllInputs() as $input) {
            $inputNames[$input->getAttribute("name")] = $input->getAttribute("type");
        }
        return array_unique($inputNames);
    }

    protected function getCompositeTagName() {
        return "form";
    }

}

/**
 * A macro class that creates Submit, Reset, and Cancel buttons which (probably) would be at the bottom of a form. 
 * The strings that are used by default are public static variables, or they can be overridden on a per-instance
 * basis by sending the text each time. $cancelAction should be the url they go to if the click the cancel button. If
 * javascript is disabled, they may be on a standalone page with just the form as the view, otherwise, it will probably
 * be a dialog box, in which case, it will simply close.
 */
class HTMLSubmitResetCancelInput extends HTMLFlatComposite {

    public static $SubmitText = "Submit";
    public static $ResetText = "Reset";
    public static $CancelText = "Cancel";

    /**
     * Creates a new Submit | Reset | Cancel button group. This button group has special behavior if this is
     * included as part of a managed form.
     * @param string $cancelPage The url of the page that the user will be sent to if they hit the cancel button. Typcially
     * this would be the same page, so the default is "".
     * @param string $cancelView The view to go to when cancel is pressed. If the main view, you may leave it at it's default, null.
     * @param string $submitText The text to put in the submit button. If null, it uses the HTMLSubmitResetCancelInput::$SubmitText variable,
     * which defaults to "Submit", but can be globally changed, as it is a public static variable.
     * @param string $resetText The text to put in the reset button. If null, it uses the HTMLSubmitResetCancelInput::$ResetText variable,
     * which defaults to "Reset", but can be globally changed, as it is a public static variable.
     * @param string $cancelText The text to put in the Cancel button. If null, it uses the HTMLSubmitResetCancelInput::$CancelText variable,
     * which defaults to "Cancel", but can be globally changed, as it is a public static variable.
     */
    public function __construct($cancelPage = "", $cancelView = null, $submitText = null, $resetText = null, $cancelText = null) {
        if ($submitText === null) {
            $submitText = self::$SubmitText;
        }
        if ($resetText === null) {
            $resetText = self::$ResetText;
        }
        if ($cancelText === null) {
            $cancelText = self::$CancelText;
        }
        $this->addView(new HTMLSubmitInput(null, $submitText));
        $this->addView(new HTMLResetInput($resetText));
        $url = $cancelPage;
        if (!($url instanceof URL)) {
            $url = new URL($url);
        }
        $urlView = $url->getQueryParams("_view");
        if ($urlView != null && $urlView != $cancelView) {
            trigger_error("Do not set the view in the url passed with \$cancelPage to the HTMLSubmitResetCancelInput view.", E_USER_WARNING);
        }
        if ($cancelView != "") {
            $url->setQueryParam("_view", $cancelView);
        }
        $a = new HTMLA(new HTMLButtonInput("cancel", $cancelText), $url->build());
        $a->addStyle("text-decoration", "none");
        $a->addClass("autoCancel");
        $this->addView($a);
    }

    /**
     * If this control block is being used in a managed form, you should set the view to show when the cancel
     * button is clicked, if this does not go to the default view.
     * @param type $view 
     */
    public function setCancelView($view) {
        $a = $this->views[2];
        $url = new URL($a->getHref());
        $url->setQueryParam("_view", $view);
        $a->setHref($url->build());
        return $this;
    }

}

abstract class HTMLShowFormControl extends HTMLView {

    protected $component;

    public function render() {
        return $this->component->render();
    }

}

class HTMLShowFormButton extends HTMLShowFormControl {

    public function __construct($formName, $buttonText) {
        $form = new HTMLForm();
        $form->setMethod(HTMLForm::POST);
        $form->addClass("--intercept");
        $form->addClass("--show-form");
        $form->addClass("---$formName");
        $form->addView(new HTMLSubmitInput(null, $buttonText));
        $form->addHiddenInputs(array("_fallback" => $formName));
        $this->component = $form;
    }

}

class HTMLShowFormLink extends HTMLShowFormControl {

    public function __construct($formName, $linkText) {
        $href = "?_fallback=" . urlencode($formName);
        $a = new HTMLA($linkText, $href);
        $a->addClass("-intercept");
        $a->addClass("--show-form");
        $a->addClass("---$formName");
        $this->component = $a;
    }

}

class HTMLFormOptions {

    /**
     * If a request can be handled asynchronously, (and javascript is enabled) this form will be submitted
     * asynchronously, and if the request was successful, it will simply clear the form (and do other
     * managed actions). If the form is managed and shown in a dialog, the dialog will be closed.
     * @var type 
     */
    public $isAsync = true;

    /**
     * If a form is activated by a HTMLFormActivator button press, instead of showing the component in
     * its own view, it is shown in a modal dialog instead. Usually if this is true, $isAsync should
     * also be true for best results. If this is false, isAsync is irrelevant, the page simply
     * redirects as if javascript were off.
     * @var type 
     */
    public $useDialog = true;

    /**
     * These are the options that will be sent to the jQuery dialog box if the dialog is shown.
     * @var type 
     */
    public $dialogOptions = array();

    /**
     * Before the form can be submitted, javascript will be used to validate the inputs that have declared
     * validation parameters. These parameters will also be automatically validated by the server upon form
     * submission, and further validation may occur as well.
     * @var type 
     */
    public $validateFirst = true;

    /**
     * Sets the javascript and server side validation options. This should be an associative array mapping
     * input name=>validation options, where validation options is an associative array with one or more of
     * the following key=>value pairs:
     * 		type:
     * 			one of either: string, numeric, or integral
     * 		minval:
     * 			if type is numeric or integral, defines the minimum value that is acceptable.
     * 		maxval:
     * 			if type is numeric or integral, defines the maximum value that is acceptable.
     * 		minlen:
     * 			if type is string, defines the minimum length value that is acceptable.
     * 		maxlen:
     * 			if type is string, defines the maximimum length value that is acceptable
     * 		regex:
     * 			if type is string, defines the regex pattern that this must match to be considered acceptable.
     * 		error:
     * 			A string, representing the error message to display if this value is not acceptable for whatever reason.
     * 			A generic message will be provided if this is not set.
     * TODO: Finish adding regex and error values, and also add custom type, which will automatically trigger both server side
     * and client side validation, which is provided by the business logic.
     * @var type 
     */
    public $validationOptions = array();

}
?>
