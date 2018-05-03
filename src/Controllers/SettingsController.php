<?PHP

class SettingsController {
    
    protected $database;
    protected $logger;
    protected $session;

    public function __construct($database, $logger, $session) {
        $this->database = $database;
        $this->logger = $logger;
        $this->session = $session;
    }
    
    public function updateProfile($req, $res, $args) {
        $data = json_decode($req->getBody());
        
        if ( ! $data) {
            return $res->withJson(['state' => 'failure', 'cause' => 'No data was received.'], 200);
        }
        
        // f_data is filtered data (safe to use)
        $f_data = $this->filter_data($data);
        if ($f_data['state']) {
            return $res->withJson($f_data, 200);
        }
        
        // UPDATING INFO OF USER
        $userId = $this->session->get('user')['id'];
        $f_data['id'] = $userId;
        
        $this->database->addNewProfile($f_data);
        $this->database->update('credentials', $userId, 'email', $f_data['email']);
        $this->database->update('credentials', $userId, 'lname', $f_data['lname']);
        $this->database->update('credentials', $userId, 'fname', $f_data['fname']);
        if ($f_data['pass']) {
            $pass = password_hash($f_data['pass'], PASSWORD_DEFAULT);
            $this->database->update('credentials', $userId, 'pass', $pass);
        }
        
        return $res->withJson(['state' => 'ok', 'cause' => 'Profile updated with success!'], 200);
    }
    
    /**
     * Ceck variables from user form
     * 
     * @param   $data   object to be checked
     */
    private function filter_data($data) {
        
        $filtered['email'] = filter_var($data->email,           FILTER_SANITIZE_EMAIL);
        $filtered['email'] = filter_var($filtered['email'],     FILTER_VALIDATE_EMAIL);
        $filtered['lname'] = filter_var($data->lname,     FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $filtered['fname'] = filter_var($data->fname,     FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $filtered['bio'] = filter_var($data->bio,         FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $filtered['tags'] = filter_var($data->tags,       FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $filtered['sexpref'] = filter_var($data->sexpref, FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $filtered['gender'] = filter_var($data->gender,   FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $filtered['age'] = filter_var($data->age,           FILTER_SANITIZE_NUMBER_INT);
        $filtered['age'] = filter_var($filtered['age'],     FILTER_VALIDATE_INT);
        $filtered['pass'] = filter_var($data->pass,       FILTER_SANITIZE_STRING);
        
        if ( ! $filtered['age'] ) {
            return ['state' => 'failure', 'cause' => 'Invalid age.'];
        }
        else if ( ! $filtered['email'] ) {
            return ['state' => 'failure', 'cause' => 'Invalid email.'];
        }
        else if ( ! $filtered['lname']) {
            return ['state' => 'failure', 'cause' => 'Invalid last name.'];
        }
        else if ( ! $filtered['fname']) {
            return ['state' => 'failure', 'cause' => 'Invalid first name.'];
        }
        else if ( ! $filtered['gender'] || ! CreateProfilePost::checkGender($filtered['gender'])) {
            return ['state' => 'failure', 'cause' => 'Invalid gender.'];
        }
        else if ( ! $filtered['sexpref'] || ! CreateProfilePost::checkSexPref($filtered['sexpref'])) {
            return ['state' => 'failure', 'cause' => 'Invalid sexual preference.'];
        }
        else if ( ! $filtered['bio']) {
            return ['state' => 'failure', 'cause' => 'Invalid biography.'];
        }
        else if ( ! $filtered['tags'] || ! $filtered['tags'] = CreateProfilePost::checkTags($filtered['tags'])) {
            return ['state' => 'failure', 'cause' => 'Invalid tags.'];
        }
        if ( ! $filtered['pass'] ) {
            unset($filtered['pass']);
        }
        
        return $filtered;
    }
}
?>