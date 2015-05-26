<?php 

namespace albus\Core;

class Response {

	public function setContentType($type) {
		header("Content-Type: $type");
	}

	public function setHeader($name, $value) {
		header("$name: $value");
	}

	public function ok($data = null) {
		
		http_response_code(200);
		return $data;
	}

	public function created($data = null) {
		
		http_response_code(201);
		return $data;
	}

	public function error($data = null) {
		
		http_response_code(400);
		return $data;
	}

	public function noauth($data = null) {
		
		http_response_code(401);
		return $data;
	}

	public function notfound($data = null) {

		http_response_code(404);
		return $data;
	}

	public function setCookie($name, $value, $time = null) {
		if($time == null)
			setcookie($name, $value, null, '/', null);
		else
			setcookie($name, $value, time() + $time, '/', null);
	}

	public function deleteCookie($name) {
		setcookie($name, null, time()-1, '/', null);
	}
}