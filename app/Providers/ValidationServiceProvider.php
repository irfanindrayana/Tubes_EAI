<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Add database connection names to validation exists rule
        $this->extendExistsValidation();
    }

    /**
     * Extend the exists validation rule to use the correct connection
     * for microservice database tables.
     */
    protected function extendExistsValidation(): void
    {
        // Map tables to their respective connections
        $connectionMap = [
            'users' => 'user_management',
            'user_profiles' => 'user_management',
            
            'routes' => 'ticketing',
            'schedules' => 'ticketing',
            'seats' => 'ticketing',
            'bookings' => 'ticketing',
            
            'payments' => 'payment',
            'payment_methods' => 'payment',
            
            'reviews' => 'reviews',
            'complaints' => 'reviews',
            
            'messages' => 'inbox',
            'message_recipients' => 'inbox',
            'notifications' => 'inbox',
        ];
        
        // Extend the exists rule
        Validator::resolver(function($translator, $data, $rules, $messages, $attributes) use ($connectionMap) {
            return new class($translator, $data, $rules, $messages, $attributes, $connectionMap) extends \Illuminate\Validation\Validator {
                protected $connectionMap;
                
                public function __construct($translator, $data, $rules, $messages, $attributes, $connectionMap)
                {
                    parent::__construct($translator, $data, $rules, $messages, $attributes);
                    $this->connectionMap = $connectionMap;
                }
                  public function validateExists($attribute, $value, $parameters)
                {
                    if (count($parameters) >= 1) {
                        $table = $parameters[0];
                        
                        // If the validation doesn't already specify a connection
                        // and the table exists in our connection map, add the connection
                        if (strpos($table, '.') === false && isset($this->connectionMap[$table])) {
                            $connection = $this->connectionMap[$table];
                            $parameters[0] = $connection . '.' . $table;
                        }
                    }
                    
                    return parent::validateExists($attribute, $value, $parameters);
                }
            };
        });
    }
}
