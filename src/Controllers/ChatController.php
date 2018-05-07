<?PHP

class ChatController
{
    protected $view;
    protected $session;
    protected $database;
    
    public function __construct($view, $database, $session)
    {
        $this->view = $view;
        $this->database = $database;
        $this->session = $session;
    }
    
    
    /**
     * Return HTML page
     */
    public function getView($req, $res, $args)
    {
        $users = $this->database->getUserConnections($this->session->get('user')['id'], $this->session->get('user')['uname']);
        
        return $this->view->render($res, "chat.php", [
                'logout'    => true,
                'home'      => '/home',
                'search'    => true,
                'settings'  => true,
                'chat'      => true,
                'connections'   => $users
            ]);
    }
    
    /**
     * Check and fetch new msgs
     */
    public function fetchNewMsgs($req, $res, $args)
    {
        // curently logged user chat
        $uname = $this->session->get('user')['uname'];
        // with user:
        $suname = filter_var($req->getQueryParams()['user'], FILTER_SANITIZE_STRING);
        
        $last_id = filter_var($req->getQueryParams()['from_id'], FILTER_SANITIZE_STRING);
        $newMsgs = $this->database->getConversation($uname, $suname, (int)$last_id);
        $newMsgs['currUser'] = $this->session->get('user')['uname'];
        return $res->withJson($newMsgs, 200);
    }
    
    /**
     * Submit message
     */
    public function postMsg($req, $res, $args)
    {
        $json = json_decode($req->getBody());
        $uname = $this->session->get('user')['uname'];
        
        $receiver = filter_var($json->receiver, FILTER_SANITIZE_STRING);
        $msg = filter_var($json->msg, FILTER_SANITIZE_STRING);
        if (strlen($msg) > 255) {
            return $res->withJson(['status' => 'err', 'msg' => 'message too long'], 200);
        }
        $datetime = date("Y-m-d H:i:s");
        
        $this->database->addMsg($uname, $receiver, $msg, $datetime);
        return $res->withJson(['status' => 'ok', 'msg' => $msg, 'datetime' => $datetime, 'currUser' => $uname, 'sender' => $uname], 200);
    }
    
    /**
     * Get converation between users
     */
    public function getConversation($req, $res, $args)
    {
        // curently logged user chat
        $uname = $this->session->get('user')['uname'];
        // with user:
        $suname = $req->getQueryParams()['u'];
        
        $conv = $this->database->getConversation($uname, $suname);
        $conv['currUser'] = $uname;
        return $res->withJson($conv, 200);
    }
}
?>