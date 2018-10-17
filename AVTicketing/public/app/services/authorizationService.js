angular
	.module('avTicketing')
	.factory('authorizationService', [function () {
		return {
			isAdmin : function () {
				var roles = JSON.parse(localStorage.getItem('roles'));
				return (angular.isArray(roles) && (roles.indexOf('admin') != -1 || roles.indexOf('studentAdmin') != -1));
			}
		};
	}]);