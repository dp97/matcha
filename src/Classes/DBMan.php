<?PHP
/* 
** Class:   DBMan
** By:      dpetrov
** Scope:   Interactions with database for user 'credentials' manipulation.
*/

class DBMan {
    private $conn = null;
    private $logger;
    
    /**
     * Constructor
     * 
     * @param   array               $config [configuration]
     * @param   \Monolog\Logger     $logger [logging]
     */
    public function __construct(array $config, $logger) {
        $this->logger = $logger;
        $this->conn = $this->setupDatabase($config);
    }
    
    function __destruct() {
        $this->setConn(null);
    }
    
    /**
     * SET UP THE DATABASE
     * 
     * @param   array $config [DSN configuration, user and pass.]
     * 
     * @return  PDO [null on error]
     */
    function setupDatabase(array $config) {
        try {
            $pdo = new PDO('mysql:host=' . $config['host'], $config['user'], $config['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Creating the database if it not exists ==========================
            $sql = "CREATE DATABASE IF NOT EXISTS " . $config['dbname'] . ";";
            $pdo->exec($sql);
            
            // Select the database =============================================
            $sql = "USE " . $config['dbname'] . ";";
            $pdo->exec($sql);
            
            // Create credential table if they dont exists =====================
            $sql = "
                    CREATE TABLE IF NOT EXISTS credentials (
                        id INT(6) AUTO_INCREMENT PRIMARY KEY,
                        email VARCHAR(50) NOT NULL,
                        uname VARCHAR(50) NOT NULL,
                        fname VARCHAR(50) NOT NULL,
                        lname VARCHAR(50) NOT NULL,
                        pass VARCHAR(255) NOT NULL
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
            // Table for user password reset request
            $sql = "
                    CREATE TABLE IF NOT EXISTS reset_pass (
                        nr INT(6) PRIMARY KEY AUTO_INCREMENT NOT NULL,
                        id INT(6),
                        code VARCHAR(100) NOT NULL,
                        FOREIGN KEY (id) REFERENCES credentials(id)
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
            // Table for Profile
            $sql = "
                    CREATE TABLE IF NOT EXISTS profiles (
                        id INT(6) PRIMARY KEY NOT NULL,
                        gender VARCHAR(10) NOT NULL,
                        age INT(3) NOT NULL,
                        sexpref VARCHAR(15) NOT NULL,
                        bio VARCHAR(255) NOT NULL,
                        hlocation VARCHAR(255) DEFAULT 'unknown',
                        location VARCHAR(50),
                        status VARCHAR(20) NOT NULL DEFAULT 'offline',
                        conTime DATETIME,
                        rating INT NOT NULL DEFAULT 0,
                        UNIQUE(id),
                        FOREIGN KEY (id) REFERENCES credentials(id)
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
            // Table for visitors
            $sql = "
                    CREATE TABLE IF NOT EXISTS visitors
                    (
                        visit_id INT(6) PRIMARY KEY AUTO_INCREMENT NOT NULL,
                        id INT(6),
                        visitor VARCHAR(50) NOT NULL,
                        visit_date DATETIME NOT NULL,
                        FOREIGN KEY (id) REFERENCES credentials(id)
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
             // Table for reporting
            $sql = "
                    CREATE TABLE IF NOT EXISTS reports
                    (
                        report_id INT(6) PRIMARY KEY AUTO_INCREMENT NOT NULL,
                        id INT(6),
                        reporter VARCHAR(50) NOT NULL,
                        report_date DATETIME NOT NULL,
                        FOREIGN KEY (id) REFERENCES credentials(id)
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
            // Table for user blocking
            $sql = "
                    CREATE TABLE IF NOT EXISTS blocks
                    (
                        block_id INT(6) PRIMARY KEY AUTO_INCREMENT NOT NULL,
                        id INT(6),
                        blocker VARCHAR(50) NOT NULL,
                        block_date DATETIME NOT NULL,
                        FOREIGN KEY (id) REFERENCES credentials(id)
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
            // Table for user notifications
            $sql = "
                    CREATE TABLE IF NOT EXISTS notifications
                    (
                        not_id INT(6) PRIMARY KEY AUTO_INCREMENT NOT NULL,
                        id INT(6),
                        innerHtml TEXT NOT NULL,
                        not_date DATETIME NOT NULL,
                        FOREIGN KEY (id) REFERENCES credentials(id)
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
            // Table for likes
            $sql = "
                    CREATE TABLE IF NOT EXISTS likes
                    (
                        like_id INT(6) PRIMARY KEY AUTO_INCREMENT NOT NULL,
                        id INT(6),
                        liker VARCHAR(50) NOT NULL,
                        like_date DATETIME NOT NULL,
                        FOREIGN KEY (id) REFERENCES credentials(id)
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
            // Table for photos
            $sql = "
                    CREATE TABLE IF NOT EXISTS photos (
                        photo_id INT(6) PRIMARY KEY AUTO_INCREMENT NOT NULL,
                        id INT(6) NOT NULL,
                        photo LONGBLOB NOT NULL,
                        FOREIGN KEY (id) REFERENCES profiles(id)
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
            // Table for tags
            $sql = "
                    CREATE TABLE IF NOT EXISTS tags (
                        tag_id INT(6) PRIMARY KEY AUTO_INCREMENT,
                        name VARCHAR(255) NOT NULL,
                        UNIQUE(name)
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
            // Table for tagmap
            $sql = "
                    CREATE TABLE IF NOT EXISTS tagmap (
                        tagmap_id INT(6) PRIMARY KEY AUTO_INCREMENT NOT NULL,
                        id INT(6) NOT NULL,
                        tag_id INT(6) NOT NULL,
                        FOREIGN KEY (id) REFERENCES profiles(id),
                        FOREIGN KEY (tag_id) REFERENCES tags(tag_id)
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
            // Table for messaging
            $sql = "
                    CREATE TABLE IF NOT EXISTS messages
                    (
                        msg_id INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
                        msg VARCHAR(255) NOT NULL,
                        receiver VARCHAR(50) NOT NULL,
                        sender VARCHAR(50) NOT NULL,
                        datetime DATETIME NOT NULL
                    )ENGINE=InnoDB;
                ";
            $pdo->exec($sql);
            
        } catch (PDOException $error) {
            $this->logger->error("Failed to connect to Database: " . $error->getMessage());
            return null;
        }
        
        $this->logger->info("Connected to Database.");
        return $pdo;
    }
    
    /**
     * Register on visitors, likes, reporting, and blocking
     * 
     * @parma   $subject_id         id on which an action is performed
     * @param   $accuser_uname      username of visitor who request
     * @param   $table              table on which to insert
     * @param   $col1 and $col2 specifies the columns name
     */
    public function setVisitor($table, $col1, $col2, $subject_id, $accuser_uname) {
        $sql = "
                INSERT INTO ". $table ." (id, ". $col1 .", ". $col2 .")
                VALUES (:s, :a, NOW())
            ;";
        
        $pdo = $this->getConn();
        if ( $pdo != null) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':s', $subject_id);
                $stmt->bindValue(':a', $accuser_uname);
                $stmt->execute();
                $stmt = null;
            }
            catch(PDOException $e) 
            {
                $this->logger->error("DBMan->setVisitors(): " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->error("DBMan->setVisitor(): pdo is null.");
            return false;
        }
        return true;
    }
    
    public function existsVisitor($table, $col, $sid, $uname) {
        $sql = "
                SELECT *
                FROM ". $table ."
                WHERE id = :id AND ". $col ." = :uname
            ;";
        
        $pdo = $this->getConn();
        if ( $pdo != null) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $sid);
                $stmt->bindValue(':uname', $uname);
                $stmt->execute();
                if ($stmt->fetch()) {
                    return true;
                }
                $stmt = null;
            }
            catch(PDOException $e) 
            {
                $this->logger->error("DBMan->setVisitors(): " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->error("DBMan->setVisitor(): pdo is null.");
            return false;
        }
        return false;
    }
    
    public function countVisitor($table, $sid) {
        $sql = "
                SELECT COUNT(*) AS count
                FROM ". $table ."
                WHERE id = :id
            ;";
        
        $pdo = $this->getConn();
        if ( $pdo != null) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $sid);
                $stmt->execute();
                $res = $stmt->fetch();
                $stmt = null;
                return $res['count'];
            }
            catch(PDOException $e) 
            {
                $this->logger->error("DBMan->countVisitors(): " . $e->getMessage());
                return null;
            }
        } else {
            $this->logger->error("DBMan->countVisitor(): pdo is null.");
            return null;
        }
        return null;
    }
    
    // $sid is subjeced user and $uname is subjector
    public function unsetVisitor($table, $col, $sid, $uname) {
        $sql = "
                DELETE FROM ". $table ."
                WHERE id = :id
                    AND ". $col ." = :uname
            ;";
        
        $pdo = $this->getConn();
        if ( $pdo != null) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $sid);
                $stmt->bindValue(':uname', $uname);
                $stmt->execute();
                $stmt = null;
            }
            catch(PDOException $e) 
            {
                $this->logger->error("DBMan->setVisitors(): " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->error("DBMan->setVisitor(): pdo is null.");
            return false;
        }
        return true;
    }
    
    /**
     * Create a notification
     * 
     * @param   $id     id of user
     * @param   $ih     innerHtml message
     */
    public function addToNotifications($id, $ih) {
        $sql = "
                INSERT INTO notifications (id, innerHtml, not_date)
                VALUES (:id, :ih, NOW())
            ";
        
        $pdo = $this->getConn();
        if ( $pdo != null) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':ih', $ih, PDO::PARAM_STR);
                $stmt->execute();
                $stmt = null;
            }
            catch(PDOException $e) 
            {
                $this->logger->error("DBMan->addToNotifications(): " . $e->getMessage());
                return null;
            }
        } else {
            $this->logger->error("DBMan->addToNotifications(): pdo is null.");
            return null;
        }
        return true;
    }
    
    
    // HANDLER: Checking Credentials ===========================================
    public function checkCredentials($uname, $pass) {
        $sql = "
            SELECT pass
            FROM credentials
            WHERE uname = :u
        ";
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':u', $uname);
            $stmt->execute();
            $result = $stmt->fetch();
            $stmt = null;
            if ($result != null) {
                if (password_verify($pass, $result['pass'])) {
                    return ['stat' => true, 'cause' => "Credentials Matches."];
                }
            }
        } else {
            $this->logger->error("DBMan: pdo is null.");
            return ['stat' => false, 'cause' => "An error occured."];
        }
        return ['stat' => false, 'cause' => "Password or Username are incorect."];
    }
    // =========================================================================
    
    /**
     * Add new user to database
     * 
     * @param   $email  - email
     * @param   $uname  - username
     * @param   $lname  - last name
     * @param   $fname  - first name
     * @param   $pass   - password
     * 
     * @return  array   - 'state' = 'ok' when successed 
     */
    public function addNewUser($email, $uname, $lname, $fname, $pass) {
        $sql = "
            INSERT INTO credentials (email, uname, lname, fname, pass) 
            VALUES (:e, :u, :l, :f, :p);
        ";
        
        if ( ! $this->checkUniqueField('email', $email)) {
            $this->logger->info("DBMan: User email suplied is already in use.");
            return ['state' => 'error', 'cause' => 'Email already in use.'];
        }
        if ( ! $this->checkUniqueField('uname', $uname)) {
            $this->logger->info("DBMan: User username suplied is already in use.");
            return ['state' => 'error', 'cause' => 'Username already in use.'];
        }
        
        $pdo = $this->getConn();
        if ( $pdo != null) {
            // Preparing...
            $stmt = $pdo->prepare($sql);
            // Filling the parameters...
            $stmt->bindParam(':e', $email);
            $stmt->bindParam(':u', $uname);
            $stmt->bindParam(':l', $lname);
            $stmt->bindParam(':f', $fname);
            $stmt->bindParam(':p', $pass);
            // Executing...
            $stmt->execute();
            // erarse stmt.
            $stmt = null;
        } else {
            $this->logger->error("DBMan: pdo is null.");
            return ['stat' => 'error', 'cause' => 'Temporary error.'];
        }
        return ['state' => 'ok'];
    }
    
    /**
     * Add new profile to database
     * 
     * @param   $new_profile    data for new profile table
     */
    public function addNewProfile($new_profile) {
        $sql = "
                INSERT INTO profiles (id, gender, sexpref, bio, hlocation, location, age)
                VALUES (:id, :gender, :sexpref, :bio, :hloc, :loc, :age)
                ON DUPLICATE KEY UPDATE gender = :gender, sexpref = :sexpref, bio = :bio, age = :age;
            ";
        
        $pdo = $this->getConn();
        if ( $pdo != null) {
            try {
                // Preparing...
                $stmt = $pdo->prepare($sql);
                // Filling the parameters...
                $stmt->bindParam(':id', $new_profile['id']);
                $stmt->bindParam(':gender', $new_profile['gender']);
                $stmt->bindValue(':age', $new_profile['age']);
                $stmt->bindParam(':sexpref', $new_profile['sexpref']);
                $stmt->bindParam(':bio', $new_profile['bio']);
                $stmt->bindValue(':hloc', $new_profile['hlocation']);
                $stmt->bindValue(':loc', $new_profile['location']);
                // Executing...
                $stmt->execute();
            }
            catch(PDOException $e) 
            {
                $this->logger->error("DBMan->addNewProfile(): " . $e->getMessage());
                return ['state' => 'failure', 'cause' => 'You already have an account.'];
            }
            
            $stmt = null;
        } else {
            $this->logger->error("DBMan->addNewProfile(): pdo is null.");
            return ['state' => 'failure', 'cause' => 'Temporary error.'];
        }
        
        $this->addNewTags($new_profile['tags']);
        $this->linkTags($new_profile['id'], $new_profile['tags']);
        
        $this->logger->info("DBMan->addNewProfile(): Created a new profile.");
        return ['state' => 'ok', 'cause' => 'Successful created profile!'];
    }
    
    /**
     * Link tags to id of user
     * 
     * @param   $id     int
     * @parma   $tags   array strings
     */
    private function linkTags($id, $tags) {
        $sql = "
                INSERT INTO tagmap (id, tag_id)
                VALUES (:uid, (
                                SELECT tag_id
                                FROM tags
                                WHERE name = :name
                            )
                        )
            ";
            
        $pdo = $this->getConn();
        if ( $pdo != null) {
            // Preparing...
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':uid', $id);
            $stmt->bindParam(':name', $name);
            
            foreach ($tags as $tag) {
                $name = $tag;
                
                $stmt->execute();
            }
            // erarse stmt.
            $stmt = null;
        } else {
            $this->logger->error("DBMan->linkTags(): pdo is null.");
        }
    }
    
    public function getUserLinkedTags($id) {
        $sql = "
                SELECT name
                FROM tags
                WHERE tag_id IN (
                        SELECT tag_id
                        FROM tagmap
                        WHERE id = :id
                    );
            ";
        $result = array();
            
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $name) {
                array_push($result, $name['name']);
            }
            $stmt = null;
        } else {
            $this->logger->error("DBMan->getUserLinkedTags(): PDO is null.");
            return null;
        }
        return $result;
    }
    
    /**
     * Add new photo
     * 
     * @param   $id     user id
     * @param   $photos   array
     * 
     * @return  bool true on success
     */
    function addNewPhoto($id, $photo) {
        $sql = "
                INSERT INTO photos (id, photo)
                VALUES (:id, :photo)
            ";
            
        $pdo = $this->getConn();
        if ( $pdo != null) {
            try {
                // Preparing...
                $stmt = $pdo->prepare($sql);
                
                $stmt->bindValue(':id', $id);
                $stmt->bindValue(':photo', $photo);
                
                $stmt->execute();
                
            }
            catch(PDOException $e) 
            {
                $this->logger->error("DBMan->addNewPhoto(): " . $e->getMessage());
                return false;
            }
            
            $stmt = null;
        } else {
            $this->logger->error("DBMan->addNewPhoto(): pdo is null.");
            return false;
        }
        $this->logger->info("DBMan->addNewPhoto(): Uploaded new photo.");
        return true;
    }
    
    /**
     * Get user photos from databse
     * 
     * @param   $id     user id
     * @param   $count  how many to retrieve
     * 
     * @return  array   photos
     */
    public function getPhotos($id, $count) {
        $sql = "
                SELECT photo
                FROM photos
                WHERE id = :id
            ";
            
        $pdo = $this->getConn();
        if ( $pdo != null) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                
                $result = $stmt->fetchAll();
                
                $stmt = null;
            }
            catch(PDOException $e) 
            {
                $this->logger->error("DBMan->getPhotos(): " . $e->getMessage());
                return null;
            }
        } else {
            $this->logger->error("DBMan->getPhotos(): pdo is null.");
            return null;
        }
        return $result;
    }
    
    /**
     * Checks how many phot a user have
     * 
     * @param   $id id of user
     */
    function countPhotos($id) {
        $sql = "
                SELECT COUNT(*)
                FROM photos
                WHERE id = :id
            ";
            
        $pdo = $this->getConn();
        if ( $pdo != null) {
            // Preparing...
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id);
            
            $stmt->execute();
            $result = $stmt->fetch();
            // erarse stmt.
            $stmt = null;
        } else {
            $this->logger->error("DBMan->countPhotos(): pdo is null.");
        }
        return $result;
    }
    
    /**
     * Add new unique tags
     * 
     * @param   $tags   array of strings
     */
    function addNewTags($tags) {
        $sql = "
                INSERT IGNORE INTO tags (name)
                VALUES (:name)
            ;";
            
        $pdo = $this->getConn();
        if ( $pdo != null) {
            // Preparing...
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":name", $name);
            
            foreach ($tags as $t) {
                $name = $t;
                $stmt->execute();
            }
            // erarse stmt.
            $stmt = null;
        } else {
            $this->logger->error("DBMan->addNewTags(): pdo is null.");
        }
    }

    
    /**
     * Checks if a value is not in database
     * 
     * @param   $col    DB column name
     * @param   $val    value to be checked
     * @param   $table  string
     * 
     * @return  bool    true if it is NOT, else false.
     */
    protected function checkUniqueField($col, $email, $table = 'credentials') {
        $sql = "
            SELECT COUNT($col)
            FROM ". $table ."
            WHERE ".$col." = :val
        ;";
            
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            // Preparing...
            $stmt = $pdo->prepare($sql);
            // Bind parameters...
            $stmt->bindParam(':val', $email);
            // Executing...
            $stmt->execute();
            // Fetching...
            $result = $stmt->fetch();
            // erarse stmt.
            $stmt = null;
        } else {
            $this->logger->error("DBMan: pdo is null.");
            return false;
        }
        return reset($result) == 0 ? true : false;
    }
    
    /**
     * Checks if a value matches a colmn in db
     * 
     * @param   $col    DB column name
     * @param   $val    value to be checked
     * @parma   $table  string
     * 
     * @return  bool    true if exists, else false.
     */
    public function exists($col, $val, $table = 'credentials') {
        return $this->checkUniqueField($col, $val, $table) ? false : true;
    }
    
    /**
     * Updates a value of a column in table
     * 
     * @param   $table      - table to use
     * @param   $user_id    - id of the user to update
     * @parma   $col        - Column to update
     * @param   $new_val    - New value to insert
     */
    public function update($table, $user_id, $col, $new_val) {
        $sql = '
            UPDATE '. $table .'
            SET '. $col .' = :new
            WHERE id = :id
        ;';
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":new", $new_val);
                $stmt->bindParam(":id", $user_id);
                $stmt->execute();
                $stmt = null;
            }
            catch(PDOException $e) 
            {
                $this->logger->error("DBMan->update(): " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->error("DBMan: PDO is null.");
            return false;
        }
        return true;
    }
    
    /**
     * Gets entire tabe
     * 
     * @param   $table  table name
     */
    function getTable($table) {
        $sql = "
            SELECT *
            FROM ". $table ."
        ;";
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $stmt = null;
        } else {
            $this->logger->error("DBMan->getTable(): PDO is null.");
            return null;
        }
        return $result;
    }
    
    /**
     * Get a value of a column in a specified table
     * 
     * @param   $table   - table to use
     * @param   $col    - column to select
     * @param   $c_col  - column for condition
     * @param   $c_val  - value that matched
     * 
     * @return  array() - all col values that matched
     */
    function get($col, $table, $c_col, $c_val) {
        $sql = "
            SELECT ". $col ."
            FROM ". $table ."
            WHERE ". $c_col ." = :v
        ;";
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":v", $c_val);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $stmt = null;
        } else {
            $this->logger->error("DBMan: PDO is null.");
            return null;
        }
        return $result;
    }
    
    /**
     * Gets all columns from table where condition is true
     * 
     * @param   $table   string table
     * @param   $where  string column
     * @param   $value  string value
     * @param   $orderBy
     * 
     * @return  array
     */
    public function getAllFrom($table, $where, $value, $orderBy = "") {
        $sql = "
                SELECT *
                FROM ". $table ."
                WHERE ". $where ." = :value
            " . $orderBy;
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":value", $value);
            $stmt->execute();
            $result = $stmt->fetchAll();
            $stmt = null;
        } else {
            $this->logger->error("DBMan: PDO is null.");
            return null;
        }
        return $result;
    }
    
    /**
     * Add a new record to 'reset_pass' table
     * 
     * @param   $code       - new code for user password reset.
     * @param   $email      - email of user who requested
     * 
     * @return  bool        - true on success.
     */
    public function addToResetPass($code, $email) {
        $sql = "
            INSERT INTO reset_pass (id, code)
            VALUES (
                (
                    SELECT id 
                    FROM credentials 
                    WHERE email = :email 
                    LIMIT 1
                ), 
                :code);
        ";
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(":email", $email);
            $stmt->bindParam(":code", $code);
            $stmt->execute();
            $stmt = null;
        } else {
            $this->logger->error("DBMan: PDO is null.");
            return false;
        }
        return true;
    }
    
    /**
     * Remove a row from 'reset_pass' table
     * 
     * @param   $code       - new code for user password reset.
     * 
     * @return  bool        - true on success.
     */
    public function removeFromResetPass($code) {
        $sql = "
            DELETE FROM reset_pass
            WHERE code = :code
        ";
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":code", $code);
            $stmt->execute();
            $stmt = null;
        } else {
            $this->logger->error("DBMan: PDO is null.");
            return false;
        }
        return true;
    }
    
    
    /**
     * Fetch users that have in common at least one tag
     */
    public function getUserRelatedBy($tags) {
        $sql = "
                SELECT *
                FROM profiles
                WHERE 
            ";
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            try {
                $stmt = $pdo->prepare($sql);
                //
                $stmt->execute();
                $result = $stmt->fetchAll();
                $stmt = null;
            }
            catch(PDOException $e)
            {
                $this->logger->error("DBMan->getUserRelatedByTag(): " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->error("DBMan->getUserRelatedByTag(): PDO is null.");
            return false;
        }
        return true;
    }
    
    /**
     * Calculate fame rating based on visitors, likes, blocks and reports
     * 
     * @param   $db     database
     * @param   $sid     id of user to be calculated on.
     */
    public function getFameRating($sid)
    {
        $visits = (int)$this->countVisitor('visitors', $sid);
        $likes = (int)$this->countVisitor('likes', $sid);
        $blocks = (int)$this->countVisitor('blocks', $sid);
        $reports = (int)$this->countVisitor('reports', $sid);
        
        $total_points = $visits + (2 * $visits);
        $user_points = $visits + (2 * $likes) - $block - $reports;
        
        $percents = ($user_points * 100) / $total_points;
        
        // Convert to base 10 percentage
        $fr = ($percents * 10) / 100;
        
        return number_format($fr, 1, '.', '');
    }
    
    /**
     * Fetch user data in relation with user that requests
     * 
     * @param   $count  how many to fetch
     */
    public function getUsersData($count, $uid, $uname) {
        $sql = "
                SELECT DISTINCT
                    credentials.id, credentials.uname, photos.photo, profiles.age
                FROM
                    credentials
                INNER JOIN
                    profiles ON credentials.id = profiles.id AND credentials.id <> :id AND credentials.id NOT IN (SELECT id FROM blocks WHERE blocker = :uname)
                INNER JOIN
                    photos ON profiles.id = photos.id
                LIMIT :limit
            ;";
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $uid);
                $stmt->bindValue(':uname', $uname);
                $stmt->bindValue(':limit', $count, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll();
                $stmt = null;
            }
            catch(PDOException $e)
            {
                $this->logger->error("DBMan->getUsersData(): " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->error("DBMan->getUsersData(): PDO is null.");
            return false;
        }
        return $result;
    }
    
    /**
     * Search and Fetch users that liked each other
     * 
     * @param   $uid     id of user
     */
    public function getUserConnections($uid, $uname) {
        $sql = "
                SELECT liker 
                FROM likes 
                WHERE id = :id 
                AND liker IN (SELECT credentials.uname FROM credentials INNER JOIN likes WHERE credentials.id = likes.id)
            ;";
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $uid);
                // $stmt->bindValue(':uname', $uname);
                $stmt->execute();
                $result = $stmt->fetchAll();
                $stmt = null;
            }
            catch(PDOException $e)
            {
                $this->logger->error("DBMan->getUserConnections(): " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->error("DBMan->getUserConnections(): PDO is null.");
            return false;
        }
        return $result;
    }
    
    public function addMsg($sender, $receiver, $msg, $datetime) {
        $sql = "
                INSERT INTO messages(receiver, msg, sender, datetime)
                VALUES (:receiver, :msg, :sender, :datetime);
            ";
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':receiver', $receiver);
                $stmt->bindValue(':msg', $msg);
                $stmt->bindValue(':sender', $sender);
                $stmt->bindValue(':datetime', $datetime);
                $stmt->execute();
                $stmt = null;
            }
            catch(PDOException $e)
            {
                $this->logger->error("DBMan->getUserRelatedByTag(): " . $e->getMessage());
                return false;
            }
        } else {
            $this->logger->error("DBMan->getUserRelatedByTag(): PDO is null.");
            return false;
        }
        return true;
    }
    
    public function getConversation($sender, $receiver, $from_msg_id = -1) {
        $sql = "
                SELECT msg_id, msg, datetime, sender
                FROM messages
                WHERE sender IN (:sender, :receiver) AND receiver IN (:sender, :receiver) AND msg_id > :id
            ;";
        
        $pdo = $this->getConn();
        if ( $pdo != null ) {
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':receiver', $receiver);
                $stmt->bindValue(':sender', $sender);
                $stmt->bindValue(':id', $from_msg_id, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetchAll();
                $stmt = null;
            }
            catch(PDOException $e)
            {
                $this->logger->error("DBMan->getConverstion(): " . $e->getMessage());
                return null;
            }
        } else {
            $this->logger->error("DBMan->getConverstion(): PDO is null.");
            return null;
        }
        return $result;
    }
    
    // Setters and Getters =====================================================
    public function setConn($new_conn) {
        $this->conn = $new_conn;
    }
    
    public function getConn() {
        return $this->conn;
    }
    // =========================================================================
}
?>