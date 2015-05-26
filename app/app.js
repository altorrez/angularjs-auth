(function() {
	var app = angular.module('AuthApp', ['ngResource', 'base64']);

	app.controller('AuthController', ['$scope', 'Session', 'User', function($scope, Session, User) {
		$scope.loggedin = false;
		// Default
		$scope.showLogin = true;
		$scope.showSignup = false;
		$scope.user = {};
		$scope.newUser = false;
		$scope.error = false;

		var getSession = function() {
			var re = new RegExp('[; ]session=([^\\s;]*)');
			var sMatch = (' '+document.cookie).match(re);
			if ('session' && sMatch) return unescape(sMatch[1]);
				return false;
		};

		// continue deleting session cookie
		if(getSession()) {
			Session.resource({}).get(function(response) {
				// success
				$scope.loggedin = true;
				console.log(response);
			}, function(response) {
				// error
				// delete cookie
				console.log('an error occured');
				console.log(response.data);
			});
		}

		$scope.login = function(user) {
			Session.resource(user).login(user, function(response) {
				// success
				$scope.loggedin = true;
				$scope.user = {};
				$scope.error = false;
			}, function(response) {
				$scope.error = response.data.message;
			});
		};

		$scope.logout = function() {
			Session.resource({}).delete(function(response) {
				$scope.loggedin = false;
				$scope.showLogin = true;
			});
		};

		$scope.createUser = function(user) {
			User.save(user, function(response) {
				// success
				$scope.showLogin = true;
				$scope.showSignup = false;
				$scope.user = {};
				$scope.error = false;
			}, function(response) {
				$scope.error = response.data.message;
				// error
			});
		};


	}]);

	app.factory('Session', ['$resource', '$base64', function($resource, $base64) {
		return {
			resource: function(user) {
				return $resource('api/sessions', null, {
					login: {
						method: 'POST',
						headers: {
							'Authorization': 'Basic ' + $base64.encode(user.username + ':' + user.password)
						}
					}
				});
			}	
		};
	}]);

	app.factory('User', ['$resource', function($resource) {
		return $resource('api/users');
	}]);


})();










