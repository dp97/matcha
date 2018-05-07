<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chat</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
        <link rel="stylesheet" href="templates/css/stylesheet.css" >
    </head>
    <body>
        <!--include header content-->
        <?PHP include 'inc/header.php';?>
        
        <div class="pure-g chat-container">
            <div class="chat-left-container">
                <?PHP
                foreach ($connections as $user) {
                    echo "
                        <div class='chatroom-selector' onclick='selectChatroom(this, \"".$user['liker']."\")'><p class='chatroom-selector-text'>".$user['liker']."</p></div>
                    ";
                }
                ?>
            </div>
            <div class="chat-right-container">
                <div id="display-message-container">
                </div>
                <div id="write-message-container">
                    <input id="message-input" type="text" name="message" placeholder="write a message..." />
                    <button id="message-send" onclick="sendMsg()" class="pure-button pure-button-primary">Send</button>
                </di>
            </div>
        </div>
        <button onclick="fetchNewMsgs()">...plp</button>
        <script>
            var sendBtn = document.getElementById('message-send');
            var msg = document.getElementById('message-input');
            var chatroom = null;
            var chatroom_obj = null;
            var last_msg_id = "";
            
            function selectChatroom(obj, user) {
                if (chatroom == user) { return ; }
                if (chatroom_obj) {chatroom_obj.style.background = "";chatroom_obj.style.color = "black";}
                
                obj.style.background = "linear-gradient(to right, #ff758c, #ff7eb3)";
                obj.style.color = "white";
                chatroom_obj = obj;
                chatroom = user;
                
                var ajax = new XMLHttpRequest();
                
                ajax.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        undisplayMsg();
                        var msgs = JSON.parse(this.responseText);
                        var i = 0;
                        for (i in msgs) {
                            if (msgs[i].msg) {
                                last_msg_id = msgs[i].msg_id;
                                postMsg(msgs[i], msgs.currUser);
                            }
                        }
                        setInterval(function(){ fetchNewMsgs(); }, 3000);
                    }
                }
                ajax.open("GET", '/chat/get/conversation?u=' + user, true);
                ajax.send();
            }
            
            function sendMsg() {
                if (chatroom == null) {
                    return ;
                }
                var ajax = new XMLHttpRequest();
                var data = {
                    "receiver": chatroom,
                    "msg": msg.value
                };
                
                ajax.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        var json = JSON.parse(this.responseText);
                        postMsg(json, json.currUser);
                        msg.value = "";
                    }
                };
                ajax.open("POST", '/chat/post/msg', true);
                ajax.send(JSON.stringify(data));
            }
            
            function postMsg(json, currUser) {
                var div = document.createElement('div');
                div.className = "message-container";
                var color = (json.sender == currUser) ? 'chartreuse' : 'aqua';
                div.innerHTML = "<p class='message-text'><b style='color: "+color+";'>"+ json.sender + ":</b> " + json.msg +"</p><p class='notify-datetime' style='color: white;'>"+ json.datetime +"</p>";
                document.getElementById('display-message-container').appendChild(div);
            }
            
            function undisplayMsg() {
                document.getElementById('display-message-container').innerHTML = "";
            }
            
            function fetchNewMsgs() {
                var ajax = new XMLHttpRequest();
                
                ajax.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        var msgs = JSON.parse(this.responseText);
                        var i = 0;
                        for (i in msgs) {
                            if (msgs[i].msg && msgs[i].sender != msgs.currUser) {
                                last_msg_id = msgs[i].msg_id;
                                postMsg(msgs[i], msgs.currUser);
                            }
                        }
                    }
                };
                ajax.open("GET", '/chat/get/newmsgs?from_id=' + last_msg_id + "&user=" + chatroom, true);
                ajax.send();
            }
        </script>
        
        <!--inlcude footer content-->
        <?PHP include 'inc/footer.php';?>
    </body>
</html>