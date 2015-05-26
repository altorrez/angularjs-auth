<?php 
namespace albus\Core;

class Router {

	// track if valid route has been matched
	private $complete;

	// URI as stored in an assoc array
	private $uri;

	// Method request
	private $method;
	
	public function __construct() {

		$this->uri = $this->captureUri();
		$this->complete = false;
		$this->method = $_SERVER['REQUEST_METHOD'];
	}

	// Capture uri supplied by end user
	private function captureUri() {

		$uri = $_SERVER['REQUEST_URI'];

		// Does the passed URI contain parameters?
		// Chop them off. Request handles parameters. 
		// We only handle base URIs here
		$param_index = strpos($uri, '?');
		if($param_index)
			$uri = substr($uri, 0, $param_index);
		
		// Parse URI into array elements
		$uri = explode('/', $uri);

		// Removes an empty element at the beginning in the array caused by exploding
		$uri = array_splice($uri, 1);

		// Adjusting for true root directory to capture URI
		// This checks if the API was installed in a subfolder ex. mydomain.com/api/stuff
		// instead of capturing api (the root directory), start capturing at /stuff
		$root_dir = substr(ROOT, strrpos(ROOT, DS) + 1);

		// Search uri if it includes the root directory, if yes return index
		// TODO: test this with deeper nested folder structure to ensure working
		$root_uri_index = array_search($root_dir, $uri) + 1;
		if($root_uri_index !== false)
			$uri = array_splice($uri, $root_uri_index);

		// Check and remove empty elements in uri array (this is caused by trailing /s in uri)
		if($uri[count($uri) - 1] == '')
			array_splice($uri, -1);

		return $uri;
	}

	public function get($pattern, $callback) {

		if($this->complete || $this->method != 'GET')
			return;

		if(!$this->comparePattern($pattern))
			return;

		if(is_callable($callback)) {
			$this->complete = true; // Notify all other calls that a match has been found
			call_user_func_array($callback, $this->extractParameters($pattern));
		} // TODO: implement error thrown class
	}

	public function post($pattern, $callback) {

		if($this->complete || $this->method != 'POST')
			return;

		if(!$this->comparePattern($pattern))
			return;

		if(is_callable($callback)) {
			$this->complete = true; // Notify all other calls that a match has been found
			call_user_func_array($callback, $this->extractParameters($pattern));
		} // TODO: implement error thrown class
	}

	public function put($pattern, $callback) {

		if($this->complete || $this->method != 'PUT')
			return;

		if(!$this->comparePattern($pattern))
			return;

		if(is_callable($callback)) {
			$this->complete = true; // Notify all other calls that a match has been found
			call_user_func_array($callback, $this->extractParameters($pattern));
		} // TODO: implement error thrown class
	}

	public function delete($pattern, $callback) {

		if($this->complete || $this->method != 'DELETE')
			return;

		if(!$this->comparePattern($pattern))
			return;

		if(is_callable($callback)) {
			$this->complete = true; // Notify all other calls that a match has been found
			call_user_func_array($callback, $this->extractParameters($pattern));
		} // TODO: implement error thrown class
	}

	public function options($pattern, $callback) {

		if($this->complete || $this->method != 'OPTIONS')
			return;

		if(!$this->comparePattern($pattern))
			return;

		if(is_callable($callback)) {
			$this->complete = true; // Notify all other calls that a match has been found
			call_user_func_array($callback, $this->extractParameters($pattern));
		} // TODO: implement error thrown class
	}

	// converts string router pattern into array for comparison to uri
	private function preparePattern($pattern) {

		// Check if there is an optional parameter
		if($this->optParam($pattern))
			$pattern = preg_replace('~\(|\)~', '', $pattern);	

		// Break pattern into array
		$pattern = explode('/', $pattern);

		// Shift array to remove empty first element
		array_shift($pattern);

		return $pattern;
	}

	// Check if optional paramter is present in router pattern
	private function optParam($pattern) {

		return (strpos($pattern, '(') == true);
	}

	// Compare router pattern to uri
	private function comparePattern($pattern) {

		// Set optional param flags
		$opt = $this->optParam($pattern);

		// prepare pattern, transform router pattern string to assoc array
		$pattern = $this->preparePattern($pattern);

		// Compare uri to router pattern
		$uri_len = count($this->uri);

		// Request for root TODO: can do this better!
		if($uri_len == 0 && $pattern[0] == "")
			return true;
		
		// Check if uri and router pattern are same length
		if(!$opt && $uri_len !== count($pattern))
			return false;

		// if opt is true router pattern can be one less than uri
		if($opt && (count($pattern) - 1 > $uri_len))
			return false;

		// Check each part of uri to router pattern
		// ignoring : for set parameters as wildcards
		for($i = 0; $i < $uri_len; $i++) {
 			
 			// the uri is longer than router pattern
 			if(!isset($pattern[$i])) 
				return false;

			if(substr($pattern[$i], 0, 1) == ':')
				continue;
			else if($pattern[$i] == $this->uri[$i])
				continue;
			else
				return false;
		}

		return true;
	}

	private function extractParameters($pattern) {

		// keep original pattern to check if optional params were passed
		$orig_pattern = $pattern;
		// prepare pattern for testing! router pattern to array
		$pattern = $this->preparePattern($pattern);

		// Check for parameters in pattern to match via pattern and passed uri
		$results = array();
		$uri_len = count($this->uri);
		
		for($i = 0; $i < $uri_len; $i++) {

			if(substr($pattern[$i], 0, 1) == ':')
				$results[] = $this->uri[$i];
			
			// an optional parameter was passed that was not included, pass null
			if($i == ($uri_len - 1) && $this->optParam($orig_pattern))
				$results[] = null;
		}

		return $results;
	}

	public function __destruct() {

		// Router completed without finding a valid route, show a 404
		if(!$this->complete) {

			$response = new Response();
			$response->setContentType('text/html');
			echo $response->notfound('
				<!DOCTYPE html>
				<html>
				<head>
					<meta name="viewport" content="width=device-width,initial-scale=1.0">
					<title>Albus RESTful Framework</title>
					<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
				</head>
				<body>
					<div class="container">
						<div class="row">
							<div class="col-lg-12">
								<h1>404 Not Found</h1>
								<p class="lead">The requested URL was not found.</p>
							</div>
						</div>
					</div>
				</body>
				</html>
			');
		}
	}
}