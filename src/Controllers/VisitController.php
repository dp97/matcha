<?PHP

/**
 * Visit a users profile
 */
class VisitController
{
    var $database;
    var $view;
    var $logger;
    var $session;
    
    public function __construct($database, $view, $logger, $session)
    {
        $this->database = $database;
        $this->view = $view;
        $this->logger = $logger;
        $this->session = $session;
    }
    
    public function visit($req, $res, $args)
    {
        $visitor_uname = $this->session->get('user')['uname'];
        $uid = $this->session->get('user')['id'];
        $sid = filter_var($args['id'], FILTER_SANITIZE_STRING);
        
        if ($sid == $uid) {
            return $res->withRedirect('/home', 301);
        }
        
        $profile_info = $this->database->getAllFrom('profiles', 'id', $sid)[0];
        if ( ! $profile_info) {
            return $res->getBody()->write('no information on user.');
        }
        
        // ADD to visits history.
        $this->database->setVisitor('visitors', 'visitor', 'visit_date', $sid, $visitor_uname);
        
        $profile_info = array_merge($profile_info, $this->database->get('uname', 'credentials', 'id', $sid)[0]);
        $profile_info = array_merge($profile_info, $this->database->get('fname', 'credentials', 'id', $sid)[0]);
        $profile_info = array_merge($profile_info, $this->database->get('lname', 'credentials', 'id', $sid)[0]);
        $profile_info['tags'] = $this->database->getUserLinkedTags($sid);
        $profile_info['profile_photo'] = $this->database->getPhotos($sid, 1)[0]['photo'];
        $profile_info['rating'] = $this->database->getFameRating($sid);
        // buttons
        $ifILiked = $this->database->existsVisitor('likes', 'liker', $sid, $visitor_uname);
        $ifHeLiked = $this->database->existsVisitor('likes', 'liker', $uid, $profile_info['uname']);
        
        if ( $ifILiked && $ifHeLiked ) {
            $profile_info['likeTxt'] = "Connected";
            $profile_info['likeBtn'] = "Disconnect";
        } else if ( $ifHeLiked ) {
            $profile_info['likeTxt'] = "liked you";
            $profile_info['likeBtn'] = "Like Back";
        } else if ( $ifILiked ) {
            $profile_info['likeBtn'] = "Unlike";
        } else {
            $profile_info['likeBtn'] = "Like";
        }
        
        $profile_info['blockBtn'] = $this->database->existsVisitor('blocks', 'blocker', $sid, $visitor_uname);
        $profile_info['reportBtn'] = $this->database->existsVisitor('reports', 'reporter', $sid, $visitor_uname);
        
        if ( $profile_info['status'] == 'offline' ) {
            $profile_info['status'] .= " last connection: " . $profile_info['conTime'];
        }
        
        
        return $this->view->render($res, 'visit.php', [
                'logout'        => true,
                'home'          => '/home',
                'settings'      => true,
                'search'        => true,
                'profile_data'  => $profile_info
            ]);
    }
    
    public function action($req, $res, $args)
    {
        $action = json_decode($req->getBody());
        
        // sid refers to visited user and uname refers to visitor username
        $sid = $args['id'];
        $suname = $this->database->get('uname', 'credentials', 'id', $sid)[0]['uname'];
        $uname = $this->session->get('user')['uname'];
        // uid refers to currently logged in user
        $uid = $this->session->get('user')['id'];
        
        if ($args['action'] == 'like')
        {
            if ( $this->database->existsVisitor('blocks', 'blocker', $sid, $uname) ) {
                return $res->withJson(['stat' => 'failure', 'cause' => 'you blocked this user', 'btntxt' => 'blocked'], 200);
            }
            if ( $this->database->existsVisitor('blocks', 'blocker', $uid, $suname) ) {
                return $res->withJson(['stat' => 'failure', 'cause' => 'you are blocked by this user', 'btntxt' => 'blocked'], 200);
            }
            
            if ( $this->database->existsVisitor('likes', 'liker', $sid, $uname) )
            {
                // perform un-like
                $this->database->unsetVisitor('likes', 'liker', $sid, $uname);
                $this->database->addToNotifications($sid, "User <a class='notify-link' href='/user/$uid'>$uname</a> Unliked you!");
                return $res->withJson(['stat' => 'ok', 'cause' => 'you unliked this user', 'btntxt' => 'Like'], 200);
            }
            
            // perform like
            $this->database->setVisitor('likes', 'liker', 'like_date', $sid, $uname);
            $this->database->addToNotifications($sid, "User <a class='notify-link' href='/user/$uid'>$uname</a> liked you!");
            return $res->withJson(['stat' => 'ok', 'cause' => 'you liked this user', 'btntxt' => 'Unlike'], 200);
        }
        else if ($args['action'] == 'block')
        {
            if ( $this->database->existsVisitor('blocks', 'blocker', $sid, $uname) )
            {
                // perform un-block
                $this->database->unsetVisitor('blocks', 'blocker', $sid, $uname);
                $this->database->addToNotifications($sid, "User <a class='notify-link' href='/user/$uid'>$uname</a> Unblocked you!");
                return $res->withJson(['stat' => 'ok', 'cause' => 'you unblocked this user', 'btntxt' => 'Block'], 200);
            }
            
            // perform block
            $this->database->setVisitor('blocks', 'blocker', 'block_date', $sid, $uname);
            // also if remove each others likes
            if ( $this->database->existsVisitor('likes', 'liker', $sid, $uname) ) {
                $this->database->unsetVisitor('likes', 'liker', $sid, $uname);
            }
            if ( $this->database->existsVisitor('likes', 'liker', $uid, $suname) ) {
                $this->database->unsetVisitor('likes', 'liker', $uid, $suname);
            }
            
            $this->database->addToNotifications($sid, "User <a class='notify-link' href='/user/$uid'>$uname</a> Blocked you!");
            return $res->withJson(['stat' => 'ok', 'cause' => 'you blocked this user', 'btntxt' => 'Unblock'], 200);
        }
        else if ($args['action'] == 'report')
        {
            if ( $this->database->existsVisitor('reports', 'reporter', $sid, $uname) )
            {
                // perform un-report
                $this->database->unsetVisitor('reports', 'reporter', $sid, $uname);
                $this->database->addToNotifications($sid, "Report as Fake on your account was resigned.");
                return $res->withJson(['stat' => 'ok', 'cause' => 'you unreported this user', 'btntxt' => 'Report fake'], 200);
            }
            
            // perform report
            $this->database->setVisitor('reports', 'reporter', 'report_date', $sid, $uname);
            $this->database->addToNotifications($sid, "Your account was reported as Fake!");
            return $res->withJson(['stat' => 'ok', 'cause' => 'you reported this user', 'btntxt' => 'Unreport'], 200);
        }
        
        return $res->withCode(404);
    }
}
?>