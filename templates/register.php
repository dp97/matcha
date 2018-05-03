<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
        <link rel="stylesheet" href="templates/css/stylesheet.css" >
    </head>
    <body>
        <!--include header content-->
        <?PHP include 'inc/header.php';?>
        
        <form class="pure-form pure-form-aligned" action="/register" method="POST">
            <fieldset>
                <div class="pure-controls">
                    <legend>Sign Up</legend>
                </div>
                
                <div class="pure-control-group">
                    <label for="email">Email:</label>
                    <input required id="email" type="email" name="email" placeholder="email"/>
                    <span class="pure-form-message-inline">This is a required field.</span>
                </div>
                
                <div class="pure-control-group">
                    <label for="username">Username:</label>
                    <input required id="username" type="text" name="username" placeholder="username"/>
                    <span class="pure-form-message-inline">This is a required field.</span>
                </div>
                
                <div class="pure-control-group">
                    <label for="lname">Last Name:</label>
                    <input required id="lname" type="text" name="lname" placeholder="last name"/>
                    <span class="pure-form-message-inline">This is a required field.</span>
                </div>
                
                <div class="pure-control-group">
                    <label for="fname">First Name:</label>
                    <input required id="fname" type="text" name="fname" placeholder="first name"/>
                    <span class="pure-form-message-inline">This is a required field.</span>
                </div>
                
                <div class="pure-control-group">
                    <label for="pass">Password</label>
                    <input required id="pass" type="password" name="pass" placeholder="password"/>
                    <span class="pure-form-message-inline">This is a required field.</span>
                </div>
                
                <div class="pure-controls">
                    <button type="submit" class="pure-button pure-button-primary">Sign Up</button>
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