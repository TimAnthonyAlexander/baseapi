<?php

$custom = $_COOKIE['zugangscode'] ?? $_GET['zugangscode'] ?? null;

if ($custom ==='zooom'){
echo "<script>
    // first
    cookieName = 'zugangscode';
    cookieValue = '$custom';
    myDate = new Date();
    myDate.setMonth(myDate.getMonth() + 12);
    document.cookie = cookieName +'=' + cookieValue + ';expires=' + myDate + ';domain=.example.com;path=/';
</script>";

} else {

echo "<script>
    cookieName = 'zugangscode';
    cookieValue = prompt('Zugang:');
    myDate = new Date();
    myDate.setMonth(myDate.getMonth() + 12);
    document.cookie = cookieName +'=' + cookieValue + ';expires=' + myDate + ';domain=.example.com;path=/';
    location.reload();
</script>";
die();
}

?>
<script>
    if(confirm("In den Zoom-Anruf?")) {
        document.location.href = "https://us06web.zoom.us/j/83709983350?pwd=d2E3d2lJWWN6U0ROMkJiUG85MHl4UT09";
    }else{
        alert("Du wirst nicht zum Zoom-Anruf weitergeleitet!");
    }
</script>
