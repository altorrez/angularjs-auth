<?php 

// Should this be changed to /session post?
$router->post('/sessions', function() use ($request, $response, $db) {
	$response->setContentType('application/json');
	if(!$request->getAuthUser() || !$request->getAuthPass()) {
		$response->noauth();
		return;
	}

	try {
		$stmt = $db->getDb()->prepare('SELECT id, password FROM users WHERE username=:username LIMIT 1');
		$stmt->execute(array(':username' => $request->getAuthUser()));
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if(!$user) {
			echo json_encode($response->noauth(array('message' => 'Username does not exist')), JSON_PRETTY_PRINT);
			return;
		}
		if(!password_verify($request->getAuthPass(), $user['password'])) {
			echo json_encode($response->noauth(array('message' => 'Incorrect Username/Password')), JSON_PRETTY_PRINT);
			return;
		}

		// Create new session!
		$stmt = $db->getDb()->prepare('DELETE FROM sessions WHERE userid=:id');
		$stmt->execute(array(':id' => $user['id']));

		do {
			$bytes = openssl_random_pseudo_bytes(32, $cstrong);
		} while(!$cstrong);
		$session = bin2hex($bytes);
		$stmt = $db->getDb()->prepare('INSERT INTO sessions (session, userid, expire) 
			VALUES (:session, :userid, DATE_ADD(NOW(), INTERVAL 15 DAY))');
		$stmt->execute(array(':session' => $session, ':userid' => $user['id']));


	}catch(PDOException $e) {
		$db->getDb()->rollBack();
		echo $response->error($e->getMessage());
		return;	
	}
	$response->setCookie('session', $session); // set cookie for browser session only

	echo json_encode($response->ok(array('message' => 'successfully logged in')), JSON_PRETTY_PRINT);
});

// Reads cookie session to restore session for user
$router->get('/sessions', function() use ($request, $response, $db) {
	$response->setContentType('application/json');

	$session = $request->getCookie('session');
	if(!$session) {
		echo json_encode($response->error(array('message' => 'Cookie not present, try logging in again')), JSON_PRETTY_PRINT);
		return;
	}

	try {

		$stmt = $db->getDb()->prepare('SELECT User.id, User.username, User.email FROM users User
			INNER JOIN sessions Session ON Session.userid=User.id WHERE Session.session=:session AND Session.expire > NOW() LIMIT 1');

		$stmt->execute(array(':session' => $session));
		$session = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!$session) {
			// session is no longer valid, delete cookie on client!
			$response->deleteCookie('session');
			echo json_encode($response->noauth(array('message' => 'You must be logged in')), JSON_PRETTY_PRINT);
			return;
		}

	}catch(PDOException $e) {
		echo $response->error($e->getMessage());
		return;
	}

	echo json_encode($response->ok($session), JSON_PRETTY_PRINT);
});

// Kill session aka logout
$router->delete('/sessions', function() use ($request, $response, $db) {
	$response->setContentType('application/json');
	
	$session = $request->getCookie('session');
	if($session) {
		try {
			$stmt = $db->getDb()->prepare('DELETE FROM sessions WHERE session=:session');
			$stmt->execute(array(':session' => $session));
			$response->deleteCookie('session');

		}catch(PDOException $e) {
			$response->error(array('message' => $e->getMessage()), JSON_PRETTY_PRINT);
			return;
		}
	}
	// Always return ok on delete
	$response->ok();
});













