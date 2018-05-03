<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
        <link rel="stylesheet" href="templates/css/stylesheet.css" >
        <style>
        
            h1 {
                text-align: center;
            }
        </style>
    </head>
    <body>
        <!--include header content-->
        <?PHP include 'inc/header.php';?>
        
        
        <fieldset>
            <div class="pure-controls">
                <legend>Settings</legend>
            </div>
            
            <form class="pure-form pure-form-aligned" onsubmit="updateProfile(event)" method="POST">
                <fieldset>
                    <div class="pure-control-group">
                        <label for="email">Email:</label>
                        <input id="email" type="email" name="email" placeholder="new email" value="<?= $data['email'] ?>"/>
                    </div>
                    
                    <div class="pure-control-group">
                        <label for="lname">Last Name:</label>
                        <input id="lname" type="text" name="lname" placeholder="new last name" value="<?= $data['lname'] ?>"/>
                    </div>
                    
                    <div class="pure-control-group">
                        <label for="fname">First Name:</label>
                        <input id="fname" type="text" name="fname" placeholder="new first name" value="<?= $data['fname'] ?>"/>
                    </div>
                    
                    <div class="pure-control-group">
                        <label for="pass">Password</label>
                        <input id="pass" type="password" name="pass" placeholder="new password"/>
                        <span class="pure-form-message-inline">not required.</span>
                    </div>
                    
                    <!--Select gender-->
                    <div class="pure-control-group">
                        <label for="gender">Gender</label>
                        <select id="gender">
                            <option <?= (($data['gender'] == 'Male') ? 'selected' : null) ?>>Male</option>
                            <option <?= (($data['gender'] == 'Female') ? 'selected' : null) ?>>Female</option>
                        </select>
                    </div>
                    
                    <!--Select age-->
                    <div class="pure-control-group">
                        <label for="age">Age</label>
                        <select id="age">
                            <?PHP
                                for ($i = 1; $i < 100; $i++) {
                                    echo "<option " . (($data['age'] == $i) ? 'selected' : null) . ">$i</option>";
                                }
                            ?>
                        </select>
                    </div>
                    
                    <!--Select sexual preferences-->
                    <div class="pure-control-group">
                        <label for="sexpref">Sexual Preferences</label>
                        <select id="sexpref">
                            <option <?= (($data['sexpref'] == 'Heterosexual') ? 'selected' : null) ?>>Heterosexual</option>
                            <option <?= (($data['sexpref'] == 'Asexual') ? 'selected' : null) ?>>Asexual</option>
                            <option <?= (($data['sexpref'] == 'Bisexual') ? 'selected' : null) ?>>Bisexual</option>
                            <option <?= (($data['sexpref'] == 'Homosexual') ? 'selected' : null) ?>>Homosexual</option>
                        </select>
                    </div>
                    
                    <!--Biography-->
                    <div class="pure-control-group">
                        <label for="biography">Biography</label>
                        <textarea id="bio" name="biography" class="pure-form-message-inline" placeholder="your biography"><?= $data['bio'] ?></textarea>
                    </div>
                    
                    <!--intersets-->
                    <div class="pure-control-group">
                        <label for="interests">Interests</label>
                        <input id="tags" type="text" name="interests" placeholder="ex: 'cars, pets'" value="<?= $data['tags'] ?>"/>
                        <span class="pure-form-message-inline">separated by ','</span>
                    </div>
                    
                    <div class="pure-controls">
                        <button id="fb" type="submit" class="pure-button pure-button-primary">Update</button>
                        <span id="fm" class="pure-form-message-inline" style="visibility: hidden;">Error: </span>
                    </div>
                </fieldset>
            </form>
                
            <form enctype="multipart/form-data" class="pure-form pure-form-aligned" method="POST" onsubmit="uploadPhoto(); return false;">
                <fieldset>
                    <div class="pure-controls">
                        <legend>Upload Photos</legend>
                    </div>
                    
                    <div class="pure-control-group">
                        <label>
                            <img id="preview" width=150 height=100 style="object-fit: contain;" />
                        </label>
                        <input id="photo" type="file" name="photo" />
                        <button id="pconfirm" type="submit" class="pure-button pure-button-primary">Upload</button>
                        <span id="sm" class="pure-form-message-inline" style="visibility: hidden;">Cool!</span>
                    </div>
                </fieldset>
            </form>
        
        </fieldset>
        
        <script>
        
            function updateProfile(evt) {
                evt.preventDefault();
                
                var message = document.getElementById('fm');
                var confirm = document.getElementById('fb');
                var ajax = new XMLHttpRequest();
                
                
                confirm.innerHTML = "processing...";
                message.style.visibility = 'hidden';
                ajax.onreadystatechange = function () {
                    if (this.readyState == 4) {
                        if (this.status == 200) {
                            // console.log(this.responseText);
                            var json = JSON.parse(this.responseText);
                            
                            message.style.visibility = 'visible';
                            message.innerHTML = json.cause;
                            if (json.state == 'ok') {
                                message.style.color = "#691";
                            } else {
                                message.style.color = "#af1515";
                            }
                        }
                        confirm.innerHTML = "Upload";
                    }
                };
                ajax.open("POST", "/settings/update", true);
                ajax.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                ajax.setRequestHeader('Content-type', 'application/json; charset=utf-8');
                ajax.send(constructData());
            }
            
            function constructData() {
                var email = document.getElementById('email').value;
                var lname = document.getElementById('lname').value;
                var fname = document.getElementById('fname').value;
                var pass = document.getElementById('pass').value;
                var gender = document.getElementById('gender').value;
                var sexpref = document.getElementById('sexpref').value;
                var bio = document.getElementById('bio').value;
                var tags = document.getElementById('tags').value;
                
                var data = {
                    "email":    email,
                    "lname":    lname,
                    "fname":    fname,
                    "gender":   gender,
                    "sexpref":  sexpref,
                    "bio":      bio,
                    "tags":     tags,
                    "age":      document.getElementById('age').value
                };
                
                if (pass) {
                    data.pass = pass;
                }
                
                return JSON.stringify(data);
            }
            
            function uploadPhoto() {
                var message = document.getElementById('sm');
                var confirm = document.getElementById('pconfirm');
                var photo = document.getElementById('photo');
                var preview = document.getElementById('preview');
                var ajax = new XMLHttpRequest();
                var data = new FormData();
                
                
                data.append('photo', photo.files[0]);
                
                confirm.innerHTML = "processing...";
                message.style.visibility = 'hidden';
                ajax.onreadystatechange = function () {
                    if (this.readyState == 4) {
                        if (this.status == 200) {
                            var json = JSON.parse(this.responseText);
                            
                            message.style.visibility = 'visible';
                            message.innerHTML = json.cause;
                            if (json.state == 'ok') {
                                message.style.color = "#691";
                                preview.src = json.photo;
                            } else {
                                message.style.color = "#af1515";
                            }
                        }
                        confirm.innerHTML = "Upload";
                    }
                };
                ajax.open("POST", "/upload/photo", true);
                ajax.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                ajax.send(data);
            }
        </script>
        
        <!--inlcude footer content-->
        <?PHP include 'inc/footer.php';?>
    </body>
</html>