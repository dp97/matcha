<?PHP
/**
 * A Session Middleware
 */
$app->add(new \Slim\Middleware\Session([
    'name'          => 'user_session',
    'lifetime'      => '1 hour',
    'autorefresh'   => true
]));


/**
 * Check if user is logged in.
 */
$mustBeLoggedIn = function ($request, $response, $next) {
    $user = $this->session->get("user");
    if ($user == null || $user["status"] != "logged") {
        return $response->withRedirect('/login');
    }
    $response = $next($request, $response);
    return $response;
};

/**
 * Check if user is NOT logged in
 */
$mustNotBeLoggedIn = function ($request, $response, $next) {
    $user = $this->session->get("user");
    if ($user != null && $user["status"] == "logged") {
        return $response->withRedirect('/home');
    }
    $response = $next($request, $response);
    return $response;
};

?>