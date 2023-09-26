<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dealership extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'state',
        'zip_code',
        'phone',
        'corporate_name',
        'corporate_city',
        'corporate_state',
        'number_of_stores',
        'current_solution_name',
        'current_solution_use',
        'notes',
        'status',
    ];
}
