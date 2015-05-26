<?php 

namespace albus\Core;

// TODO: This needs to be reworked better handling of error messages in test
class Validation {

	private $rules;
	private $errorMessage;

	public function __construct() {

	}

	public function setRules($rules) {
		$this->rules = $rules;
	}

	public function getRules() {
		return $this->rules;
	}

	public function getMessage() {
		return $this->errorMessage;
	}

	public function test($model) {
		$fields = $this->rules;

		$validate = array();
		$validate['valid'] = true; 	// Assume the best
		$validate['error'] = false; // Begin with no error
		
		$test_a = array();
		foreach($fields as $field => $expr) {	// Check each field
			foreach($expr as $value => $test) { // Check each expression and test
				
				if($test === 'required' || $field === 'email') // This check is used to fix key issues with PHP
					$value = $test;
				
				$exists = isset($model[$field]);
				switch($value) {
					case 'required': if(!$exists) $validate['error'] = "$field is required"; break;
					case 'minLength': if($exists && strlen($model[$field]) < $test) $validate['error'] = "$field must be at least $test characters"; break;
					case 'maxLength': if($exists && strlen($model[$field]) > $test) $validate['error'] = "$field must be at most $test characters"; break;
					case 'equalTo': if($exists && $model[$field] !== $test) $validate['error'] = "$field must be equal to $test"; break;
					case 'minInt': if($exists && $model[$field] < $test) $validate['error'] = "$field must be at least $test"; break;
					case 'maxInt': if($exists && $model[$field] > $test) $validate['error'] = "$field must be at most $test"; break;
					case 'email': if($exists && !filter_var($model[$field], FILTER_VALIDATE_EMAIL)) $validate['error'] = "$field must be a valid email address"; break;
					case 'inArray': if($exists && !in_array($model[$field], $test)) $validate['error'] = $model[$field] . " is not a valid value for $field"; break;
					default: break;
				}

				// something is wrong! validation did not pass
				if($validate['error'] !== false) {
					$validate['valid'] = false;
					$this->errorMessage = $validate['error'];
					return false;
					// return $validate;
				}
			}
			
		}
		// Everything went as planned, let us leave happily!
		return $validate;
	}
}