<header class="p-3 d-flex flex-wrap align-items-center justify-content-center justify-content-md-between mb-4 border-bottom border-dark">

    <a href="./" class="d-flex align-items-center col-md-3 mb-2 mb-md-0 text-dark text-decoration-none">
        <!-- Logo TODO: select logo name/location from database -->
        <img class="bi" src="./ressources/icons/clanms_logo.svg" width="70" height="50" alt="Logo"></img>
    </a>
    
    <!-- Navigation der Seite. Hier GET-Parameter per URL im href-Attribut an index.php übergeben -->
    <ul class="nav justify-content-center">
        <li><a href="./" class="nav-link px-2 link-dark nav-darkmode">Home</a></li>
        <li><a href="./?nav=news" class="nav-link px-2 link-dark nav-darkmode">Blog</a></li>
        <li><a href="./?nav=info" class="nav-link px-2 link-dark nav-darkmode">About us</a></li>
        <li><a href="./?nav=calendar" class="nav-link px-2 link-dark nav-darkmode">Events</a></li>
        <li><a href="#" class="nav-link px-2 link-dark nav-darkmode">FAQ</a></li>
    </ul>
    <?php
        if(empty($_SESSION['userid']) || empty($_SESSION['username'])) {
            echo '<div class="col-md-2 text-end">
                        <button type="button" id="loginBtn" class="btn btn-darkmode-outline me-2" data-bs-toggle="modal" data-bs-target="#loginRegisterModal" onclick="openLoginRegisterModal(this.id)">Login</button>
                        <button type="button" id="signupBtn" class="btn btn-darkmode" data-bs-toggle="modal" data-bs-target="#loginRegisterModal" onclick="openLoginRegisterModal(this.id)">Sign-up</button>
                    </div>';
        } else {
            echo '<div class="col-md-2 text-end">
                    <button type="button" id="logoutBtn" class="btn btn-darkmode-outline me-2" onclick="destroy_session();return false;">Logout</button>
                </div>';
        }
    ?>

    <!-- Modal triggered by login button 
            TODO:   change buttons and content via javascript (not fully functional yet)
    -->
    <div class="modal fade" id="loginRegisterModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog text-dark">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Login to your account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="loginSignupModalBody" class="modal-body">
                <!-- Login / Register form -->
            </div>
            </div>
        </div>
    </div>
</header>
