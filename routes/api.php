<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Log;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/user/token', function (Request $request) {
    Log::info($request->all());
    $request->validate([
        'email' => 'required|email:api',
        'password' => 'required:api',
        'device_name' => 'required:api',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'The provided credentials are incorrect.'], 401);
    }

    return response()->json(['token' => $user->createToken($request->device_name)->plainTextToken]);
});

Route::post('/user/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
})->middleware('auth:sanctum');

Route::get('/current-stats', 'App\Http\Controllers\API\GeneralController@fetchCurrentState')->middleware('auth:sanctum');

Route::post('/checkin', 'App\Http\Controllers\API\CheckinController@checkin')->middleware('auth:sanctum');
Route::get('/projects', 'App\Http\Controllers\API\ProjectController@getProjects')->middleware('auth:sanctum');
Route::get('/projects/{project_id}/tasks', 'App\Http\Controllers\API\TaskController@getProjectTasks')->middleware('auth:sanctum');
Route::post('/track/{task_id}', 'App\Http\Controllers\API\TaskController@trackTask')->middleware('auth:sanctum');
Route::post('/screenshot/{task_id}', 'App\Http\Controllers\API\TaskController@uploadScreenshot')->middleware('auth:sanctum');