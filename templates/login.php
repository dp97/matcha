<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Log In</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
        <link rel="stylesheet" href="templates/css/stylesheet.css" >
    </head>
    <body>
        <!--include header content-->
        <?PHP include 'inc/header.php';?>
        <form class="pure-form pure-form-aligned" action="/login" method="POST">
            <fieldset>
                <div class="pure-controls">
                    <legend>Log In</legend>
                </div>
                
                <div class="pure-control-group">
                    <label for="username">Username:</label>
                    <input autofocus required id="username" type="text" name="username" placeholder="username"/>
                    <span class="pure-form-message-inline">This is a required field.</span>
                </div>
                
                <div class="pure-control-group">
                    <label for="pass">Password</label>
                    <input required id="pass" type="password" name="pass" placeholder="password"/>
                    <span class="pure-form-message-inline">This is a required field.</span>
                </div>
                
                <div class="pure-controls">
                    <button type="submit" class="pure-button pure-button-primary">Log In</button>
                    <a class="pure-form-message-inline" href="/reset_password">Reset Password</a>
                    <?PHP
                        if (isset($error)) {
                            echo '<span class="pure-form-message-inline">Error: '. $error .'</span>';
                        }
                    ?>
                </div>
            </fieldset>
        </form>
        
        <!--inlcude footer content-->
        <?PHP include 'inc/footer.php';?>
    </body>
</html>