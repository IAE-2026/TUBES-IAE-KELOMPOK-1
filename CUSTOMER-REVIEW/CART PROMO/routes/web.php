<?php

use Illuminate\Support\Facades\Route;
use Nuwave\Lighthouse\Http\GraphQLController;

Route::get('/', function () {
    return view('welcome');
});

Route::match(['GET', 'POST'], '/graphql', GraphQLController::class);

Route::get('/graphql-playground', function () {
    return view('graphql-playground');
});
