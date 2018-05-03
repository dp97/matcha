<header>
    <div class="pure-menu pure-menu-horizontal" style="background: linear-gradient(to right, #29323c, #485563);">
        <a href="/" class="pure-menu-heading pure-menu-link">Matcha</a>
        <ul class="pure-menu-list">
            
            <?PHP 
            if (isset($login)) {
                echo '<li class="pure-menu-item"><a href="/login" class="pure-menu-link">Log in</a></li>';
            }
            if (isset($register)) {
                echo '<li class="pure-menu-item"><a href="/register" class="pure-menu-link">Register</a></li>';
            }
            if (isset($logout)) {
                echo '<li class="pure-menu-item"><a href="/logout" class="pure-menu-link">Logout</a></li>';
            }
            if (isset($settings)) {
                echo '<li class="pure-menu-item"><a href="/settings" class="pure-menu-link">Settings</a></li>';
            }
            if (isset($search)) {
                echo '<li class="pure-menu-item"><a href="/search" class="pure-menu-link">Search</a></li>';
            }
            ?>
        </ul>
    </div>
</header>