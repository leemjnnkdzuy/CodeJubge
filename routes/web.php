<?php
$routes = [
    '' => 'pagesController@welcome',
    '/' => 'pagesController@welcome',
    'welcome' => 'pagesController@welcome',
    'home' => 'pagesController@home',
    'discussions' => 'pagesController@discussions',
    'api/discussions' => 'DiscussionController@api',
    'api/discussions/(\d+)/edit' => 'DiscussionController@getForEdit',
    'api/discussions/(\d+)' => 'DiscussionController@handleDiscussionById',
    'api/discussions/(\d+)/user-interactions' => 'DiscussionController@getUserInteractions',
    'api/discussions/like' => 'DiscussionController@like',
    'api/discussions/bookmark' => 'DiscussionController@bookmark',
    'api/discussions/replies' => 'DiscussionController@createReply',
    'api/discussions/reply-like' => 'DiscussionController@likeReply',
    'api/discussions/mark-solution' => 'DiscussionController@markSolution',
    'discussions/api' => 'DiscussionController@api',
    'discussions/like' => 'DiscussionController@like',
    'discussions/bookmark' => 'DiscussionController@bookmark',
    'discussions/create' => 'DiscussionController@create',
    'discussions/store' => 'DiscussionController@store',
    'api/discussions/create' => 'DiscussionController@apiCreate',
    'discussions/(\d+)/edit' => 'DiscussionController@edit',
    'discussions/(\d+)/delete' => 'DiscussionController@delete',
    'discussions/(\d+)' => 'DiscussionController@show',
    
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
    'admin/login' => 'AdminController@login',
    'admin/problems' => 'AdminController@problems',
    'admin/problems/create' => 'AdminController@createProblem',
    'admin/problems/store' => 'AdminController@storeProblem',
    'admin/problems/(\d+)/edit' => 'AdminController@editProblem',
    'admin/problems/(\d+)/update' => 'AdminController@updateProblem',
    'admin/problems/(\d+)/delete' => 'AdminController@deleteProblem',
    'admin/users' => 'AdminController@users',
    'admin/users/table-data' => 'AdminController@getUsersTableData',
    'admin/users/get/(\d+)' => 'AdminController@getUserById',
    'admin/users/create' => 'AdminController@createUser',
    'admin/users/update/(\d+)' => 'AdminController@updateUser',
    'admin/users/delete/(\d+)' => 'AdminController@deleteUser',
    'admin/submissions' => 'AdminController@submissions',
    'admin/contests' => 'AdminController@contests',
    
    // API routes
    'api/run-code' => 'ApiController@runCode',
    'api/submit-solution' => 'ApiController@submitSolution',
    'api/get-problem' => 'ApiController@getProblem',
];

return $routes;
?>