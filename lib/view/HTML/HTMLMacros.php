<?php 
include_once(dirname(__FILE__)."/HTMLView.php");
/**
 * Creates a form that will cause the page to refresh and show the specified view. If the view specified is shown, and
 * this button is embedded in a managed dialog, the dialog will simply close. 
 */
class HTMLShowViewButton extends HTMLView {

    private $form;

    public function __construct($viewName, $buttonText) {
        $form = new HTMLForm();
        $form->setMethod(HTMLForm::POST);
        $form->addHiddenInputs(array("vh_view" => $viewName));
        $form->addView(new HTMLSubmitInput(null, $buttonText));
        $form->addClass("--intercept");
        $form->addClass("--show-view");
        $this->form = $form;
    }

    public function render() {
        return $this->form->render();
    }

}
?>
