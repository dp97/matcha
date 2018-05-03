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
            #notify {
                background: floralwhite;
                padding: 5px;
                box-sizing: border-box;
                height: 75vh;
                overflow-x: hidden; /* Hide horizontal scrollbar */
                overflow-y: scroll; /* Add vertical scrollbar */
            }
            aside {
                background: #1f8dd6;
                margin: 0.2em 0;
                padding: 0.3em 1em;
                border-radius: 3px;
                color: #fff;
            }
        </style>
    </head>
    <body>
        <!--include header content-->
        <?PHP include 'inc/header.php';?>
        
        <div class="pure-g">
            
            <div id="notify" class="pure-u-1-3">
                <div class="pure-u-1">
                    <header>
                        <h1>Notifications</h1>
                    </header>
                </div>
                <?PHP
                foreach ($profile_data['notifications'] as $notify) {
                    echo "
                        <div class='pure-u-1'>
                            <aside>
                                <p class='notify-msg'>". $notify['innerHtml'] ."</p>
                                <p class='notify-datetime'>on ". $notify['not_date'] ."</p>
                            </aside>
                        </div>
                        ";
                }
                ?>
                
            </div>
            
            <div class="pure-u-2-3">
                <div class="pure-u-1">
                     <h1>Welcome back, <?= $profile_data['uname'] ?>!</h1>
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
        
        <!--inlcude footer content-->
        <?PHP include 'inc/footer.php';?>
    </body>
</html>