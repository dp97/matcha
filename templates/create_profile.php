<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
        <link rel="stylesheet" href="templates/css/stylesheet.css" >
    </head>
    <body>
        <!--include header content-->
        <?PHP include 'inc/header.php';?>
        
        <form class="pure-form pure-form-aligned" method="POST" onsubmit="sendRequest(); return false;">
            <fieldset>
                <div class="pure-controls">
                    <legend>Create Your Profile</legend>
                </div>
                
                <!--Select gender-->
                <div class="pure-control-group">
                    <label for="gender">Gender</label>
                    <select id="gender">
                        <option>Male</option>
                        <option>Female</option>
                    </select>
                </div>
                
                <!--Select sexual preferences-->
                <div class="pure-control-group">
                    <label for="sexpref">Sexual Preferences</label>
                    <select id="sexpref">
                        <option>Heterosexual</option>
                        <option>Asexual</option>
                        <option>Bisexual</option>
                        <option>Homosexual</option>
                    </select>
                </div>
                
                <!--Select sexual preferences-->
                <div class="pure-control-group">
                    <label for="age">Age</label>
                    <select id="age">
                        <?PHP
                            for ($i = 1; $i < 100; $i++) {
                                echo "<option>$i</option>";
                            }
                        ?>
                    </select>
                </div>
                
                <!--Biography-->
                <div class="pure-control-group">
                    <label for="biography">Biography</label>
                    <textarea required id="bio" name="biography" class="pure-form-message-inline" placeholder="your biography"></textarea>
                </div>
                
                <!--intersets-->
                <div class="pure-control-group">
                    <label for="interests">Interests</label>
                    <input required id="tags" type="text" name="interests" placeholder="ex: 'cars, pets'"/>
                    <span class="pure-form-message-inline">separated by ','</span>
                </div>
                
                <!--submit-->
                <div class="pure-controls">
                    <button id="confirm" type="submit" class="pure-button pure-button-primary">Confirm</button>
                    <span id="message" class="pure-form-message-inline" style="visibility: hidden;">Cool!</span>
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
                    <span id="pmessage" class="pure-form-message-inline" style="visibility: hidden;">Cool!</span>
                </div>
                
            </fieldset>
        </form>
        
        <script>
            var geo = null;
            var humangeo = null;

            window.onload = function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(showPosition);
                }
            };
            
            function showPosition(position) {
                geo = position.coords.latitude + "," + position.coords.longitude;
            }
        
            function uploadPhoto() {
                var message = document.getElementById('pmessage');
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
        
            function sendRequest() {
                var ajax = new XMLHttpRequest();
                var confirm = document.getElementById('confirm');
                var message = document.getElementById('message');
                var gender = document.getElementById('gender');
                var sexpref = document.getElementById('sexpref');
                var bio = document.getElementById('bio');
                var tags = document.getElementById('tags');
                
                var data = JSON.stringify({
                    "gender":   gender.value,
                    "sexpref":  sexpref.value,
                    "bio":      bio.value,
                    "tags":     tags.value,
                    "hlocation": humangeo,
                    "location": geo,
                    "age":      document.getElementById('age').value
                });
                
                confirm.innerHTML = "processing...";
                message.style.visibility = 'hidden';
                ajax.onreadystatechange = function () {
                    if (this.readyState == 4) {
                        if (this.status == 200) {
                            console.log(this.responseText);
                            var json = JSON.parse(this.responseText);

                            message.style.visibility = 'visible';
                            message.innerHTML = json.cause;
                            if (json.state == 'ok') {
                                message.style.color = "#691";
                            } else {
                                message.style.color = "#af1515";
                            }
                        }
                        confirm.innerHTML = "Confirm";
                    }
                };
                ajax.open("POST", "/create_profile", true);
                ajax.setRequestHeader('Content-type', 'application/json; charset=utf-8');
                ajax.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                ajax.send(data);
            }
            
            function callback(data)
        	{
        	    geo = data.latitude + "," + data.longitude
        	    humangeo = data.city + ", " + data.country_name;
        	}
        </script>
        
        <script type="text/javascript" src="https://geoip-db.com/jsonp"></script>
        
        <!--inlcude footer content-->
        <?PHP include 'inc/footer.php';?>
    </body>
</html>