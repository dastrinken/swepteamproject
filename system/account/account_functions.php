<?php

/* Decide what to do before loading the content */
if($_POST['registerBtn']){
    registerNewAccount();
} elseif($_POST['loginBtn']) {
    loginAccount();
}

/** Reads the group-id from the database, given the users (group-members) id
 * @param mixed $userid The id of the user which is in a group
 * @return String Returns the id of the users group
 */
function getUserGroup($userid) {
    global $dbpre;
    $mysqli = connect_DB();
    $query = "SELECT id_group FROM ".$dbpre."user_groups WHERE id_user=$userid";
    $select = $mysqli->query($query);
    while($row = $select->fetch_row()) {
        return $row[0];
    }
    $select->close();
    $mysqli->close();
}

/** Selects the whole users profile from database
 * @param ?int $userid Optional parameter, it will be used as the userid to look for, if not set the sessions userid will be used
 * @return Array An associative mysqli-array with the selected result-set (users profile)
 */
function getUserProfile(?int $userid = null) {
    $userid = $userid == null ? $_SESSION['userid'] : $userid;
    $mysqli = connect_DB();
    $select = $mysqli->prepare("SELECT cup.name, cup.info, cg.title
                                FROM clanms_user_profile AS cup 
                                LEFT JOIN clanms_user_groups AS cug 
                                ON cup.id_user = cug.id_user 
                                LEFT JOIN clanms_groups AS cg
                                ON cug.id_group = cg.id
                                WHERE cup.id_user=?");
    $select->bind_param("i", $userid);
    $select->execute();
    $result = $select->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    return $data;
}

/** Selects the profile pic from the database and echoes it as a html img-tag
 * @param int $size The size of the picture
 * @param bool $rounded Decides which css class to display (0 = not rounded, 1 = rounded-circle)
 * @param ?int $userid Optional parameter, it will be used as the userid to look for, if not set the sessions userid will be used
 * @return String The image tag as an html-string
 */
function getProfilePic($size, $rounded, ?int $userid = 0) {
    $userid = $userid == 0 ? $_SESSION['userid'] : $userid;
    $mysqli = connect_DB();
    $select = $mysqli->prepare("SELECT avatar FROM clanms_user_profile WHERE id_user=?");
    $select->bind_param("i", $userid);
    $select->execute();
    $result = $select->get_result();
    $data = $result->fetch_assoc();
    foreach($data as $value) {
        $content = base64_encode($value);
    }
    if($rounded == 0) {
        $image = '<img src="data:image/png;base64,'.$content.'" width = "'.$size.'px" height= "'.$size.'px" />';
    } elseif($rounded == 1) {
        $image = '<img src="data:image/png;base64,'.$content.'" width = "'.$size.'px" height="'.$size.'px" class="rounded-circle" />';
    }
    return $image;
}

/* Profilseite */

/** Changes the users profile given the data from the change profile form
 * 
 */
function changeProfile() {
    if(empty($_POST['pname'])) {
        $pname = selectOneRow_DB("name", "clanms_user_profile", "id_user", $_SESSION['userid']);
    } else {
        $pname = $_POST['pname'];
    }
    if(empty($_POST['pinfo'])){
        $pinfo = selectOneRow_DB("info", "clanms_user_profile", "id_user", $_SESSION['userid']);
    } else {
        $pinfo = $_POST['pinfo'];
    }
    if(empty($_FILES['ppic']['tmp_name'])) {
        $ppic = selectOneRow_DB("avatar", "clanms_user_profile", "id_user", $_SESSION['userid']);
    } else {
        $ppic = file_get_contents($_FILES['ppic']['tmp_name']);
    }

    $mysqli = connect_DB();
    $update = "UPDATE clanms_user_profile SET name=?, avatar=?, info=? WHERE id_user=?";
    $stmt = $mysqli->prepare($update);
    if($stmt->bind_param("sssi", $pname, $ppic, $pinfo, $_SESSION['userid'])) {
        debug_to_console("Hat geklappt!");
    } else {
        debug_to_console($stmt);
    }

    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

/** Deletes the users Account from db given the users db-id (warning: can not be undone)
 * @param int $userid The "to be deleted" users id
 */
function deleteAccount($userid) {
    $getpass = $_POST['passwordDelete'];
    $password = selectOneRow_DB("password", "clanms_user", "id", $userid);
    if(password_verify($getpass, $password)) {
        $mysqli = connect_DB();
        $delete = "DELETE FROM clanms_user_groups WHERE id_user=$userid;
                    DELETE FROM clanms_user_profile WHERE id_user=$userid;
                    DELETE FROM clanms_user WHERE id=$userid;";
        if($mysqli->multi_query($delete)) {
            $errormsg = "success! everything is deleted";
        } else {
            $errormsg =  "error! data could not be deleted";
        }
        $mysqli->close();
    }
    showToastMessage($errormsg);
}

/** Changes the users email given the users id
 * @param int $userid The users id
 */
function emailChange($userid){
    $getpass = $_POST['passwordEmail'];
    $getemail = $_POST['newEmail'];
    $password = selectOneRow_DB("password", "clanms_user", "id", $userid);
    if(password_verify($getpass, $password)) {
        $mysqli = connect_DB();
        $changemail = $mysqli->prepare("UPDATE clanms_user SET email=? WHERE id=?");
        $changemail->bind_param("si", $getemail, $userid);
        if($changemail->execute()){
            echo("Email wurde erfolgreich geändert!");
        }else{
            echo("Emailänderung fehlgeschlagen!");
        }
        $changemail->close();
        $mysqli->close();
    }
}

/** Changes the users password
 * @param int $userid The users id
 */
function passwordChange($userid){
    $getpass = $_POST['oldPass'];
    $getnewpass = $_POST['newPass'];
    $getnewpassre = $_POST['newPassRe'];
    $password = selectOneRow_DB("password", "clanms_user", "id", $userid);
    if(password_verify($getpass, $password)) {
        if($getnewpass === $getnewpassre){
            $newpass = password_hash($getnewpass, PASSWORD_DEFAULT);
            $mysqli = connect_DB();
            $changepass = $mysqli->prepare("UPDATE clanms_user SET password=? WHERE id=?");
            $changepass->bind_param("si", $newpass, $userid);
            if($changepass->execute()){
                echo("Passwort wurde erfolgreich geändert!");
            }else{
                echo("Passwortänderung fehlgeschlagen!");
            }
            $changepass->close();
            $mysqli->close();
        }else{
            echo "Passwörter stimmen nicht überein!";
        }
    
    }
}

/* Login und Registrieren */

/** The login function. Validates and communicates with the database. Sets the session variables.
 * 
 */
function loginAccount() {
    $getemail = $_POST['email'];
    $getpass = $_POST['password'];
    if($getemail) {
        if($getpass) {
            $mysqli = connect_DB();
            $stmt = $mysqli->prepare("SELECT id, username, password, activated FROM clanms_user WHERE email=?");
            $stmt->bind_param("s", $getemail);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $id = $row['id'];
            $username = $row['username'];
            $password = $row['password'];
            $activated = $row['activated'];
            $stmt->close();
            $mysqli->close();

            if(password_verify($getpass, $password)){
                if($activated === 1) {
                    $_SESSION['userid'] = $id;
                    $_SESSION['username'] = $username; 
                    //Eventuell neue Tabellenspalte "lastvisited" anlegen und an dieser Stelle Timestamp setzen für Nutzungsstatistiken?
                    showToastMessage("Login erfolgreich");
                } else {
                    showToastMessage("Bitte aktiviere erst deinen Account!"); //Anbieten Email erneut zu schicken falls nicht angekommen?
                }
            } else {
                showToastMessage("Falsches Passwort!");
            }
        } else {
            showToastMessage("Bitte ein Passwort eingeben!");
        }
    } else {
        showToastMessage("Bitte Email eingeben.");
    }
}

/**
*  registerNewAccount is performed once when registering as a new user.
*  there are different parts of this function:
*      1. register new user into database
*      2. insert user into standard user group
*      3. create new user_profile with standard avatar
*      4. send activation email
*
*      TODO: create procedure or trigger to automatically detect and delete unused user profiles
*/
function registerNewAccount() {
    global $dbpre;
    global $title;
    $getuser = $_POST['username'];
    $getemail = $_POST['email'];
    $getpass = $_POST['password'];
    $getpassretype = $_POST['passwordRetype'];

    if($getuser) {
        if($getemail && filter_var($getemail, FILTER_VALIDATE_EMAIL)) {
            if($getpass) {
                if($getpassretype) {
                    if($getpass === $getpassretype) {
                        $mysqli = connect_DB();
                        
                        //Escaping users input for security reasons
                        $getuser = mysqli_escape_string($mysqli, $getuser);
                        $getemail = mysqli_escape_string($mysqli, $getemail);
                        $getpass = mysqli_escape_string($mysqli, $getpass);
                        $getpassretype = mysqli_escape_string($mysqli, $getpassretype);

                        $result = $mysqli->query("SELECT id FROM ".$dbpre."user WHERE username='$getuser'");
                        $rowcount = $result->num_rows;
                        $result->close();
                        if($rowcount == 0) {
                            $result = $mysqli->query("SELECT id FROM ".$dbpre."user WHERE email='$getemail'");
                            $rowcount = $result->num_rows;
                            $result->close();
                            if($rowcount == 0){
                                $password = password_hash($getpass, PASSWORD_DEFAULT);
                                $date = date("Y-m-d");
                                $code = md5(rand());
                                $activated = 0;
                                
                                $stmt = $mysqli->prepare("INSERT INTO `clanms_user` (`username`, `password`, `email`, `registeredSince`, `activated`, `activationCode`) VALUES (?,?,?,?,?,?)");
                                $stmt->bind_param("ssssis", $getuser, $password, $getemail, $date, $activated, $code);
                                $stmt->execute();
                                $stmt->close();
                                $result = $mysqli->query("SELECT id FROM ".$dbpre."user WHERE username='$getuser'");
                                $rowcount = $result->num_rows;
                                while($row = $result->fetch_row()) {
                                    $id = $row[0];
                                }
                                $result->close();
                                if($rowcount == 1) {
                                    /* Setze Rechte für neu erstelltes Benutzerprofil */
                                    $usergroup = 4;
                                    $comment = $getuser." - new User";
                                    $stmt = $mysqli->prepare("INSERT INTO clanms_user_groups (id_user, id_group, comment) VALUES (?,?,?)");
                                    $stmt->bind_param("iis", $id, $usergroup, $comment);
                                    $stmt->execute();
                                    $stmt->close();
                                    /* Erstelle Benutzerprofil in der Datenbank */
                                    $avatar = file_get_contents(__DIR__."/../../ressources/images/standardAvatar.png");
                                    $info = "Willkommen auf meinem öffentlichen Profil!";
                                    $stmt = $mysqli->prepare("INSERT INTO clanms_user_profile (id_user, name, avatar, info) VALUES (?,?,?,?)");
                                    $stmt->bind_param("isss", $id, $getuser, $avatar, $info);
                                    $stmt->execute();
                                    $stmt->close();
                                    /* Verschicke bestätigungsemail */
                                    // TODO: Schönere Email formulieren
                                    $site = $_SERVER['SERVER_NAME']."/clanms/index.php"; // TODO: !clanms (Ordner) aus URL entfernen vor dem Release! url aus Datenbank auslesen?
                                    $webmaster = getSetting("email"); //TODO: Set up webmaster email during installation process!
                                    $header[] = 'MIME-Version: 1.0';
                                    $header[] = 'Content-type: text/html; charset=iso-8859-1';
                                    $header[] = "To: $getuser <$getemail>";
                                    $header[] = "From: $webmaster";
                                    $subject = "Deine Registrierung bei $title"; //Titel & Message sollen später vom Seitenbetreiber selbst über Adminpanel festgelegt werden können.
                                    $message = "Hallo $getuser!<br/>Du hast dich soeben erfolgreich bei $title registriert.<br/>"; 
                                    $message .= "<a href=\"$site?id=$id&code=$code\">Um deine Registrierung abzuschließen, klicke bitte auf diesen Link</a>";
                                    //var_dump Ausgabe der Email zum Testen, vor Release entfernen:
                                    showToastMessage($message);
                                    if(mail($getemail, $subject, $message, implode("\r\n", $header))) {
                                        $errormsg = "Registrierung erfolgreich, es wurde eine Email mit Aktivierungslink an die angegebene Adresse verschickt";
                                        $errormsg .= "<br>webmaster mail: $webmaster";
                                        $errormsg .= "<br>header: $header";
                                        $errormsg .= "<br>subject: $subject";
                                        $errormsg .= "<br>message: $message";
                                        /* important: empty user and email variable (don't change this!) */
                                        $getuser = "";
                                        $getemail = "";
                                    } else {
                                        $errormsg = "Email konnte nicht verschickt werden. Bitte kontaktiere den Administrator. $webmaster";
                                    }
                                } else {
                                    $errormsg = "Es ist ein Fehler aufgetreten, dein Account wurde nicht registriert";
                                }
                            }
                            else{
                                $errormsg = "Diese Emailadresse ist bereits vergeben";
                            }
                        } else {
                            $errormsg = "Der Benutzername existiert bereits, bitte wählen sie einen anderen";
                        }
                        $mysqli->close();
                    } else {
                        $errormsg = "Die beiden Passwörter stimmen nicht überein";
                    }
                } else {
                    $errormsg = "Bitte bestätige dein Passwort.";
                }
            } else {
                $errormsg = "Bitte geben Sie ein Passwort ein";
            }
        } else {
            $errormsg = "Bitte geben sie eine gültige Emailadresse ein!";
        }
    }
    else {
        $errormsg = "Bitte geben sie einen Benutzernamen ein";
    }
showToastMessage($errormsg);
}

?>