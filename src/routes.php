<?PHP
/**
 * ROUTES
 */
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Track users ip address.
 */
$app->add(new RKA\Middleware\IpAddress());

/**
 * Must Be Logged In Routes
 */
$app->group('', function () use ($app) {
    
    /**
     * This is first time Route to new users logged for first time.
     */
    $app->get('/create_profile', function (Request $req, Response $res) {
        return $this->view->render($res, 'create_profile.php', [
                'home'      => '/home',
                'logout' => true
                ]);
    });
    
    /**
     * Browse and Search Route
     */
    $app->get('/api/preview/users', 'SearchController:getUsersPreview');
    $app->get('/search', 'SearchController:getView');
    
    $app->get('/user/{id}', 'VisitController:visit');
    $app->post('/user/{action}/{id}', 'VisitController:action');
    
    /**
     * Chat Room Route
     */
    $app->get('/chat', 'ChatController:getView');
    $app->get('/chat/get/conversation', 'ChatController:getConversation');
    $app->get('/chat/get/newmsgs', 'ChatController:fetchNewMsgs');
    $app->post('/chat/post/msg', 'ChatController:postMsg');
    
    /**
     * Get data from form  
     */
    $app->post('/create_profile', 'CreateProfilePost:createProfile');
    $app->post('/upload/photo', 'CreateProfilePost:uploadPhoto');
    
    /**
     * Setttings Route to modify data
     */
    $app->get('/settings', function (Request $req, Response $res) {
        $user_id = $this->session->get('user')['id'];
        $profile_info = $this->database->getAllFrom('profiles', 'id', $user_id)[0];
        $profile_info['email'] = $this->database->get('email', 'credentials', 'id', $user_id)[0]['email'];
        $profile_info['fname'] = $this->database->get('fname', 'credentials', 'id', $user_id)[0]['fname'];
        $profile_info['lname'] = $this->database->get('lname', 'credentials', 'id', $user_id)[0]['lname'];
        foreach ($this->database->getUserLinkedTags($user_id) as $tag) {
            if ( ! $profile_info['tags']) {
                $profile_info['tags'] = $tag;
            } else {
                $profile_info['tags'] .= ", " . $tag;
            }
        }
        
        return $this->view->render($res, 'settings.php', [
            'home'      => '/home',
            'logout'    => true,
            'data'      => $profile_info,
            'search'    => true,
            'chat'      => true
            ]);
    });
    
    $app->post('/settings/update', 'SettingsController:updateProfile');
    

    /**
     * User Home Route
     */
    $app->get('/home', function (Request $request, Response $response) {
        // Check if user have profile, if not then redirect 'create_profile'
        $user_id = $this->session->get('user')['id'];
        
        $profile_info = $this->database->getAllFrom('profiles', 'id', $user_id)[0];
        if ( !$profile_info) {
            return $response->withRedirect('/create_profile');
        }
        
        $profile_info = array_merge($profile_info, $this->database->get('uname', 'credentials', 'id', $user_id)[0]);
        $profile_info = array_merge($profile_info, $this->database->get('fname', 'credentials', 'id', $user_id)[0]);
        $profile_info = array_merge($profile_info, $this->database->get('lname', 'credentials', 'id', $user_id)[0]);
        $profile_info['tags'] = $this->database->getUserLinkedTags($user_id);
        $profile_info['profile_photo'] = $this->database->getPhotos($user_id, 1)[0]['photo'];
        $profile_info['notifications'] = $this->database->getAllFrom('notifications', 'id', $user_id, "ORDER BY not_date DESC");
        
        
        $response = $this->view->render($response, 'home.php', [
                'home'          => "/home",
                'logout'        => true,
                'settings'      => true,
                'search'        => true,
                'profile_data'  => $profile_info,
                'chat'          => true
            ]);
        return $response;
    });
    
    
    /**
     * Logout Route
     */
    $app->get('/logout', function (Request $request, Response $response) {
        $this->database->update('profiles', $this->session->get('user')['id'], 'status', 'offline');
        $this->session->delete('user');
        $this->session->destroy();
        $this->logger->info('User logged out.');
        
        // Redirect user to root Landing page.
        return $response->withRedirect('/');
    });
    
})->add($mustBeLoggedIn);


/**
 * Must NOT Be Logged In Routes
 */
