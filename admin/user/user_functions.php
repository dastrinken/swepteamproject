<?php 
    function deleteUserFromDB($userid){
        if (session_status() === PHP_SESSION_NONE){session_start();}
        $mysqli = connect_DB();
        $stmt1 = $mysqli->prepare("DELETE FROM clanms_user_groups WHERE clanms_user_groups.id_user = ?");
        $stmt1->bind_param("i", $userid);
        $stmt1->execute();
        $stmt1->close();
        $stmt2 = $mysqli->prepare("DELETE FROM clanms_user_profile WHERE clanms_user_profile.id_user = ?");
        $stmt2->bind_param("i",$userid);
        $stmt2->execute();
        $stmt2->close();
        $stmt3 = $mysqli->prepare("DELETE FROM clanms_user WHERE clanms_user.id = ?");
        $stmt3->bind_param("i", $userid);
        $stmt3->execute();
        $stmt3->close();
        $mysqli->close();
    }

    function getUsersFromDB($displayOption) {
        if (session_status() === PHP_SESSION_NONE){session_start();}
        $groups = getUserGroups();
        $mysqli = connect_DB();
        switch($displayOption) {
            case "all":
                $where = "";
                break;
            case "admin":
                $where = "WHERE cg.title = 'Admin'";
                break;
            case "moderator":
                $where = "WHERE cg.title = 'Moderator'";
                break;
            case "member";
                $where = "WHERE cg.title = 'Member'";
                break;
            case "registered":
                $where = "WHERE cg.title = 'Registered'";
                break;
            default:
                $where = "";
                break;
        }
        $select = "SELECT cs.id AS userid, cs.username, cs.email, cs.registeredSince, cs.activated, cg.title, cg.id AS groupId FROM clanms_user cs JOIN clanms_user_groups cug ON cs.id = cug.id_user JOIN clanms_groups cg ON cug.id_group = cg.id ".$where;
        $result = $mysqli->query($select, MYSQLI_USE_RESULT);
        $table = "<div class='table'>
        <div class='thead'>
            <div class='tr mb-2'>
                <span class='td border-bottom border-dark'>#</span>
                <span class='td border-bottom border-dark'>Username</span>
                <span class='td border-bottom border-dark'>E-mail</span>
                <span class='td border-bottom border-dark'>Registriert seit</span>
                <span class='td border-bottom border-dark'>Aktiviert (1 = ja, 0 = nein)</span>
                <span class='td border-bottom border-dark'>Nutzergruppe</span>
                <span class='td border-bottom border-dark'></span>
                <span class='td border-bottom border-dark'></span>
            </div>
        </div><div class='tbody'>";
        $count = 0;
        while($row = $result->fetch_assoc()) {
            ++$count;
            $user_id = $row['userid'];
            $user_name = $row['username'];
            $user_email = $row['email'];
            $user_registeredSince = $row['registeredSince'];
            $activatedInt = $row['activated'];
            if($row['activated']==="1"){
                $user_activated = "aktiviert";
            } elseif($row['activated']==="0"){
                $user_activated = "nicht aktiviert";
            }
            $group_title = $row['title'];
            $group_id = $row['groupId'];
            $table .= '<form method="post" class="tr activeTable" >
                        <span class="td border-end border-activeTable">
                        '.(/*$offset+*/$count).'
                            <input type="hidden" name="userId" value="'.$user_id.'">
                        </span>
                        <span class="td border-end border-activeTable" name="userName">
                            '.$user_name.'
                        </span>
                        <span class="td border-end border-activeTable" name="userEmail">
                            '.$user_email.'
                            <input type="hidden" name="userMail" value="'.$user_email.'">
                        </span>
                        <span class="td border-end border-activeTable" name="userRegistered">
                            '.$user_registeredSince.'
                            <input type="hidden" name="userRegisteredSince" value="'.$user_registeredSince.'">
                        </span>
            
                        <span class="td border-end border-activeTable">
                            <select name="activated" class="form-select border-0" aria-label="select activated status">
                                <option value="'.$activatedInt.'">'.$user_activated.'</option>
                                <option value="1">aktiviert</option>
                                <option value="0">nicht aktiviert</option>
                            </select>
                        </span>
                        <span class="td border-end border-activeTable">
                            <select name="userGroup" class="form-select border-0" aria-label="select user group">
                                <option value='.$group_id.'>'.$group_title.'</option>';
                                foreach($groups as $row) {
                                    $table .="<option value=".$row['groupId'].">".$row['title']."</option>";
                                }
                        $table .='</select>
                        </span>
                        <span class="td border-end border-activeTable">
                            <button name="updateUser" value="true" class="btn btn-secondary submit">Speichern</button>
                        </span>
                        <span class="td border-end border-activeTable">
                            <button name="deleteUserOverview" type="submit" value="true" class="btn btn-danger submit" onclick="return confirm(\'Der Benutzer wird endgültig aus der Datenbank gelöscht, bist du dir sicher?\');">Löschen</button>
                        </span>
                    </form>';
        }
        $table .= "</div>";
        $result->close();
        $mysqli->close();
        return $table;
    }

    function getActiveDropdown() {
        $mysqli = connect_DB();
        $stmt1 = $mysqli->prepare("DELETE FROM clanms_user_group WHERE clanms_user_groups.id_user = ?");
        $stmt1->bind_param("i", $userid);
        $stmt1->execute();
        $stmt1->close();
        $mysqli->close();
    }

    function getUserGroups(){
        $mysqli = connect_DB();
        $select = "SELECT id AS groupId, title FROM clanms_groups";
        $query = $mysqli->query($select,MYSQLI_USE_RESULT);
        $resultArray = $query->fetch_all(MYSQLI_ASSOC);
        $query->close;
        $mysqli->close;
        return $resultArray;
    }

    function writeUsersToDB() {
        $userid = $_POST['userId'];
        $useractivated = $_POST['activated'];
        $usergroup = $_POST['userGroup'];
        
        $mysqli = connect_DB();
        $stmt = $mysqli->prepare("UPDATE clanms_user SET clanms_user.activated = ? WHERE clanms_user.id = ?");
        $stmt->bind_param("ii",$useractivated, $userid);
        $stmt->execute();
        $stmt->close();
        $stmt2 = $mysqli->prepare("UPDATE clanms_user_groups SET clanms_user_groups.id_group = ? WHERE clanms_user_groups.id_user = ?");
        $stmt2->bind_param("ii", $usergroup, $userid);
        $stmt2->execute();
        $stmt2->close();
        $mysqli->close();
    }
?>