<?php

/**
 * The HTMLPageManager class manages construction of entire pages, including ajax callback
 * mechanisms. This is the primary controller that all accessible pages should use. Ultimately,
 * to display a page, the only code required is:
 * <code>
 * $page = new HTMLPageManager();
 * $page->registerView("main", function($manager){ return new HTMLPage() });
 * $page->handle();
 * </code>
 * 
 */
final class HTMLPageManager {

	private static $reservedRequestVars = array("vh_view", "vh_action", "vh_ajax", "vh_managed", "vh_regen", "vh_fallback");
	//reserved request variables
	private $vh_view;
	private $vh_action;
	private $vh_ajax;
	private $vh_managed;
	private $vh_regen;
	private $vh_fallback;
	private $defaultHTMLViewOptions = null;
	private $defaultView = null;
	private $handled = false;
	private $views = null;
	private $viewOptions = null;
	private $components = null;
	private $forms = null;
	private $formCallbacks = null;
	private $formOptions = null;
	private $actionHandler = null;
	private $wrapperGenerator = null;

	public function __construct() {
		$this->views = array();
		$this->viewOptions = array();
		$this->components = array();
		$this->forms = array();
		$this->formCallbacks = array();
		$this->formOptions = array();
		$this->req = array();
		//Our default wrapper generator is a barebones page
		$this->wrapperGenerator = function($manager, $content){
			return new HTMLPage($content);
		};
	}

	public function __destruct() {
		if (!$this->handled) {
			trigger_error("Page Manager constructed, but not invoked. Did you forget to call \$PageManager->handle()? If you purposely meant to"
				. " ignore this object, call \$PageManager->ignore() to supress this warning.", E_USER_WARNING);
		}
	}

	/**
	 *  
	 */
	public function ignore() {
		$this->handled = true;
	}

