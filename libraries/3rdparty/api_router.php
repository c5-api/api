<?php
class ApiRoute {

	private $ID;
	private $route;
	private $name;
	private $class;
	private $method;
	private $pkgHandle;
	
	private $parameters;
	
	private $enabled;

	public function __construct($obj = false) {
		$this->setPropertiesFromArray($obj);
	}

	public function setPropertiesFromArray($arr) {
		foreach($arr as $key => $prop) {
			$this->{$key} = $prop;
		}
	}
	
	public function getEnabled() {
		return $this->enabled;
	}

	public function getID() {
		return $this->ID;
	}

	public function getRoute() {
		return $this->route;
	}

	public function getName() {
		return $this->name;
	}

	public function getClass() {
		return $this->class;
	}

	public function getMethod() {
		return $this->method;
	}

	public function getPackageHandle() {
		return $this->pkgHandle;
	}
	
	public function getRegex() {
		return preg_replace("/:(\w+)/", "([\w-]+)", $this->getRoute());
	}
	
	public function getParameters() {
		return $this->parameters;
	}
	
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}

}

class ApiRouter {

	private $routes = array();

    public function map(ApiRoute $route, array $args = array()) {
        $this->routes[] = $route;
    }

    public function matchCurrentRequest() {
        $requestUrl = C5_API_REQUESTED_ROUTE;

        if(($pos = strpos($requestUrl, '?')) !== false) {
            $requestUrl =  substr($requestUrl, 0, $pos);
        }

        return $this->match($requestUrl);
    }

    public function match($requestUrl) {
                        
        foreach($this->routes as $route) {

            if (!preg_match("@^".$route->getRegex()."*$@i", $requestUrl, $matches)) continue;

            $params = array();

            if (preg_match_all("/:([\w-]+)/", $route->getRoute(), $argument_keys)) {

                $argument_keys = $argument_keys[1];

                foreach ($argument_keys as $key => $name) {
                    if (isset($matches[$key + 1]))
                        $params[$name] = $matches[$key + 1];
                }

            }

            $route->setParameters($params);
            return $route;
            
        }

        return false;
    }
    
}