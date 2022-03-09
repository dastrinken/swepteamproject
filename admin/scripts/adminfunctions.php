<?php
if($_POST['saveArticle']) {
    writeArticleToDB();
}

function writeArticleToDB() {
    $title = $_POST['title'];
    $color = $_POST['color'];
    //$content = nl2br2($_POST['content']);
    $content = $_POST['content'];
    $author = $_POST['author'];
    $id_author = $_POST['userid'];
    $date = $_POST['date'];
    $publish = $_POST['publish'];    
    
    showToastMessage("Writing to DB:<br>Title:".$title." Color: ".$color."<br> Content: ".$content."<br>Author: ".$author." Date: ".$date."<br>Publish: ".$publish);

    $mysqli = connect_DB();
    $stmt = $mysqli->prepare("INSERT INTO clanms_news(headline, color, content, id_author, date_created, date_published) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("sssiss", $title, $color, $content, $id_author, $date, $publish);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

function nl2br2($string) {
    $string = str_replace(array("\r\n", "\r", "\n"), "<br />", $string);
    return $string;
    }
     
?>