	/**
	 * This looks at the request variables to decide what view to show, and how it needs to be shown. If the request was
	 * a managed ajax request, it will also be handled.
	 */
	public function handle() {
		foreach (self::$reservedRequestVars as $var) {
			if (isset($_REQUEST[$var])) {
				$varVal = $_REQUEST[$var];
				unset($_REQUEST[$var]);
				unset($_POST[$var]);
				unset($_GET[$var]);
				switch ($var) {
					case "vh_view":
						$this->vh_view = $varVal;
						break;
					case "vh_action":
						$this->vh_action = $varVal;
						break;
					case "vh_ajax":
						$this->vh_ajax = $varVal;
						break;
					case "vh_managed":
						$this->vh_managed = $varVal;
						break;
					case "vh_regen":
						$this->vh_regen = $varVal;
						break;
					case "vh_fallback":
						$this->vh_fallback = $varVal;
						break;
					default:
						break;
				}
			}
		}

		$this->handled = true;
		if (isset($this->vh_fallback)) {
			//It's a form fallback, we need to generate the form, display it, then return.			
			$content = null;
			$form = $this->getComponent($this->vh_fallback);
			$this->prepareForm($this->vh_fallback, $form);
			if ($this->wrapperGenerator === null) {
				trigger_error("No wrapper generator was provided, using barebones page instead.", E_USER_WARNING);
				$content = new HTMLPage($form);
			} else {
				$content = call_user_func($this->wrapperGenerator, $this, $form);
			}
			$content->display();
			return;
		}
		if (isset($this->vh_action)) {
			//It's a request that needs action, if it's managed, then it's a form submission (ajax or otherwise)
			//and if it's not, we need to pass the action on to the generic action handler.
			if (isset($this->vh_managed)) {
				if (isset($this->formCallbacks[$this->vh_action])) {
					//We need to fill in the missing parameters that are supposed to be in this form, but are not
					//(checkboxes have this behavior, for instance)
					foreach ($this->getComponent($this->vh_action)->getAllInputNames() as $name => $type) {
						if ($type == HTMLInput::CHECKBOX) {
							if (!isset($_REQUEST[$name])) {
								$_REQUEST[$name] = false;
							}
						}
					}
					$errors = array();
					if (isset($this->formOptions[$this->vh_action]->validationOptions)) {
						$errors = array_merge($errors, $this->doValidation($_REQUEST, $this->formOptions[$this->vh_action]->validationOptions));
					}
					try {
						//This function can throw an exception, which will 
						call_user_func($this->formCallbacks[$this->vh_action], $_REQUEST);
					} catch (HTMLValidationException $e) {
						$errors = array_merge($errors, $e->getErrors());
					}
					if (count($errors) > 0) {
						
					}
				} else {
					trigger_error("No callback function registered for the form '{$this->_action}'", E_USER_WARNING);
				}
			} else {
				if ($this->actionHandler instanceof Closure) {
					call_user_func($this->actionHandler, $this->vh_action);
				} else {
					trigger_error("A custom action was sent, but the action handler has not been set", E_USER_NOTICE);
				}
			}
		}
		if (isset($this->vh_ajax)) {
			//It's an ajax request, and it may want to regen some components
			//TODO
		} else {
			//This is a full blown view
			$this->defaultHTMLViewOptions = new HTMLViewOptions();
			$viewToRender = $this->defaultView;
			$viewOptions = $this->defaultHTMLViewOptions;
			$requestedView = $this->vh_view;
			if (isset($requestedView)) {
				if (isset($this->views[$requestedView])) {
					$viewToRender = $this->views[$requestedView];
				} else {
					trigger_error("Requesting unknown view, default view is being provided.");
				}
			}
			if (isset($this->viewOptions[$requestedView])) {
				$viewOptions = $this->viewOptions[$requestedView];
			}

			if ($viewOptions->validateFirst instanceof Closure) {
				try {
					call_user_func($viewOptions->validateFirst, $this);
					//We're good, we can continue rendering this view
				} catch (Exception $e) {
					//This will be thrown if the validateFirst callback threw an exception
					$this->error($e->getMessage());
					die();
				}
			}
			$view = call_user_func($viewToRender, $this);
			//If the view returned isn't an HTMLPage, we'll use the wrapper
			if(!($view instanceof HTMLPage)){
				$view = call_user_func($this->wrapperGenerator, $this, $view);
			}
			//If it's still not a HTMLPage, they messed up something.
			if (!($view instanceof HTMLPage)) {
				trigger_error("Attempting to display a non-HTMLPage in the view manager, or the wrapper generator did not return an HTMLPage object. Make sure that the callbacks"
					. " you registered with registerView returns an HTMLPage object.", E_USER_WARNING);
				return;
			}
			//We need to add our dialog-able forms here now, so they will be able to be shown in a dialog
			foreach ($this->forms as $formName) {
				$form = $this->getComponent($formName);
				$this->prepareForm($formName, $form);
				if (isset($this->formOptions[$formName]) && $this->formOptions[$formName]->useDialog) {
					$form->addExternalScript(CommonIncludes::JQuery);
					$form->addExternalScript(CommonIncludes::JQueryUI);
					$form->addExternalScript(CommonIncludes::ViewCore);
					$form->addExternalCSS(CommonIncludes::JQueryCSS);
					$form->addStyle("display", "none");
					$view->appendContent($form);
				}
				$this->validateValidationParams($form, $this->formOptions[$formName]->validationOptions);
			}
			if (count($this->forms) > 0) {
				foreach ($this->forms as $formName) {
					if (isset($this->formOptions[$formName])) {
						$view->addHeaderScript("VC.addFormOptions(\"$formName\", " . json_encode($this->formOptions[$formName]) . ");\n");
					}
				}
			}
			$view->display();
		}
	}

	/**
	 * Validates the validationOptions array passed in, since it's a bit complex, and easy to fat finger. We will
	 * also fill in missing options, so further steps can make assumptions to reduce code complexity elsewhere.
	 * @param type $formName
	 * @param type $params 
	 */
	private function validateValidationParams(HTMLForm $form, array &$params) {
		foreach ($params as $name => $options) {
			if (!isset($options['type'])) {
				trigger_error("Type not set in validationOptions for '$name'");
			} else {
				$type = strtolower($options['type']);
				if ($type == "int") {
					$type = "integral";
				}
				if ($type == "double") {
					$type = "numeric";
				}
				switch ($type) {
					case "string":
						if (isset($options['minval']) || isset($options['maxval'])) {
							trigger_error("minval and maxval should not be added to a validation parameter of type string.", E_USER_WARNING);
						}
						break;
					case "integral":
					case "numeric":
						if (isset($options['minlen']) || isset($options['maxlen'])) {
							trigger_error("minlen and maxlen should not be added to a validation parameter of type integral or numeric.", E_USER_WARNING);
						}
						break;
					default:
						trigger_error("Invalid type specified: '$type'.", E_USER_WARNING);
						break;
				}
			}
		}
	}

