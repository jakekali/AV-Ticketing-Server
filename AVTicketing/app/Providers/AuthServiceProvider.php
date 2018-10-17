<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
	    'App\Ticket' => 'App\Policies\TicketPolicy'
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
	    \Gate::define('read-own-tickets', function (User $user) {
	    	return $user->hasPermission('readUserTickets') || $user->hasPermission('readAllTickets');
	    });
	    \Gate::define('write-own-tickets', function (User $user) {
	    	return $user->hasPermission('writeUserTickets') || $user->hasPermission('writeAllTickets');
	    });
	    \Gate::define('read-all-tickets', function (User $user) {
	    	return $user->hasPermission('readAllTickets');
	    });
	    \Gate::define('read-users', function (User $user) {
	    	return $user->hasPermission('readUsers');
	    });
	    \Gate::define('write-users', function (User $user) {
	    	return $user->hasPermission('writeUsers');
	    });
    }
}
