<?php 
if($editing) {
    $title = $_GET['headline'];
    $content = $_GET['content'];
    $articleId = $_GET['articleId'];
    $color = $_GET['color'];
    $author_id = $_GET['author_id'];
    $author_name = $_GET['author_name'];
    $date_created = $_GET['date_created'];
    $date_published = $_GET['date_published'];
    echo "<h2 class='text-center mt-2'>Artikel bearbeiten</h2>";
} else {
    echo "<h2 class='text-center mt-2'>Neuer Artikel</h2>";
}
?>
<form action="./dashboard.php" method="post">
    <div class="d-flex mb-3">
        <div class="input-group me-2">
            <span class="input-group-text" id="ariaLabelTitle">Titel</span>
            <input id="newsTitle" class="form-control" type="text" aria-describedby="ariaLabelTitle" name="title" value="<?php echo $title; ?>" placeholder="Bitte Titel eingeben">
        </div>
        <div class="input-group w-25">
            <span class="input-group-text" id="ariaLabelTitleColor">Farbe</span>
            <input type="color" class="form-control form-control-color" id="exampleColorInput" name="color" value="<?php echo empty($color) ? "#dc3545" : $color; ?>" aria-describedby="ariaLabelTitleColor"  title="Choose your color">
        </div>

    </div>
    <div id="" class="mb-3">
        <label for="newsContent" class="form-label">Inhalt</label>
        <textarea class="form-control" id="newsContent" name="content" rows="15" placeholder="Fange an einen tollen Artikel zu schreiben..."><?php echo $content; ?></textarea>
    </div>
    <div class="d-flex mb-3">
        <div class="input-group w-auto me-2">
            <span class="input-group-text" id="ariaLabelAuthor">Author</span>
            <input class="form-control" type="text" name="author" value="<?php echo empty($author_name) ? $_GET['author'] : $author_name; ?>" aria-describedby="ariaLabelAuthor" readonly>
        </div>

        <div class="input-group w-auto me-2">
            <span class="input-group-text" id="ariaLabelDate">Datum</span>
            <input class="form-control" type="text" name="date" value="<?php echo empty($date_created) ? date('Y-m-d H:i:s') : $date_created; ?>" aria-describedby="ariaLabelDate" readonly>
        </div>

        <div class="input-group w-auto me-2">
            <span class="input-group-text" id="ariaLabelPublish">Veröffentlichung</span>
            <input class="form-control" type="datetime-local" name="publish" value="<?php echo empty($date_published) ? date('Y-m-d H:i:s') : $date_published; ?>" aria-describedby="ariaLabelPublish">
        </div>

        <input type="hidden" name="userid" value="<?php echo $_GET['userid']; ?>">
        <?php 
            if($editing) {
                echo '<input type="hidden" name="updateArticle" value="true">';
                echo '<input type="hidden" name="articleId" value="'.$articleId.'">';
                $editing = false;
            } else {
                echo '<input type="hidden" name="updateArticle" value="false">';
            }
        ?>
        <button id="saveArticle" class="form-control submit w-25" name="saveArticle" value="save">Speichern</button>
    </div>
</form>
<script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
<script>
    var simplemde = new SimpleMDE({ element: document.getElementById("newsContent") });
    console.log("Test");
</script>