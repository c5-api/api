<?php defined('C5_EXECUTE') or die("Access Denied.");
/**
 * concrete5 API
 * This is the base api class
 *
 * @category Api
 * @package  ApiCore
 * @author   Michael Krasnow <mnkras@gmail.com>
 * @author   Lucas Anderson <lucas@lucasanderson.com>
 * @copyright 2011-2012 Michael Krasnow and Lucas Anderson
 * @license  See License.txt
 * @link     http://c5api.com
 */
class ApiRouteController {

	final private function setupRequestTask() {

		$req = ApiRouter::get();
		
		$task = substr('/' . $req->requestedPath, strlen($req->requestedRoute) + 1);

		// grab the whole shebang
		$taskparts = explode('/', $task);

		if (isset($taskparts[0]) && $taskparts[0] != '') {
			$method = $taskparts[0];
		}

		if ($method == '') {
			$method = 'run';
			$this->parameters = array();
		}

		$foundTask = false;

		try {
			$r = new ReflectionMethod(get_class($this), $method);
			$cl = $r->getDeclaringClass();
			if (is_object($cl)) {
				if ($cl->getName() != 'ApiRouteController' && strpos($method, 'on_') !== 0 && strpos($method, '__') !== 0 && $r->isPublic()) {
					$foundTask = true;
				}
			}
		} catch(Exception $e) {

		}

		if ($foundTask) {

			$this->task = $method;
			if (!is_array($this->parameters)) {
				$this->parameters = array();
				if (isset($taskparts[1])) {
					array_shift($taskparts);
					$this->parameters = $taskparts;
				}
			}

		} else {

 			$this->task = 'run';
			if (!is_array($this->parameters)) {
				$this->parameters = array();
				if (isset($taskparts[0])) {
					$this->parameters = $taskparts;
				}
			}

			$do400 = false;
			if (get_class($this) != 'BadRequestApiRouteController') { 
				if (!is_callable(array($this, $this->task)) && count($this->parameters) > 0) {
					$do400 = true;
				} else if (is_callable(array($this, $this->task))  && (get_class($this) != 'ForbiddenApiRouteController')) {
					// we use reflection to see if the task itself, which now much exist, takes fewer arguments than 
					// what is specified
					$r = new ReflectionMethod(get_class($this), $this->task);
					if ($r->getNumberOfParameters() < count($this->parameters)) {
						$do400 = true;
					}
				}
			}

			if($do400==true) {
			    if (in_array('__call', get_class_methods(get_class($this)))) {
			        $this->task = $method;
			        if (!is_array($this->parameters)) {
			            $this->parameters = array();
			            if (isset($taskparts[1])) {
			                array_shift($taskparts);
			                $this->parameters = $taskparts;
			            }
			        }
			        $do400 = false;
			    }
			}

			if ($do400) {
				$route = ApiRouteList::getRouteByPath('bad_request');
				$class = $txt->camelcase($route->route).'ApiRouteController';
				$cl = new $class;
				$cl->setupAndRun();
				
			}
 		}

 		
 	}

	/** 
	 * Based on the current request, the Controller object is loaded with the parameters and task requested
	 * The requested method is then run on the active controller (if that method exists)
	 * @return void
	 */	
	final public function setupAndRun() {
		$this->setupRequestTask();
		$this->on_start();

		if ($this->task) {
			return $this->runTask($this->task, $this->parameters);
		}
	}

	public function on_start() {

	}

	/** 
	 * Runs a task in the active controller if it exists.
	 * @return void
	 */
	final public function runTask($method, $params) {
		// can be an array of cyclable methods. The first one found is fired.
		if (is_array($method)) {
			$methodArray = $method;
		} else {
			$methodArray[] = $method;
		}
		foreach($methodArray as $method) {
			if (is_callable(array($this, $method))) {
				if(!is_array($params)) {
					$params = array($params);
				}
				return call_user_func_array(array($this, $method), $params);
			}
		}
		return null;
	}

	final public function respond($data) {
		
	}

}