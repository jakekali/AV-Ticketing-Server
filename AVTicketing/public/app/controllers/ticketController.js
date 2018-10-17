angular
	.module('avTicketing')
	.controller('ticketController', ['$stateParams', 'ticketData', 'statusArray', '$sanitize', 'dbService', 'uiCalendarConfig', 'authorizationService',
							function ($stateParams,   ticketData,   statusArray,   $sanitize,   dbService,   uiCalendarConfig,   authorizationService) {
		var vm = this;

		vm.isOwnTicket = function () {
			for(var i = 0; i < ticketData.users.length; i++){
				if(ticketData.users[i].id == ticketData.currUserID){
					return i;
				}
			}
			return -1;
		};

		vm.setUserStatus = function (status) {
			dbService.updateTicketUserStatus(ticketData.id, status).then(function (response) {
				vm.ticketData.users = response.users;
			});
		};

		vm.sendToIT = function () {
			dbService.sendToIT(ticketData.id);
		};

		vm.updateFreshdesk = function () {
			dbService.updateFreshdesk(ticketData.id);
		};

		vm.getTicketMonday = function () {
			var currentDate = moment().startOf('isoweek');
			if(ticketData.event_data && ticketData.event_data.EventDate){
				currentDate = moment(ticketData.event_data.EventDate).startOf('isoweek');
			}
			return currentDate;
		};

		vm.uiConfig = {
           calendar : {
	           height: 450,
	           editable: true,
	           header: {
		           left: 'title',
		           center: '',
		           right: 'today prev,next'
	           },
	           defaultView: 'schoolWeek',
	           views: {
		           schoolWeek: {
			           type: 'agenda',
			           duration: {days: 5},
			           buttonText: '7 day',
			           nowIndicator : true
		           }
	           },
	           defaultDate : vm.getTicketMonday()
           }
        };

		vm.events = [];
		vm.hoverSources = [
			{
				events : []
			}
		];



		vm.ticketData = ticketData;
		vm.statusArray = statusArray;
		vm.addMember = function (member) {
			if(member != undefined) {
				vm.ticketData.users.push(member);
				vm.ticketUserSelect = undefined;
				dbService.addUserToTicket(member.id, ticketData.id);
			}
		};
		vm.removeUser = function (index, user) {
			vm.ticketData.users.splice(index, 1);
			dbService.removeUserFromTicket(user.id, ticketData.id);
		};

		vm.getMemberMatches = function (searchText) {
			return dbService.findAvMember(searchText);
		};

		vm.avMembers = [];

		dbService.getAvMembers().then(function (response) {
			vm.avMembers = response;
		});

		vm.hoverUser = function (user) {
			console.log(uiCalendarConfig);
			if(!user){
				// vm.hoverData = "";
				return;
			}
			vm.hoverData = user.firstName + " " + user.lastName + "'s schedule goes here.";
			dbService.getUserSchedule(user.id, vm.getTicketMonday().format('MMDDYYYY')).then(function (response) {
				vm.hoverSources.splice(0, 1);
				vm.hoverSources.push({
					events : response
				});
			});

			
		};

		vm.updateAttribute = function (AttributeName, AttributeValue, AttributeType) {
			dbService.updateAttribute(AttributeName, AttributeValue, AttributeType, ticketData.id);
		};

		vm.updateStatus = function () {
			dbService.updateTicketStatus(ticketData.id, ticketData.StatusID);
		};

		vm.isAdmin = function () {
			return authorizationService.isAdmin();
		};

		vm.sendMessage = function (isNote) {
			dbService.sendMessage(ticketData.id, vm.message, isNote).then(function (response) {
				vm.ticketData.messages = response.messages;
			});
			vm.message = null;
		};

		vm.isCurrentUsersTicket = vm.isOwnTicket();
		console.log(vm.isCurrentUsersTicket);
	}]);