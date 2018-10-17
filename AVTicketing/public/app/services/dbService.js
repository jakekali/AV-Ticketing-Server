angular
	.module('avTicketing')
	.factory('dbService', ['$http', function ($http) {
		var apiPath = '/api/';
		return {
			getUnassignedTickets : function () {
				return $http({
					url: apiPath+'getUnassignedTickets',
					method : "GET"
				});
			},
			getMyTickets : function () {
				return $http({
					url: apiPath+'getMyTickets',
					method : "GET"
				}).then(function (response) {
					return response.data;
				});
			},
			getSoonTickets : function () {
				return $http({
					url: apiPath+'getSoonTickets',
					method : "GET"
				}).then(function (response) {
					return response.data;
				});
			},
			getTicketData : function (ticketID) {
				return $http({
					url: apiPath+'ticket/'+ticketID,
					method : "GET"
				}).then(function (response) {
					return response.data;
				});
			},
			getStatusArray : function () {
				return $http({
					url: apiPath+'getStatusArray',
					method : "GET"
				}).then(function (response) {
					return response.data;
				});
			},
			findAvMember : function (searchText) {
				return $http({
					url: apiPath+'searchMembers/'+searchText,
					method : "GET"
				}).then(function (response) {
					return response.data;
				});
			},
			getAvMembers : function () {
				return $http({
					url: apiPath+'searchMembers/*',
					method : "GET"
				}).then(function (response) {
					return response.data;
				})
			},
			/**
			 * @param userId - user id or -1 for current user
			 */
			getUserData : function (userId) {
				return $http({
					url: apiPath+'user/'+userId,
					method : "GET"
				}).then(function (response) {
					return response.data;
				})
			},
			getRoles : function () {
				return $http({
					url: apiPath+'roles',
					method : "GET"
				}).then(function (response) {
					return response.data;
				})
			},
			getUserRoles : function () {
				return $http({
					url: apiPath+'user/roles',
					method : "GET"
				}).then(function (response) {
					var returnArray = [];
					for(var i = 0; i < response.data.length; i++){
						returnArray.push(response.data[i].name);
					}
					return returnArray;
				})
			},
			syncWithSlack : function () {
				return $http({
					url: apiPath+'syncWithSlack',
					method : "GET"
				}).then(function (response) {
					return response.data;
				})
			},
			addRoleToUser : function (userID, roleID) {
				return $http({
					url: apiPath+'user/'+userID+'/roles/' + roleID,
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			addUserToTicket : function (userID, ticketID) {
				return $http({
					url: apiPath+'user/'+userID+'/tickets/' + ticketID,
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			getUserSchedule : function (userID, startDate) {
				return $http({
					url: apiPath+'user/'+userID+'/schedule/'+startDate,
					method : "GET"
				}).then(function (response) {
					return response.data;
				})
			},
			removeRoleFromUser : function (userID, roleID) {
				return $http({
					url: apiPath+'user/'+userID+'/roles/' + roleID,
					method : "DELETE"
				}).then(function (response) {
					return response.data;
				})
			},
			removeUserFromTicket : function (userID, ticketID) {
				return $http({
					url: apiPath+'user/'+userID+'/tickets/' + ticketID,
					method : "DELETE"
				}).then(function (response) {
					return response.data;
				})
			},
			updateAttribute : function (AttributeName, AttributeValue, AttributeType, ticketID) {
				return $http({
					url: apiPath+'ticket/'+ticketID+'/updateAttribute',
					data: {AttributeName: AttributeName, AttributeValue : AttributeValue, AttributeType: AttributeType},
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			register : function(email, password, firstName, lastName, roleID) {
				return $http({
					url: apiPath+'register',
					data: {email: email, password : password, firstName: firstName, lastName: lastName, roleID: roleID},
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			updateTicketStatus : function (ticketID, statusID) {
				return $http({
					url: apiPath+'ticket/'+ticketID+'/status/'+statusID,
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			sendToIT : function (ticketID) {
				return $http({
					url: apiPath+'ticket/'+ticketID+'/toIT',
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			updateFreshdesk : function (ticketID) {
				return $http({
					url: apiPath+'ticket/'+ticketID+'/updateFreshdesk',
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			updateTicketUserStatus : function (ticketID, status) {
				return $http({
					url: apiPath+'ticket/'+ticketID+'/user/status',
					data : {status: status},
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			sendMessage : function (ticketID, message, isNote) {
				var endpoint = isNote ? 'note' : 'message';
				return $http({
					url: apiPath+'ticket/'+ticketID+'/message',
					data : {message: message, type: endpoint},
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			updatePassword : function (oldPassword, newPassword) {
				return $http({
					url: apiPath+'password/change',
					data : {oldPassword: oldPassword, newPassword: newPassword},
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			updateUserInfo : function (email, firstName, lastName) {
				return $http({
					url: apiPath+'user/update',
					data : {email: email, firstName: firstName, lastName: lastName},
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			updateSettings : function (settings) {
				return $http({
					url: apiPath+'user/updateSettings',
					data : {settings: settings},
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			getSettings : function () {
				return $http({
					url: apiPath+'user/getSettings',
					method : "GET"
				}).then(function (response) {
					return response.data;
				})
			},
			sendPasswordResetLink : function (email) {
				return $http({
					url: apiPath+'password/sendResetEmail',
					data : {email: email},
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			validatePasswordResetToken : function (token) {
				return $http({
					url: apiPath+'password/validateToken',
					data : {token: token},
					method : "POST"
				}).then(function (response) {
					return response.data;
				})
			},
			resetPassword : function (token, password, password_confirmation, email) {
				return $http({
					url: apiPath+'password/reset',
					data : {
						token: token,
						password : password,
						password_confirmation : password_confirmation,
						email : email
					},
					method : "POST"
				}).then(function (response) {
					return response.data;
				}, function (response) {
					var responseData = response.data.password;
					var returnData = {
						type: 'Error',
						response: ''
					};
					for(var i = 0; i < responseData.length; i++){
						returnData.response += responseData[i] + "<br>";
					}
					return returnData;
				})
			}
		};
	}]);