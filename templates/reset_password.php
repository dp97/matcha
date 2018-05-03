<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Reset Password</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
        <link rel="stylesheet" href="templates/css/stylesheet.css" >
    </head>
    <body>
        <!--include header content-->
        <?PHP include 'inc/header.php';?>
        
        <form class="pure-form pure-form-aligned" method="POST" onsubmit="sendRequest('<?= (($pass) ? "pass" : "email") ?>', <?= (($pass) ? $user_id : 0) ?>); return false;">
            <fieldset>
                <div class="pure-controls">
                    <legend>Reset Password</legend>
                </div>
                
                <?PHP
                    if ($pass) {
                        echo '
                            <div class="pure-control-group">
                                <label for="pass">New Password:</label>
                                <input autofocus required id="pass" type="password" name="pass" placeholder="enter new password"/>
                                <span class="pure-form-message-inline">This is a required field.</span>
                            </div>
                        ';
                    } else {
                        echo '
                            <div class="pure-control-group">
                                <label for="email">Account Email:</label>
                                <input autofocus required id="email" type="email" name="email" placeholder="enter email"/>
                                <span class="pure-form-message-inline">This is a required field.</span>
                            </div>
                        ';
                    }
                ?>
                
                <div class="pure-controls">
                    <button id="confirm" type="submit" class="pure-button pure-button-primary">Confirm</button>
                    <span id="on-success" class="pure-form-message-inline" style="visibility: hidden;">Cool! An email with password reset instructions was sent.</span>
                </div>
            </fieldset>
        </form>
        
        <script>
            
            function sendRequest(input_type, user_id) {
                var but = document.getElementById("confirm");
                var onSuccess = document.getElementById("on-success");
                var xml = new XMLHttpRequest();
                var input = document.getElementById(input_type).value;
                
                but.innerHTML = "processing...";
                xml.onreadystatechange = function () {
                    if (this.readyState == 4) {
                        but.innerHTML = "Confirm";
                        if (this.status == 200) {
                            onSuccess.style.visibility = "visible";
                            var json = JSON.parse(this.responseText);
                            
                            if (json.state == "failure") {
                                onSuccess.innerHTML = json.cause;
                                onSuccess.style.color = "#af1515";
                                return ;
                            }
                            if (input_type == 'pass') {
                                onSuccess.innerHTML = "Your Password has been reset with Success.";
                                onSuccess.style.color = "#691";
                            } else {
                                onSuccess.innerHTML = "Cool! An email with password reset instructions was sent.";
                                onSuccess.style.color = "#691";
                            }
                            but.style.display = "none";
                            but.disabled = true;
                        }
                    }
                };
                xml.open('POST', '/reset_password', true);
                xml.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xml.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xml.send(input_type + "=" + input + "&user_id=" + user_id);
            }
        </script>
        
        <!--inlcude footer content-->
        <?PHP include 'inc/footer.php';?>
    </body>
</html>