angular
	.module('avTicketing')
	.controller('homeController', ['unassignedTickets', 'myTickets', 'soonTickets', function (unassignedTickets, myTickets, soonTickets) {
		var vm = this;
		vm.unassignedTickets = unassignedTickets;
		vm.myTickets = myTickets;
		vm.soonTickets = soonTickets;
		console.log(myTickets);
	}]);