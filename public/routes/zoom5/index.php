<?php
/*
 * Copyright (c) 2021. Der Code ist geistiges Eigentum von Tim Anthony Alexander.
 * Der Code wurde geschrieben unter dem Arbeitstitel und im Auftrag von baseapi.
 * Verwendung dieses Codes außerhalb von baseapi von Dritten ist ohne ausdrückliche Zustimmung von Tim Anthony Alexander nicht gestattet.
 */


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
    if(confirm("In den Zoom5-Anruf?")) {
        document.location.href = "https://us04web.zoom.us/j/74270511029?pwd=bEpzSG44RUpCUjk4Rms1ZEdWOUV1UT09";
    }else{
        alert("Du wirst nicht zum Zoom5-Anruf weitergeleitet!");
    }
</script>
