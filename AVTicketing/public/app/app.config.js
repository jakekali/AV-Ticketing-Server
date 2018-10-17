angular
	.module('avTicketing')
	.config(['$mdThemingProvider', function($mdThemingProvider){
		$mdThemingProvider.theme('default')
			.primaryPalette('blue')
			.warnPalette('red')
			.backgroundPalette('grey')
			.accentPalette('light-blue');
		$mdThemingProvider.theme('error')
			.primaryPalette('grey')
			.warnPalette('red')
			.backgroundPalette('red')
			.accentPalette('light-blue');
	}])
	.config(['$authProvider', function ($authProvider) {
		$authProvider.loginUrl = '/api/authenticate';
	}]);