angular
	.module('avTicketing')
	.config(['$stateProvider', '$urlRouterProvider', function ($stateProvider, $urlRouterProvider) {
		$urlRouterProvider.otherwise('/');

		var loginRequired = ['$q', '$location', '$auth', function($q, $location, $auth) {
			var deferred = $q.defer();
			if ($auth.isAuthenticated()) {
				deferred.resolve();
			} else {
				localStorage.removeItem("roles");
				var redirectUri = window.encodeURIComponent($location.path());
				$location.path('/login'+"/"+redirectUri);
			}
			return deferred.promise;
		}];

		$stateProvider
			.state('/', {
				url : '/',
				templateUrl : 'app/views/home.html',
				resolve :{
					loginRequired : loginRequired,
					unassignedTickets : ['dbService', 'authorizationService', function (dbService, authorizationService) {
						if(authorizationService.isAdmin()){
							return dbService.getUnassignedTickets().then(function (response) {
								return response.data;
							});
						}
						else{
							return [];
						}
					}],
					myTickets: ['dbService', function (dbService) {
						return dbService.getMyTickets().then(function (response) {
							return response;
						})
					}],
					soonTickets: ['dbService', function (dbService) {
						return dbService.getSoonTickets().then(function (response) {
							return response;
						});
					}]
				},
				controller : 'homeController',
				controllerAs : 'homeView'
			})
			.state('login', {
				url : '/login/{redirectURL}',
				templateUrl : 'app/views/login.html',
				controller : 'loginController',
				controllerAs : 'login'
			})
			.state('ticket', {
				url : '/ticket/{id:int}',
				templateUrl : 'app/views/ticket.html',
				controller : 'ticketController',
				controllerAs : 'ticket',
				resolve : {
					loginRequired : loginRequired,
					ticketData : ['$stateParams', 'dbService', function ($stateParams, dbService) {
						return dbService.getTicketData($stateParams.id).then(function (data) {
							return data;
						});
					}],
					statusArray : ['dbService', function (dbService) {
						return dbService.getStatusArray().then(function (response) {
							return response;
						});
					}]
				}
			})
			.state('admin', {
				url : '/admin',
				templateUrl: 'app/views/admin.html',
				controller : 'adminController',
				controllerAs : 'adminView',
				resolve : {
					loginRequired: loginRequired
				}
			})
			.state('admin.manageUsers', {
				url : '/manageUsers',
				templateUrl: 'app/views/manageUsers.html',
				controller : ['users', function (users) {
					this.users = users;
				}],
				controllerAs : 'manageUsers',
				resolve : {
					loginRequired: loginRequired,
					users : ['dbService', function (dbService) {
						return dbService.getAvMembers().then(function (response) {
							return response;
						});
					}]
				}
			})
			.state('admin.manageUser', {
				url : '/{user:int}',
				templateUrl: 'app/views/manageUser.html',
				controller : 'manageUserController',
				controllerAs : 'manageUser',
				resolve : {
					loginRequired: loginRequired,
					userData: ['$stateParams', 'dbService', function ($stateParams, dbService) {
						return dbService.getUserData($stateParams.user).then(function (response) {
							return response;
						})
					}],
					roles : ['dbService', function (dbService) {
						return dbService.getRoles().then(function (response) {
							return response;
						})
					}]
				}
			})
			.state('admin.registerUser', {
				url : '/newUser',
				templateUrl: 'app/views/registerUser.html',
				controller : 'registerUserController',
				controllerAs : 'registerView',
				resolve : {
					loginRequired: loginRequired,
					roles : ['dbService', function (dbService) {
						return dbService.getRoles().then(function (response) {
							return response;
						})
					}]
				}
			})
			.state('settings', {
				url : '/settings',
				templateUrl: 'app/views/settings.html',
				controller : 'settingsController',
				controllerAs : 'settingsView',
				resolve : {
					loginRequired: loginRequired,
					settings: ['dbService', function (dbService) {
						return dbService.getSettings().then(function (response) {
							return response;
						})
					}],
					userData: ['dbService', function (dbService) {
						return dbService.getUserData(-1).then(function (response) {
							return response;
						})
					}]
				}
			})
			.state('forgotPassword', {
				url : '/password/forgot',
				templateUrl: 'app/views/forgotPassword.html',
				controller : 'forgotPasswordController',
				controllerAs : 'forgotPasswordView'
			})
			.state('resetPassword', {
				url : '/password/reset/:token',
				templateUrl: 'app/views/resetPassword.html',
				controller : 'resetPasswordController',
				controllerAs : 'resetPasswordView',
				resolve: {
					validateToken : ['dbService', '$stateParams', function (dbService, $stateParams) {
						return dbService.validatePasswordResetToken($stateParams.token);
					}]
				}
			});

	}]);