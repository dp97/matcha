<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Search</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
        <link rel="stylesheet" href="templates/css/stylesheet.css" >
    </head>
    <body>
        <!--include header content-->
        <?PHP include 'inc/header.php';?>
        
        <h1>Search For Perfect Match</h1>
        
        <div>
            <div>
                <label for="from-age">Age From</label>
                <select id="from-age">
                    <?PHP
                        for ($i = 1; $i < 100; $i++) {
                            echo "<option>$i</option>";
                        }
                    ?>
                </select>
                <label for="to-age">To</label>
                <select id="to-age">
                    <?PHP
                        for ($i = 1; $i < 100; $i++) {
                            echo "<option ". (($i == 99) ? 'selected' : null) .">$i</option>";
                        }
                    ?>
                </select>
            </div>
            
            <div>
                <label for="from-fr">Fame From</label>
                <select id="from-fr">
                    <?PHP
                        for ($i = 1; $i < 10; $i++) {
                            echo "<option>$i</option>";
                        }
                    ?>
                </select>
                <label for="to-fr">To</label>
                <select id="to-fr">
                    <?PHP
                        for ($i = 1; $i < 10; $i++) {
                            echo "<option ". (($i == 99) ? 'selected' : null) .">$i</option>";
                        }
                    ?>
                </select>
            </div>
            
            <div class="tag-field">
                <?PHP
                    foreach ($atags as $tag) {
                        $name = $tag['name'];
                        echo "<div id='tag'>$name</div>";
                    }
                ?>
            </div>
        </div>
            
        <div class="pure-g">
            
            <?PHP
            foreach ($profiles as $prof)
            {
                echo "
                    <div class='pure-u-1-5 profile-container' onclick='visitUser(".$prof['id'].")'>
                        <div class='pure-u-1'>
                            <img class='pure-img' src='".$prof['photo']."'>
                        </div>
                        <p class='profile-container-p'><b>".$prof['uname']."</b></p>
                    </div>
                    ";
            }
            ?>
            
        </div>
        
        <script>
            function visitUser(id) {
                window.location.replace('/user/' + id);
            }
        </script>
        
        <!--inlcude footer content-->
        <?PHP include 'inc/footer.php';?>
    </body>
</html>