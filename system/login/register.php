
<?php 
if($_POST['registerBtn']){
    $getuser = $_POST['username'];
    $getemail = $_POST['email'];
    var_dump($getuser, $getemail);
}

    $register_form = "<form action='' method='post'>
                        <div class='mb-3'>
                            <label for='registerUsername' class='form-label'>Username</label>
                            <input type='text' class='form-control' id='registerUsername' name='username' value='$getuser' required>
                        </div>
                        <div class='mb-3'>
                            <label for='registerEmail' class='form-label'>Email address</label>
                            <input type='email' class='form-control' id='registerEmail' name='email' value='$getemail' aria-describedby='emailHelp' required>
                        <div id='emailHelp' class='form-text'>We'll never share your email with anyone else.</div>
                        </div>
                        <div class='mb-3'>
                            <label for='registerPassword' class='form-label'>Password</label>
                            <input type='password' name='password' class='form-control' id='registerPassword' value ='' required>
                        </div>
                        <div class='mb-3'>
                            <label for='registerPassword' class='form-label'>Retype Password</label>
                            <input type='password' name='passwordRetype' class='form-control' id='registerPasswordRetype' value ='' required>
                        </div>
                            <button type='submit' class='btn btn-primary' name='registerBtn'>Submit</button>
                    </form>"; 





echo $register_form;

?>