Test Mail Function
<?php
$to = "j.weigel@karo3.de";
$subject = "My subject";
$txt = "Hello world!";
$headers = "From: j.weigel@karo3.de" . "\r\n";

$success = mail($to,$subject,$txt,$headers);
if (!$success) {
	echo 'error';
	$errorMessage = error_get_last()['message'];
} else {
	echo 'success';
}
echo '?';
echo $errorMessage;
?>
