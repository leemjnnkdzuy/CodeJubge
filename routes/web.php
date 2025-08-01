<?php
$routes = [
    '' => 'pagesController@welcome',
    '/' => 'pagesController@welcome',
    'welcome' => 'pagesController@welcome',
    'home' => 'pagesController@home',
    
    'problems' => 'pagesController@problems',
    'problems/([a-zA-Z0-9_-]+)' => 'pagesController@problemDetail',
    'problems/(\d+)' => 'ProblemController@show',
    'problems/(\d+)/solve' => 'ProblemController@solve',
    'problems/create' => 'ProblemController@create',
    'problems/store' => 'ProblemController@store',
    
    'languages' => 'pagesController@languages',
    
    'submit' => 'SubmissionController@submit',
    'submissions' => 'SubmissionController@index',
    'submissions/(\d+)' => 'SubmissionController@show',
    
    // Authentication routes
    'login' => 'pagesController@login',
    'register' => 'pagesController@register',
    'logout' => 'pagesController@logout',
    'forgot-password' => 'pagesController@forgotPassword',
    
    // Documentation routes
    'docs/privacy' => 'pagesController@privacy',
    'docs/terms' => 'pagesController@terms',
    'docs/cookies' => 'pagesController@cookies',
    'docs/contact' => 'pagesController@contact',
    
    // User routes
    'profile' => 'ProfileController@index',
    'profile/update' => 'ProfileController@update',
    'profile/(\d+)' => 'ProfileController@show',
    'profile/change-password' => 'UserController@changePassword',
    'leaderboard' => 'leaderboardController@index',
    'leaderboard/api' => 'leaderboardController@api',
    'user/([a-zA-Z0-9_]+)' => 'UserController@viewProfile',
    
    // Admin routes
    'admin' => 'AdminController@index',
    'admin/problems' => 'AdminController@problems',
    'admin/users' => 'AdminController@users',
    'admin/submissions' => 'AdminController@submissions',
    
    // API routes
    'api/run-code' => 'ApiController@runCode',
    'api/submit-solution' => 'ApiController@submitSolution',
    'api/get-problem' => 'ApiController@getProblem',
];

return $routes;
?>