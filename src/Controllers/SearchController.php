<?PHP

class SearchController {
    
    protected $logger;
    protected $database;
    protected $view;
    protected $session;
    
    public function __construct($database, $logger, $view, $session) {
        $this->logger = $logger;
        $this->database = $database;
        $this->view = $view;
        $this->session = $session;
    }
    
    /**
     * On GET Request render the page.
     */
    public function getView($req, $res, $args) {
        $uid = $this->session->get('user')['id'];
        $uname = $this->session->get('user')['uname'];
        $suggestions = null;
        
        $profiles = $this->database->getUsersData(6, $uid, $uname);
        
        return $this->view->render($res, 'search.php', [
                'logout'    => true,
                'settings'  => true,
                'home'      => '/home',
                'sgts'      => $suggestions,
                'atags'     => $this->database->getTable('tags'),
                'profiles'  => $profiles
            ]);
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