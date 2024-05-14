<?php

namespace App\Providers;

use Laravel\Passport\Passport;
// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [

   ];
   
   public function boot()
   {
       $this->registerPolicies();
    //    if (! $this->app->routesAreCached()) {
    //        Passport::routes();
    //    }
   }
}
