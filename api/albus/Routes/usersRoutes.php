<?php 

$userVD = new albus\Core\Validation();
$userVD->setRules(array(
	'id' => array(),
	'username' => array('required', 'minLength' => 4, 'maxLength' => 30),
	'password' => array('required', 'minLength' => 6, 'maxLength' => 64),
	'email' => array('required', 'email', 'maxLength' => 120),
	'created' => array()
));

// Create a new user
$router->post('/users', function() use ($request, $response, $db, $userVD) {
	$response->setContentType('application/json');

	$body = json_decode($request->getBody(), true);
	if(!$userVD->test($body)) {
		echo json_encode($response->error(array('message' => $userVD->getMessage()), JSON_PRETTY_PRINT));
		return;
	}

	$users = array();
	try {

		// Check that username and email is available
		$stmt = $db->getDb()->prepare('SELECT DISTINCT
			(SELECT username FROM users WHERE username=:username LIMIT 1) as username,
			(SELECT email FROM users WHERE email=:email LIMIT 1) as email
			FROM users');
		$stmt->execute(array(':username' => $body['username'], ':email' => $body['email']));
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		if(isset($user['username'])) {
			echo json_encode($response->error(array('message' => 'Username is taken')), JSON_PRETTY_PRINT);
			return;
		}else if(isset($user['email'])) {
			echo json_encode($response->error(array('message' => 'Email address already in use')), JSON_PRETTY_PRINT);
			return;
		}

		// Continue with account creation
		$sql = 'INSERT INTO users (id, username, password, email, created) 
		VALUES (:id, :username, :password, :email, :created)';

		// Bcrypt password
		$body['password'] = password_hash($body['password'], PASSWORD_BCRYPT);

		$db->getDb()->beginTransaction();
		$stmt = $db->getDb()->prepare($sql);
		$insert = $stmt->execute($db->prepareData($body, $userVD->getRules()));

		$id = $db->getDb()->lastInsertId();
		$db->getDb()->commit();

		$stmt = $db->getDb()->prepare('SELECT id, username, email, created FROM users WHERE id=:id LIMIT 1');
		$stmt->execute(array(':id' => $id));
		$users = $stmt->fetch(PDO::FETCH_ASSOC);

	}catch(PDOException $e) {
		$db->getDb()->rollBack();
		echo $response->error($e->getMessage());
		return;
	}

	echo json_encode($response->created($users), JSON_PRETTY_PRINT);

});

// primarily used to check if username is available
$router->get('/users', function() use ($request, $response, $db) {
	$response->setContentType('application/json');
	
	try {
		// user's email address is not obtaining with this method! at least not when an authorized user isn't logged in
		$stmt = $db->getDb()->prepare('SELECT id, username, created FROM users
			WHERE username LIKE :username LIMIT :limit OFFSET :offset');
		$params = array(
			':username' => isset($_GET['username']) ? $_GET['username'] : '%',
			':limit' => isset($_GET['limit']) ? $_GET['limit'] : 25,
			':offset' => isset($_GET['offset']) ? $_GET['offset'] : 0
		);
		$stmt->execute($params);
		$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if(isset($_GET['fields'])) {
			$fields = array_flip(explode(',', $_GET['fields']));

			foreach($users as $key => $val)
				$users[$key] = array_intersect_key($val, $fields);
		}

	}catch(PDOException $e) {
		echo $response->error($e->getMessage());
		return;
	}

	echo json_encode($response->ok($users), JSON_PRETTY_PRINT);
});




