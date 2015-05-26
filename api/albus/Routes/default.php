<?php 

$router->get('/', function() use ($response) {
	$response->setContentType('text/html');
	$template = '
	<!DOCTYPE html>
	<html>
	<head>
		<meta name="viewport" content="width=device-width,initial-scale=1.0">
		<title>AlbusPHP Framework - Microblog</title>
		<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
	</head>
	<body>
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h1>Albus RESTful Framework</h1>
				</div>
			</div>
		</div>
	</body>
	</html>
	';
	echo $template;
});

$router->get('/example(/:message)', function($message) use ($request, $response) {
	$response->setContentType('application/json');

	echo json_encode($response->ok(array('data' => $message)), JSON_PRETTY_PRINT);
});