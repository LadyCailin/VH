<?php

class JS {

	/**
	 * If true, the rendering process adds things like newlines, indentations, and cookie crumbs.
	 * This assists in debugging potential issues in your rendered scripts. If false, the scripts
	 * are minified and uncommented.
	 * @var type 
	 */
	public static $devMode = false;

	/**
	 * Should be called at the start of all functions. If arguments are being
	 * passed to the function, they should be sent to start, which will ensure
	 * they are all Structures, as well as handling the stack if it is misaligned
	 * now, due to a argument rift.
	 * @param JS $item
	 * @return \JS 
	 */
	private static function start(&$args = null) {
		$args = self::resolve($args);
		return $args;
	}

	private static function resolve(&$args) {
		if ($args === null) {
			return null;
		} else if (is_array($args)) {
			foreach ($args as $index => $arg) {
				$args[$index] = self::resolve($arg);
			}
			return $args;
		} else {
			if ($args instanceof Structure) {
				$render = $args->render(self::$devMode);
				$render = preg_replace("/;[ \n]*$/m", '', $render);
				$args = new Raw($render);
			} else {				
				$args = new Atomic($args);
			}
			return $args;
		}
	}
	

	/**
	 * Creates an if/ifelse block. $condition is the test condition,
	 * $true is the code put in the if block, and optionally, $false is
	 * the code to put in the else block. In all cases, _ifelse could
	 * also be used.
	 * @param type $condition
	 * @param type $true
	 * @param type $false
	 * @return type 
	 */
	public static function _if($condition, $true, $false = null){		
		if($false == null){
			return self::_ifelse($condition, $true);
		} else {
			return self::_ifelse($condition, $true, $false);
		}
	}
	
	/**
	 *  
	 */
	public static function _ifelse(){
		$argv = self::start(func_get_args());
		$argc = count($argv);
		if($argc < 2){
			throw new ErrorException("Cannot use _ifelse with less than two arguments");
		}
		$isOdd = $argc % 2 != 0;
		$render = "";
		for($i = 0; $i < $argc; $i++){
			$condition = $argv[$i];
			if($i == $argc - 1 && $isOdd){
				//We are at the else clause, so condition is actually the code, and we are done
				$code = $condition;				
			} else {
				//If we are at an "else if" portion, we need to output the "else " part here
				if($i > 1){
					$render .= (self::$devMode?" ":"")."else ";
				}
				$code = $argv[++$i];
			}
			//Prepare the code
			$code = preg_replace("/[ \n]*$/m", '', $code);
			$codeLines = preg_split("/\n/m", $code);
			$code = "";
			$first = true;
			//Add indentation here
			foreach($codeLines as $codeLine){
				if(!$first){
					$code .= (self::$devMode?"\n":"");
				}
				$first = false;
				$code .= (self::$devMode?"\t":"") . $codeLine;
			}
			if($i == $argc - 1 && $isOdd){
				$render .= (self::$devMode?" ":"") . "else" . (self::$devMode?" ":"") . "{" . (self::$devMode?"\n":"");
				$render .= $code;
				$render .= (self::$devMode?"\n":"") . "}";
				break;
			}
			//Take off any end newlines in the condition
			$condition = preg_replace("/[ \n]*$/", '', $condition);
			$render .= "if(" . $condition . "){" . (self::$devMode?"\n":"");			
			$render .= $code;
			$render .= (self::$devMode?"\n":"") . "}";
		}
		return new Raw($render);
	}

	public static function alert($text) {
		self::start($text);
		return new Func("alert", $text);
	}

	public static function getHTML($selector) {
		self::start($selector);
		$selector = $selector->forceStringRender(self::$devMode);
		return new MultiFunc("$(" . $selector . ").html();");
	}

