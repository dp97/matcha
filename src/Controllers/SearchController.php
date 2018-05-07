<?PHP

class SearchController {
    
    protected $logger;
    protected $database;
    protected $view;
    protected $session;
    
    public function __construct($database, $logger, $view, $session)
    {
        $this->logger = $logger;
        $this->database = $database;
        $this->view = $view;
        $this->session = $session;
    }
    
    /**
     * On GET Request render the page.
     */
    public function getView($req, $res, $args)
    {
        return $this->view->render($res, 'search.php', [
                'logout'    => true,
                'settings'  => true,
                'search'    => true,
                'home'      => '/home',
                'atags'     => $this->database->getTable('tags')
            ]);
    }
    
    /**
     * On GET return preview info about users from database
     */
    public function getUsersPreview($req, $res, $args)
    {
        $uid = $this->session->get('user')['id'];
        $uname = $this->session->get('user')['uname'];
        
        $profiles = $this->database->getUsersData(10, $uid, $uname);
        $end = count($profiles);
        for ($i = 0; $i < $end; $i++) {
            $profiles[$i]['rating'] = $this->database->getFameRating($profiles[$i]['id']);
            $profiles[$i]['tags'] = $this->database->getUserLinkedTags($profiles[$i]['id']);
        }
        return $res->withJson($profiles, 200);
    }
    
    /**
     * Perform suggestions
     */
    function getSuggestions() {
        $uid = $this->session->get('user')['id'];
        
        $user_tags = $this->getUserLinkedTags($id);
        
    }
}
?>