$app->group('', function () use ($app) {
        
    /**
     * Landing Route
     */
    $app->get('/', function (Request $request, Response $response) {
        $response = $this->view->render($response, 'index.php', [
                'home'      => "/",
                'login'     => true,
                'register'  => true
            ]);
        return $response;
    });
    
    /**
     * Reset User Password Route.
     * 
     * @param   $request    PSR-7 Request
     * @param   $response   PSR-7 Response
     * 
     * @return  $response   PSR-7 Response
     */
    $app->get('/reset_password', function (Request $req, Response $res) {
        // 'login' and 'register' is sent to display menu options acordingly.
        $res_args = [
            'login'         => true,
            'register'      => true,
            'pass'          => false
        ];
        
        // 'pass' for displaying the password input instead of email input.
        $req_args = $req->getQueryParams();
        $code = filter_var($req_args['code'], FILTER_SANITIZE_STRING);
        
        if ($code) {
            // To display password field instead of email field
            $res_args['pass'] = true;
            
            // Get code from 'reset_pass' to compare from one received from link
            $result = $this->database->get('id', 'reset_pass', 'code', $code);
            
            // If code is NOT VALID redirect to landingpage
            if (empty($result)) {
                return $res->withRedirect('/', 301);
            }
            
            // Send user id to use when quering db
            $res_args['user_id'] = $result[0]['id'];
            
            $this->logger->info("".$result[0]['id']);
            
            // Delete code for reset once accessed the page with it, for security.
            $this->database->removeFromResetPass($code);
        }
        
        $res = $this->view->render($res, 'reset_password.php', $res_args);
        return $res;
    });
    
    /**
     * Process Form for reset password
     * 
     * @return  array   - 'state', 'cause' on failure.
     */
    $app->post('/reset_password', function ($req, $res) {
        $parsedBody = $req->getParsedBody();
        
        // Get POST email variable
        $email = filter_var($parsedBody['email'], FILTER_SANITIZE_EMAIL);
        
        if ( $email ) {
        
            if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $res->withJson([
                    'state' => 'failure',
                    'cause' => 'Not a valid email.'
                    ]);
            }
            
            $exist = $this->database->exists('email', $email);
            
            if ($exist) {
                // send reset url link to mail
                $this->logger->info("[ Request_PasswordReset ] email: " . $email);
                $code = $this->mail->sendPassReset($email);
                
                // store code to reset_pass table in database
                $this->database->addToResetPass($code, $email);
                
                $status['state'] = 'success';
            } else {
                $status['state'] = 'failure';
                $status['cause'] = "No account with such email.";
            }
        } else {
            $pass = password_hash(filter_var($parsedBody['pass'], FILTER_SANITIZE_STRING), PASSWORD_DEFAULT);
            $id = filter_var($parsedBody['user_id'], FILTER_SANITIZE_INT | FILTER_VALIDATE_INT);
            
            $status['state'] = 'success';
            
            // Update the password in database
            $this->database->update('credentials', $id, 'pass', $pass);
        }
        return $res->withJson($status, 200);
    });
    
    
    /**
     * Login Route
     */
    $app->get('/login', function (Request $request, Response $response, array $args) {
        $response = $this->view->render($response, 'login.php', [
                'home'      => "/",
                'login'     => true,
                'register'  => true
            ]);
            
        // LOG
        $this->logger->addInfo("New user login request.");
        return $response;
    });
    
    $app->post('/login', function(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        
        $uname = filter_var($parsedBody['username'], FILTER_SANITIZE_STRING);
        $pass = filter_var($parsedBody['pass'], FILTER_SANITIZE_STRING);
        
        /**
         * Check if credentials match
         */
        $status = $this->database->checkCredentials($uname, $pass);
        
        if ($status['stat']) {
            $this->logger->info("User loged in with success: " . $uname);
            
            $id = $this->database->get('id', 'credentials', 'uname', $uname)[0]['id'];
            $this->database->update('profiles', $id, 'status', 'online');
            $this->database->update('profiles', $id, 'conTime', date("Y-m-d H:i:s"));
            /**
             * Register user to Session.
             */
            $this->session->set("user", ["status" => "logged"]);
            $this->session->merge("user", ["uname" => $uname]);
            $this->session->merge("user", ["id" => $id]);
            
            // Redirect user to his homepage
            return $response->withRedirect('/home');
        } else {
            $this->logger->addInfo("User failed to login.");
            return $response = $this->view->render($response, 'login.php', [
                'home'      => "/",
                'login'     => true,
                'register'  => true,
                'error'     => $status['cause']
            ]);
        }
        
        return $response;
    });
    
    
    
    /**
     * Register Route
     */
    $app->get('/register', function (Request $request, Response $response, array $args) {
        $response = $this->view->render($response, 'register.php', [
                'home'      => "/",
                'login'     => true,
                'register'  => true
            ]);
        
        // LOGGING
        $this->logger->addInfo("New user registration request.");
        
        return $response;
    });
    
    
    $app->post('/register', function (Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();
        
        // Sanitizing user input data...
        $email      = filter_var($parsedBody['email'],      FILTER_SANITIZE_EMAIL);
        $email      = filter_var($email, FILTER_VALIDATE_EMAIL);
        $username   = filter_var($parsedBody['username'],   FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $lname      = filter_var($parsedBody['lname'],      FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $fname      = filter_var($parsedBody['fname'],      FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $pass       = filter_var($parsedBody['pass'],       FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        
        // Checking for success...
        if ( $email == false || $username == false || $lname == false || $fname == false || $pass == false ) {
            return $response = $this->view->render($response, 'register.php', [
                'home'      => "/",
                'login'     => true,
                'register'  => true,
                'error'     => 'Please enter valid data.'
            ]);
        }
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
        
        // Add user info to database ===============================================
        $state = $this->database->addNewUser($email, $username, $lname, $fname, $pass_hash);
        if ($state['state'] == 'ok') {
            $this->logger->addInfo("Registered a new user with success.");
        } else {
            return $response = $this->view->render($response, 'register.php', [
                'home'      => "/",
                'login'     => true,
                'register'  => true,
                'error'     => $state['cause']
            ]);
        }
        
        return $response->withRedirect('/login', 301);
    });
    
})->add($mustNotBeLoggedIn);

?>