	public static function concat($args) {
		$args = func_get_args();
		foreach($args as $key => $arg){
			if(!($arg instanceof Structure)){
				$arg = new Atomic($arg);
				$args[$key] = new Raw($arg->forceStringRender());
			}
		}
		$newArgs = array();
		foreach ($args as $arg) {
			//Since we explicitely know we want to do string concatenation, 
			//not addition, let's ensure that's what will actually happen,
			//by forcing all the arguments to string if they are numeric.
			if ($arg instanceof Structure) {
				$newArgs[] = $arg;
			} else {
				$mix = new Atomic($arg);
				//Piggyback off of Atomic to do the escaping if it's a string
				$mix = $mix->forceStringRender();
				$newArgs[] = new Raw($mix);
			}
		}
		return new Operator("+", $newArgs);
	}

}

abstract class Structure {

	protected $data;
	
	public function __toString() {
		return $this->render(JS::$devMode) . (JS::$devMode?"\n":"");
	}
	
	/**
	 * For most types of objects, this will simply call render, however
	 * subclasses have the option of overriding it (for instance, Atomic does).
	 * @param type $devMode
	 * @param type $indent
	 * @return type 
	 */
	public function forceStringRender($devMode = false){		
		return $this->render($devMode);
	}
	public function render($devMode, $indent = ""){
		$render = $this->render0($devMode, $indent);
		return $render;
	}
	protected abstract function render0($devMode, $indent = "");
}

class Atomic extends Structure {

	public function __construct($value) {
		$this->data = $value;
	}

	private function _render0($forceString) {
		$data = $this->data;
		if(is_null($data)){
			return "null";
		}
		if(is_bool($data)){
			return $data?"true":"false";
		}
		if (is_numeric($data)) {
			if ($forceString) {
				return '"' . $data . '"';
			} else {
				return $data;
			}
		} else {
			$render = $data;
			//Escape inner double quotes
			$render = preg_replace('/"/', '\"', $render);
			$render = '"' . $render . '"';
			return $render;
		}
	}

	protected function render0($devMode, $indent = "") {
		return $this->_render0(false);
	}

	public function forceStringRender($devMode = false) {
		return $this->_render0(true);
	}

}

class Operator extends Structure {

	private $operator;
	private $args;

	public function __construct($operator, $args) {
		$this->operator = $operator;
		$this->args = $args;
	}

	protected function render0($devMode, $indent = "") {
		$args = $this->args;
		$first = true;
		$render = "";
		foreach ($args as $arg) {
			if (!$first) {
				$render .= $devMode ? " " . $this->operator . " " : $this->operator;
			}
			$first = false;
			if ($arg instanceof Structure) {
				$render .= $arg->render($devMode);
			} else {
				//Piggyback off of Atomic to do the escaping if it's a string
				$mix = new Atomic($arg);
				$mix = $mix->render($devMode);
				$render .= $mix;
			}
		}
		return $render;
	}

}

/**
 * For basic functions, this will suffice. 
 */
class Func extends Structure {

	private $name;
	private $args;

	public function __construct($name, $args, $args_ = null) {
		$args = func_get_args();
		$name = $args[0];
		unset($args[0]);
		$this->name = $name;
		$this->args = $args;
	}

	protected function render0($devMode, $indent = "") {
		$render = $indent . $this->name . "(";
		$first = true;
		foreach ($this->args as $arg) {
			if (!$first) {
				$render .= "," . ($devMode?"\n\t":"") . $indent;
			}
			$first = false;
			$renderedArg = $arg->render($devMode);
			$render .= $renderedArg;
		}
		$render .= ");";
		return $render;
	}

}

/**
 * More complex things should use this class. Basically, you render it,
 * then pass it to here. It is still a Func though, so the block
 * renderer will handle it correctly.
 */
class MultiFunc extends Func {

	private $render;

	public function __construct($rendered) {
		$this->render = $rendered;
	}

	protected function render0($devMode, $indent = "") {
		return $indent . $this->render;
	}

}

/**
 * Things that shouldn't be touched at all can be put in here 
 */
class Raw extends Structure {

	private $render;

	public function __construct($rendered) {
		$this->render = $rendered;
	}

	protected function render0($devMode, $indent = "") {
		return $this->render;
	}

}

?>
