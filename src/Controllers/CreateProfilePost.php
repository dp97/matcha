<?PHP
use Slim\Http\UploadedFile;

class CreateProfilePost {
    
    private $database;
    private $session;
    protected $logger;
    
    public function __construct($database, $session, $logger) {
        $this->database = $database;
        $this->session = $session;
        $this->logger = $logger;
    }
    
    /**
     * Upload images
     */
    public function uploadPhoto($req, $res, $args) {
        $uploadedPhotos = $req->getUploadedFiles();
        
        if ( empty($uploadedPhotos) ) {
            return $res->withJson(['state' => 'failure', 'cause' => 'No photo selected.'], 200);
        }
        
        $uphoto = $uploadedPhotos['photo'];
        if ($uphoto->getError() === UPLOAD_ERR_OK) {
            $photo = "data:image/jpg;base64, " . base64_encode( file_get_contents($uphoto->file ));
            $user = $this->session->user;
            
            if ((int)$this->database->countPhotos($user['id']) > 5) {
                return $res->withJson(['state' => 'failure', 'cause' => 'Maximum nr of photos reached (5).'], 200);
            }
            if ( ! $this->database->addNewPhoto($user['id'], $photo)) {
                return $res->withJson(['state' => 'failure', 'cause' => 'En error occured.'], 200);
            }
        } else {
            return $res->withJson(['state' => 'failure', 'cause' => 'Failed to upload image.'], 200);
        }
        return $res->withJson(['state' => 'ok', 'cause' => 'Photo Uploaded!', 'photo' => $photo], 200);
    }
    
    public function createProfile($req, $res, $args) {
        // store
        $fdata = [];
        $ret['state'] = 'ok';
        $ret['cause'] = 'User profile Created with success!';
        
        // Getting form data
        $rdata = json_decode($req->getBody());
        
        // filter variables
        $fdata['gender']     = filter_var($rdata->gender,    FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $fdata['sexpref']    = filter_var($rdata->sexpref,   FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $fdata['bio']        = filter_var($rdata->bio,       FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $fdata['tags']       = filter_var($rdata->tags,      FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $fdata['hlocation']  = filter_var($rdata->hlocation, FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $fdata['location']   = filter_var($rdata->location,  FILTER_SANITIZE_STRING | FILTER_VALIDATE_STRING);
        $fdata['age']        = filter_var($rdata->age,       FILTER_SANITIZE_NUMBER_INT);
        $fdata['age']        = filter_var($fdata['age'],        FILTER_VALIDATE_INT);
       
        if ( ! $fdata['age']) {
            $ret['state'] = 'failure';
            $ret['cause'] = 'Invalid Age.';
        }
       
        if ( ! $fdata['gender'] || ! $this->checkGender($fdata['gender'])) {
            $ret['state'] = 'failure';
            $ret['cause'] = 'Invalid Gender.';
        }
        
        if ( ! $fdata['sexpref'] || ! $this->checkSexPref($fdata['sexpref'])) {
            $ret['state'] = 'failure';
            $ret['cause'] = 'Invalid Sexual Preference.';
        }
        
        if ( ! $fdata['bio']) {
            $ret['state'] = 'failure';
            $ret['cause'] = 'Please fill in Biography field.';
        }
        
        if ( ! $fdata['tags'] || empty($fdata['tags'] = $this->checkTags($fdata['tags'])) ) {
            $ret['state'] = 'failure';
            $ret['cause'] = 'Insert correct values in Interests field.';
        }
        
        if ($this->database->countPhotos($user['id']) == 5) {
            $ret['state'] = 'failure';
            $ret['cause'] = 'Upload photos first.';
        }
        
        if ($ret['state'] == 'ok') {
            $user = $this->session->user['id'];
            $fdata['id'] = $user;
            
            $ret = $this->saveProfile($fdata);
            if ($result['state'] == 'ok') {
                return $res->withRedirect('/home', 301);
            }
        }
        
        return $res->withJson($ret, 200);
    }
    
    /**
     * Save profile info to Database
     * 
     * @param   $param  data about profile
     */
    private function saveProfile($profile) {
        return $this->database->addNewProfile($profile);
    }
    
    /**
     * Check for null indexes
     * 
     * @param   $photos array
     * 
     * @return  array
     */
    function checkPhotos($photos) {
        $res = array();
        foreach ($photos as $k => $v) {
            if ($v != null) {
                $res[$k] = $v;
            }
        }
        return $res;
    }
    
    /**
     * Check if string contains valid tags of form (tag, )
     * 
     * @param   $tags   string
     * 
     * @return  array null on error
     */
    function checkTags($tags) {
        $t = array();
        
        foreach (explode(",", $tags) as $tag) {
            $trim_tag = trim($tag);
            
            array_push($t, $trim_tag);
            if (preg_match('/\s/', $trim_tag)) {
                return [];
            }
        }
        return $t;
    }
    
    /**
     * Check if Gender is valid one
     * 
     * @param   $gender string
     * 
     * @return  bool
     */
    function checkGender($gender) {
        return in_array($gender, ['Male', 'Female']);
    }
    
    /**
     * Check if sexual preference is a valid one
     * 
     * @param   $sexpref string
     * 
     * @return  bool
     */
    function checkSexPref($sexpref) {
        $valid_pref = ['Heterosexual', 'Homosexual', 'Bisexual', 'Asexual'];
        return in_array($sexpref, $valid_pref);
    }
}
?>