<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

//Route::get('/mailable', function () {
//   $email = \App\Models\DealerEmail::first();
//
//   return new App\Mail\DealerEmailMail($email);
//});
