<form action='./?nav=profile' method='post'>
    <div class="mb-3">
        <label for="profilename" class="form-label"><abbr title="Ändere deinen öffentlichen Namen">Profilname</abbr></label>
        <input type="text" class="form-control" id="profilename" value="<?php echo $pname; ?>" name="pname">
    </div>
    <div class="mb-3">
        <label for="avatar" class="form-label"><abbr title="Hier kannst du ein neues Profilbild hochladen (max. 64kB)">Profilbild</abbr></label>
        <input class="form-control" type="file" id="avatar" name="ppic">
    </div>
    <div class="mb-3">
        <label for="infotext" class="form-label"><abbr title="Der Infotext deines öffentlichen Profils">Profilinfo</abbr></label>
        <textarea class="form-control" id="infotext" rows="3" name="pinfo"></textarea>
    </div>
    <div class="mb-3">
        <input name="changeProfile" type="submit" value="Absenden"/>
    </div>
</form>