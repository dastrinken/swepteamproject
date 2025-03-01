<?php
    require_once(__DIR__ . "/../scripts/adminfunctions.php");
    if (session_status() === PHP_SESSION_NONE){session_start();}

    if($_POST['command'] === "saveGroupRights") {
        saveGroupRights($_POST['id']);
    }

    function getGroupsFromDB(){
        $mysqli = connect_DB();
        $select = "SELECT id FROM clanms_groups cg";
        $result = $mysqli->query($select, MYSQLI_USE_RESULT);
        $return = array();
        while($row = $result->fetch_assoc()) {
            $return[] = $row['id'];
        }
        $result->close();
        $mysqli->close();
        return $return;
    }

    function getRightsFromDB(){
        $mysqli = connect_DB();
        $select = "SELECT id FROM clanms_rights";
        $result = $mysqli->query($select, MYSQLI_USE_RESULT);
        $return = array();
        while($row = $result->fetch_assoc()) {
            $return[] = $row['id'];
        }
        $result->close();
        $mysqli->close();
        return $return;
    }

    function writeGroupToDB(){
        $rights = getRightsFromDB();
        $groupTitle = $_POST['groupTitle'];
        $groupDesc = $_POST['groupDesc'];
        $mysqli = connect_DB();
        $insert = $mysqli->prepare("INSERT INTO clanms_groups (title, description) VALUES (?, ?)");
        $insert->bind_param("ss",$groupTitle, $groupDesc);
        if($insert->execute()){
            $groupId = mysqli_insert_id($mysqli);
            $insert->close();
            $insert = "INSERT INTO clanms_group_has_rights (id_group, id_right, value) VALUES";
            foreach($rights as $row){
                if($row < count($rights)){
                    $insert .= "($groupId, $row, 0),";
                } else {
                    $insert .= "($groupId, $row, 0)";
                }
            }
        }
        $result = $mysqli->query($insert);
        $mysqli->close();
    }

    /** Displays all db-entries for group-rights in a (accordion-)table
     * @param int $groupId The id of the users group
     */
    function showRightsTable($groupId) {
        //TODO: Helpful tooltips on checkboxes
        $mysqli = connect_DB();
        $select = "SELECT cr.id AS rightId, 
                    cr.title AS rightTitle, 
                    cghr.value AS rightValue, 
                    cg.id AS groupId, 
                    cg.title AS groupTitle, 
                    cg.description AS groupDesc,
                    cr.sorting_order AS grouped 
                    FROM clanms_group_has_rights AS cghr
                    LEFT JOIN clanms_rights AS cr ON cghr.id_right = cr.id
                    LEFT JOIN clanms_groups AS cg ON cghr.id_group = cg.id
                    WHERE cg.id = ".$groupId."
                    ORDER BY cr.sorting_order ASC;";
        $result = $mysqli->query($select);
        $table = "<div class='table'>
                        <div class='thead'>
                            <div class='tr'>
                                <span class='td border-bottom border-end text-center'>#</span>
                                <span class='td border-bottom border-end'>Bereich</span>
                                <span class='td border-bottom'>Zugriff/Eigene verwalten</span>
                                <span class='td border-bottom'>Administration</span>
                            </div>
                        </div>
                        <div class='tbody'>";
        while($row = $result->fetch_assoc()) {
            if($row['grouped'] % 10 == 0) {
                $borderclass = "border border-0 border-top";
                if($row['grouped'] < 10) {
                    $leading = "News";
                } elseif($row['grouped'] < 20) {
                    $leading = "Event";
                } elseif($row['grouped'] < 30) {
                    $leading = "Gall";
                } elseif($row['grouped'] < 40) {
                    $leading = "Adm";
                } elseif($row['grouped'] < 50) {
                    $leading = "Acc";
                }
            } else {
                $borderclass = "";
            }
            $table .= "<div class='tr activeTable'>
                        <span class='td border-end text-center $borderclass'>".$leading."<input type='hidden' name='rightId' value='".$row['rightId']."'></span>
                        <span class='td border-end $borderclass'>".$row['rightTitle']."</span>";
            $checkedOwn = $row['rightValue'] >= 25 ? "checked" : "";
            $checkedAll = $row['rightValue'] >= 75 ? "checked" : "";
            //set checkboxes to disabled if permission is false and generally disable admin-rights checkboxes
            if(!checkPermission("manageRights", true, false) || $row['groupId'] === '1' && $row['groupTitle'] === 'Administrator'){
                $disabled = " disabled";
            } else {
                $disabled = "";
            }
            $table .= "<span class='td border-end $borderclass'><div class='form-check'><input id='".$row['groupId']."_".$row['rightId']."_25' class='form-check-input' type='checkbox' ".$checkedOwn.$disabled."></div></span>";
            //some rights only have one value therefore showing only one checkbox
            switch($row['rightId']) {
                case 6:
                case 7:
                case 8:
                case 9:
                case 10:
                    $table .= "<span class='td $borderclass'></span>";
                    break;
                default:
                $table .= "<span class='td $borderclass'><div class='form-check'><input id='".$row['groupId']."_".$row['rightId']."_75' class='form-check-input' type='checkbox' ".$checkedAll.$disabled."></div></span>";
                    break;
            }
            
            $table .= "</div>";
        }
        $table .= "</div>
                </div>";
        $result->close();
        $mysqli->close();
        echo $table;
    }

    function saveGroupRights($id) {
        $explodeId = explode("_", $id);
        $groupId = $explodeId[0];
        $rightId = $explodeId[1];
        $wantedValue = $explodeId[2];
        $checked = $_POST['active'];

        $select = "SELECT * 
                    FROM clanms_group_has_rights AS cghr 
                    WHERE id_group = $groupId 
                    AND id_right = $rightId";
        $mysqli = connect_DB();
        $result = $mysqli->query($select);
        while($row = $result->fetch_assoc()) {
            $idDB = $row['id'];
            $oldValue = $row['value'];
            switch($checked) {
                case "true":
                    $newvalue = $row['value'] > $wantedValue ? $oldValue : $wantedValue;
                    break;
                case "false":
                    $newvalue = $row['value'] < $wantedValue ? $oldValue : ($wantedValue - 25);
                    break;
            }
        }
        $result->close();

        $update = "UPDATE clanms_group_has_rights SET value = ? WHERE id = ?";
        $stmt = $mysqli->prepare($update);
        $stmt->bind_param("ii", $newvalue, $idDB);
        $stmt->execute();
        $stmt->close();
        $mysqli->close();
        // DEBUG: echo "groupId: ".$groupId." - rightId: ".$rightId." - (checkbox)value: ".$value." - checked: ".$checked." - Old Value: ".$oldValue." - New Value: ".$newvalue;
    }
    /* TODO: Beim Eintragen einer neuen Gruppe müssen alle Werte mit eingetragen werden (auf 0 gesetzt). */
?>