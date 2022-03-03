var cookie = document.cookie;
console.log("Cookies: "+cookie);

// General
function destroy_session() {
    var xhttp = new XMLHttpRequest();
    xhttp.open('GET','./system/destroy_session.php', true);
    xhttp.onreadystatechange=function(){
        if (xhttp.readyState == 4){
            if(xhttp.status == 200){
                window.location.href = './';
          }
        }
     };
     xhttp.send(null);
}

// Header
function openLoginRegisterModal(buttonId) {
    var xhttp = new XMLHttpRequest();

    var modalFormBody = document.getElementById("loginSignupModalBody");

    xhttp.onload = function() {
        modalFormBody.innerHTML = this.responseText;
    }
    if(buttonId == "loginBtn") {
        xhttp.open("GET", "./system/login/login.php");
    } else if(buttonId == "signupBtn") {
        xhttp.open("GET", "./system/login/register.php");
    }
    xhttp.send();
}
