<?php

/**
 * Routing class to match request URL's against given routes and map them to a controller action.
 */
class ApiRouter {

    /**
    * Array that holds all Route objects
    * @var array
    */ 
    private $routes = array();

    /**
     * Array to store named routes in, used for reverse routing.
     * @var array 
     */
    private $namedRoutes = array();

    /**
     * The base REQUEST_URI. Gets prepended to all route url's.
     * @var string
     */
    private $basePath = '';
    
    /**
     * Set the base url - gets prepended to all route url's.
     * @param string $base_url 
     */
    public function setBasePath($basePath) {
        $this->basePath = (string) $basePath;
    }

    /**
    * Route factory method
    *
    * Maps the given URL to the given target.
    * @param string $routeUrl string
    * @param mixed $target The target of this route. Can be anything. You'll have to provide your own method to turn *      this into a filename, controller / action pair, etc..
    * @param array $args Array of optional arguments.
    */
    public function map($routeUrl, $pkg = C5_API_HANDLE, $target = '', array $args = array()) {
        $route = new ApiRoute();

        $route->setUrl($this->basePath . $routeUrl);

        $route->setTarget($target);
        
        $route->setPkg($pkg);

        if(isset($args['methods'])) {
            $methods = explode(',', $args['methods']);
            $route->setMethods($methods);
        }

        if(isset($args['filters'])) {
            $route->setFilters($args['filters']);
        }

        if(isset($args['name'])) {
            $route->setName($args['name']);
            $this->namedRoutes[$route->getName()] = $route;
        }

        $this->routes[] = $route;
    }

    /**
    * Matches the current request against mapped routes
    */
    public function matchCurrentRequest() {
        $requestMethod = (isset($_POST['_method']) && ($_method = strtoupper($_POST['_method'])) && in_array($_method,array('PUT','DELETE'))) ? $_method : $_SERVER['REQUEST_METHOD'];

        return $this->match($_SERVER['REQUEST_URI'], $requestMethod);
    }

    /**
    * Match given request url and request method and see if a route has been defined for it
    * If so, return route's target
    * If called multiple times
    */
    public function match($requestUrl, $requestMethod = 'GET') {
    	//print_r($this->routes);
        foreach($this->routes as $route) {

            // compare server request method with route's allowed http methods
            if(!in_array($requestMethod, $route->getMethods())) continue;

            // check if request url matches route regex. if not, return false.
            if (!preg_match("@^".rtrim($route->getRegex(), '/')."*$@i", rtrim($requestUrl, '/'), $matches)) continue;

            $params = array();

            if (preg_match_all("/:(\w+)/", $route->getUrl(), $argument_keys)) {

                // grab array with matches
                $argument_keys = $argument_keys[1];

                // loop trough parameter names, store matching value in $params array
                foreach ($argument_keys as $key => $name) {
                    if (isset($matches[$key + 1]))
                        $params[$name] = $matches[$key + 1];
                }

            }
			
			$tar = $route->getTarget();
            return array('pkgHandle' => $route->getPkg(), 'controller' => $tar['controller'], 'action' => $tar['action'], 'params' => $params);
            
        }

        return false;
    }


    
    /**
     * Reverse route a named route
     * 
     * @param string $route_name The name of the route to reverse route.
     * @param array $params Optional array of parameters to use in URL
     * @return string The url to the route
     */
    public function generate($routeName, array $params = array()) {
        // Check if route exists
        if (!isset($this->namedRoutes[$routeName]))
            throw new Exception(t("No route with the name %s has been found.", $routeName));

        $route = $this->namedRoutes[$routeName];
        $url = $route->getUrl();

        // replace route url with given parameters
        if ($params && preg_match_all("/:(\w+)/", $url, $param_keys)) {

            // grab array with matches
            $param_keys = $param_keys[1];

            // loop trough parameter names, store matching value in $params array
            foreach ($param_keys as $i => $key) {
                if (isset($params[$key]))
                    $url = preg_replace("/:(\w+)/", $params[$key], $url, 1);
            }
        }

        return $url;
    }

}

class ApiRoute {
	
	/**
	* URL of this Route
	* @var string
	*/
	private $url;

	/**
	* Accepted HTTP methods for this route
	* @var array
	*/
	private $methods = array('GET','POST','PUT','DELETE');

	/**
	* Target for this route, can be anything.
	* @var mixed
	*/
	private $target;

	/**
	* The name of this route, used for reversed routing
	* @var string
	*/
	private $name;

	/**
	* Custom parameter filters for this route
	* @var array
	*/
	private $filters = array();

	/**
	* The package the route belongs to
	* @var string
	*/
	private $pkg;
	
	public function getUrl() {
		return $this->url;
	}

	public function setUrl($url) {
		$this->url = $url;
	}

	public function getTarget() {
		return $this->target;
	}

	public function setTarget($target) {
		$this->target = $target;
	}

	public function getMethods() {
		return $this->methods;
	}

	public function setMethods(array $methods) {
		$this->methods = $methods;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = (string) $name;
	}

	public function getPkg() {
		return $this->pkg;
	}
	
	public function setPkg($pkg) {
		$this->pkg = $pkg;
	}

	public function setFilters(array $filters) {
		$this->filters = $filters;
	}

	public function getRegex() {
		return preg_replace_callback("/:(\w+)/", array(&$this, 'substituteFilter'), $this->url);
	}

	private function substituteFilter($matches) {
		if (isset($matches[1]) && isset($this->filters[$matches[1]])) {
            return $this->filters[$matches[1]];
        }
        
        return "(\w+)";
	}

}