	/**
	 * When a form is automatically managed, we need to add a few parameters to the request. These parameters will
	 * not be passed to the handler, they will be removed prior.
	 * @param HTMLForm $form 
	 */
	private function prepareForm($formName, $form) {
		if (!($form instanceof HTMLForm)) {
			trigger_error("The form generator registered with addForm must return an HTMLForm object", E_USER_WARNING);
		}
		$form->addHiddenInputs(array("_managed" => true, "_action" => $formName));
	}

	private function error($msg) {
		//TODO
		die($msg);
	}

	/**
	 * A component is a section that can be regenerated with an ajax request, standalone from the rest of the page. It 
	 * can also be displayed separately from the rest of the page, should javascript be disabled. Views should use
	 * getComponent() to retrieve the component from this PageManager, so the display of this view will always
	 * be consistent. $componentGenerator should be a callback function that returns an HTMLView, which will only 
	 * be rendered if needed.
	 */
	public function addComponent($name, $componentGenerator) {
		if ($componentGenerator instanceof Closure) {
			$this->components[$name] = $componentGenerator;
		} else {
			trigger_error("Closure not provided to addComponent.", E_USER_WARNING);
		}
		return $this;
	}

	/**
	 * Retrieves a registered component. All components should be registered each time, regardless of if they are used,
	 * because otherwise there is the chance calling this function will trigger a warning.
	 * @param type $name 
	 * 
	 * @return string The generated HTMLView
	 */
	public function getComponent($name, $arguments = array()) {
		if (isset($this->components[$name])) {
			return call_user_func_array($this->components[$name], array_merge(array($this), $arguments));
		} else {
			trigger_error("Requested the '$name' component, but that component doesn't exist!", E_USER_WARNING);
			return null;
		}
	}

	/**
	 * Adds a managed form element to the page. Works like addComponent, but it will also register the form
	 * for automatic management, which means that it will submit the form via ajax. Additionally, you must
	 * register a callback handler, which will handle the input from the form once it is submitted, either
	 * via ajax or not; the manager will handle that for you. The callback will recieve an array with the
	 * parameters submitted via the form, and it needn't check the $_REQUEST variables directly. Once
	 * registered, this component can be retrieved via getComponent.
	 * @param type $name
	 * @param HTMLPageComponent $form 
	 */
	public function addForm($name, $formGenerator, $callback, /* HTMLFormOptions */ $options = null) {
		if ($formGenerator instanceof Closure) {
			$this->components[$name] = $formGenerator;
			$this->forms[] = $name;
			$this->formCallbacks[$name] = $callback;
			if ($options !== null) {
				$this->formOptions[$name] = $options;
			} else {
				$this->formOptions[$name] = new HTMLFormOptions();
			}
		} else {
			trigger_error("Closure not provided to addForm", E_USER_WARNING);
		}
		return $this;
	}

	/**
	 * This registers an HTMLView as an eligable view. A view is a standard layout of components
	 * that will be shown, given a request for the particular view. The $options are an HTMLViewOptions
	 * object, which is used to specify managed behavior per view. $viewGenerator is a callback that should
	 * return a single HTMLPage object which will end up being displayed if this view was requested.
	 * 
	 * @param HTMLViewOptions $options
	 */
	public function registerView($name, $viewGenerator, $options = null) {
		if ($viewGenerator instanceof Closure) {
			$this->views[$name] = $viewGenerator;
			if ($this->defaultView === null) {
				$this->defaultView = $viewGenerator;
			}
			if ($options !== null) {
				$this->viewOptions[$name] = $options;
				if ($options->defaultView) {
					$this->defaultView = $viewGenerator;
				}
			}
		} else {
			trigger_error("Closure not provided to registerView", E_USER_WARNING);
		}
		return $this;
	}

	/**
	 * An action handler is used to handle generic action requests to the server. If a form is added with
	 * the addForm method, that form will be automatically managed, but javascript (or forms) may submit
	 * for custom actions as well, and then the function provided is called. The signature of the handler
	 * should be function($action), $action will be set to the action provided, by the request inputs, and
	 * 
	 * @param type $handler 
	 */
	public function setActionHandler($handler) {
		//TODO
		return $this;
	}

