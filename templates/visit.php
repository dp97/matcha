<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Visit User</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
        <link rel="stylesheet" href="/templates/css/stylesheet.css" >
    </head>
    <body>
        <!--include header content-->
        <?PHP include 'inc/header.php';?>
        
        <div class="pure-g">
            
            <div class="pure-u-1">
                <div class="pure-u-1">
                    <div class="pure-g">
                        <div class="pure-u-1-2">
                            <h1>Profile of <?= $profile_data['uname'] ?></h1>
                        </div>
                        <div class="pure-u-1-2" style="align-self: center;">
                            <div class="pure-u-1">
                                <span style="font-size: 12px;text-align: center;color: green;"><?= $profile_data['likeTxt'] ?> | <?= $profile_data['status'] ?> </span>
                            </div>
                            <div class="pure-u-1">
                                <button id="like" onclick="addRemoveLike(<?= $profile_data['id'] ?>)" class="pure-button pure-button-primary"><?= $profile_data['likeBtn'] ?></button>
                                <button id="block" onclick="addRemoveBlock(<?= $profile_data['id'] ?>)" class="pure-button button-block"><?= (($profile_data['blockBtn']) ? 'Unblock' : 'Block') ?></button>
                                <button id="report" onclick="addRemoveReport(<?= $profile_data['id'] ?>)" class="pure-button"><?= (($profile_data['reportBtn']) ? 'Unreport' : 'Report fake') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="pure-u-1-5">            
                    <img class="pure-img" src="<?= $profile_data['profile_photo'] ?>">
                </div>
                
                <div class="pure-u-1-2">
                    <div class="pure-u-1">
                        <p><b>First Name:</b> <?= $profile_data['fname'] ?></p>
                    </div>
                    <div class="pure-u-1">
                        <p><b>Last Name:</b> <?= $profile_data['lname'] ?></p>
                    </div>
                </div>
                
                <div class="pure-g pure-u-1">
                    <div class="pure-u-11-24">
                        <p><b>Gender:</b> <?= $profile_data['gender'] ?></p>
                    </div>
                    <div class="pure-u-11-24">
                        <p><b>Age:</b> <?= $profile_data['age'] ?></p>
                    </div>
                </div>
                
                <div class="pure-u-1">
                    <p><b>Sexual Preferences:</b> <?= $profile_data['sexpref'] ?></p>
                </div>
                <div class="pure-u-1">
                    <p><b>Biography:</b> <?= $profile_data['bio'] ?></p>
                </div>
                
                <div class="pure-u-1">
                    <p><b>Interests:</b></p>
                    <?PHP
                        foreach ($profile_data['tags'] as $key => $tag) {
                            echo "<div id='tag'>$tag</div>";
                        }
                    ?>
                </div>
                
                <div class="pure-u-1 tags">
                    <p><b>Location:</b> <?= $profile_data['hlocation'] ?></p>
                    <img class='pure-img' src='http://staticmap.openstreetmap.de/staticmap.php?center=<?= $profile_data['location'] ?>&zoom=14&size=600x200&maptype=mapnik&markers=<?= $profile_data['location'] ?>' />
                </div>
            </div>
        </div>
        
        <script>
            function addRemoveLike(id) {
                var ajax = new XMLHttpRequest();
                var like = document.getElementById('like');
                
                like.innerHTML = "wait..";
                ajax.onreadystatechange = function () {
                    if (this.readyState == 4) {
                        if (this.status == 200) {
                            // console.log(this.responseText);
                            var json = JSON.parse(this.responseText);
                            
                            if (json.stat === 'ok') {
                                like.innerHTML = json.btntxt;
                            } else {
                                like.innerHTML = "Like";
                            }
                        }
                        else {
                            like.innerHTML = "Like";
                        }
                    }
                }
                ajax.open("POST", "/user/like/" + id, true);
                ajax.send();
            }
            
            function addRemoveBlock(id) {
                var ajax = new XMLHttpRequest();
                var like = document.getElementById('block');
                
                like.innerHTML = "wait..";
                ajax.onreadystatechange = function () {
                    if (this.readyState == 4) {
                        if (this.status == 200) {
                            // console.log(this.responseText);
                            var json = JSON.parse(this.responseText);
                            
                            if (json.stat === 'ok') {
                                like.innerHTML = json.btntxt;
                            } else {
                                like.innerHTML = "Block";
                            }
                        }
                        else {
                            like.innerHTML = "Block";
                        }
                    }
                }
                ajax.open("POST", "/user/block/" + id, true);
                ajax.send();
            }
            
            function addRemoveReport(id) {
                var ajax = new XMLHttpRequest();
                var like = document.getElementById('report');
                
                like.innerHTML = "wait..";
                ajax.onreadystatechange = function () {
                    if (this.readyState == 4) {
                        if (this.status == 200) {
                            // console.log(this.responseText);
                            var json = JSON.parse(this.responseText);
                            
                            if (json.stat === 'ok') {
                                like.innerHTML = json.btntxt;
                            } else {
                                like.innerHTML = "Report fake";
                            }
                        }
                        else {
                            like.innerHTML = "Report fake";
                        }
                    }
                }
                ajax.open("POST", "/user/report/" + id, true);
                ajax.send();
            }
        </script>
        
        <!--inlcude footer content-->
        <?PHP include 'inc/footer.php';?>
    </body>
</html>