	/**
	 * For forms that support falling back, the wrapper is used to generate the frame around the form itself.
	 * This should be a callback function that will recieve the generated content as an HTMLView: function($contents).
	 * @param type $wrapper 
	 */
	public function setWrapperGenerator($wrapper) {
		if ($wrapper instanceof Closure) {
			$this->wrapperGenerator = $wrapper;
		} else {
			trigger_error("setWrapperGenerator expects a closure", E_USER_WARNING);
		}
		return $this;
	}

	/**
	 * In some cases, such as in a custom action handler, you may wish to force a particular
	 * view to be shown. If this is the case, calling this function will force that view to
	 * be displayed, checking to see if this is an ajax request, and if so, commanding the
	 * javascript to refresh the page to the given view.
	 * @param type $view 
	 */
	public function displayView($view) {
		if ($this->vh_ajax) {
			$this->javascriptControl("showView", $view);
		} else {
			$view = $this->views[$view];
			$view = call_user_func($view, $this);
			$view->display();
		}
	}

	/**
	 * If this is an ajax request, issues a command to the javascript, then dies. If not
	 * an ajax request, triggers a warning, then dies anyways. Though javascript of course
	 * cannot be relied on to listen to the command, for non-secure actions, it is a reasonable
	 * assumption to make that the javascript will listen.
	 * @param type $command
	 * @param type $params 
	 */
	private function javascriptControl($command, $params) {
		$args = func_get_args();
		unset($args[0]); //That's the command
		//TODO
	}

	private function doValidation($request, $options) {
		$errors = array();
		foreach ($options as $input => $option) {
			$errorMsg = "";
			$failValidation = false;
			$val = $request[$input];
			if ($option['type'] == 'string') {
				$hasMinLen = false;
				if (isset($option["minlen"])) {
					$hasMinLen = true;
					if (strlen($val) < $option['minlen']) {
						$failValidation = true;
					}
				}
				$hasMaxLen = false;
				if (isset($option["maxlen"])) {
					$hasMaxLen = true;
					if (strlen($val) > $option['maxlen']) {
						$failValidation = true;
					}
				}
				if ($hasMaxLen && $hasMinLen) {
					if ($option['minlen'] == $option['maxlen']) {
						$errorMsg = "You must enter exactly " . $option['minlen'] . " character" . ($option['minlen'] == 1 ? "" : "s") . ".";
					} else {
						$errorMsg = "You must enter between " . $option['minlen'] . "-" . $option['maxlen'] . " characters.";
					}
				} else if ($hasMaxLen) {
					$errorMsg = "You must enter no more than " . $option['maxlen'] . " characters.";
				} else if ($hasMinLen) {
					$errorMsg = "You must enter at least " . $option['minlen'] . " characters.";
				}
			} else if ($option['type'] == 'numeric' || $option['type'] == 'integer') {
				//TODO: Mirror the javascript implementation
			}
			if ($failValidation) {
				$errors[] = $errorMsg;
			}
		}
		return $errors;
	}

}

/**
 * This class contains options that can be set on a view, which is passed in with
 * registerView in HTMLPageManager. 
 */
class HTMLViewOptions {

	/**
	 * If an invalid view is requested, (or no view at all) if this view is the
	 * default view, it will be shown. If more
	 * than one default view is provided, it is an error, and a warning will be shown.
	 * If no view is specified as the default, the first one is assumed to be the default.
	 * @var boolean 
	 */
	public $defaultView = false;

	/**
	 * Some views can be shown regardless of previous actions. If the view needs to validate
	 * parameters before it is shown, it can set this to a callback function, and it will
	 * be called before the requested view is shown. If the function returns normally, it will
	 * be shown, if it throws an exception, the exception's error message will be displayed instead.
	 * @var callable 
	 */
	public $validateFirst = null;

}

/**
 * This class contains consts that are commonly included javascript and css files.
 * There is no requirement you use these specifically, but if you do, they will be automatically
 * upgraded for you as versions are upped. Note that using multiple versions of a javascript library
 * will cause issues, so it is highly recommended you use these standard values. 
 */
class CommonIncludes {
	//Javascript

	const ViewCore = "js/lib/viewcore.js";
	const JQuery = "http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js";
	const JQueryUI = "http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js";

	//CSS
	const JQueryCSS = "css/ui-lightness/jquery-ui-1.8.20.custom.css";

}

?>
