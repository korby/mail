#!/usr/bin/env php
<?php

if (in_array('phar', stream_get_wrappers()) && class_exists('Phar', 0)) {
	$includePrefix = "phar://mail.phar/";
} else {
	$includePrefix = "./";
}
include($includePrefix.'class.phpmailer.php');
include($includePrefix.'class.smtp.php');

$from=get_current_user()."@".gethostname();
$body= "See attachment(s).";
$attachs = false;
$debugLevel=0;

$optionsDefinitions = array();
$optionsDefinitions["h"] = "help";
$optionsDefinitions["a:"] = "attachement: filepath of an attachment to add, you can add multiple -a options to add many files";
$optionsDefinitions["b:"] = "body: by default it's \"".$body."\"";
$optionsDefinitions["d:"] = "debug level: 2 would be an interesting value";
$optionsDefinitions["f:"] = "from: by default it's ".$from;
$optionsDefinitions["u:"] = "user: MANDATORY smtp username";
$optionsDefinitions["s:"] = "subject: MANDATORY";

$options = getopt(implode("", array_keys($optionsDefinitions)));
//	var_dump($options); die();
foreach($options as $key=>$value) {
	switch($key) {
		case "a":
			$attachs=$value;
		break;

		case "b":
			$body=$value;
		break;

		case "d":
			$debugLevel=$value;
		break;

		case "f":
			$from=$value;
		break;

		case "h":
			help();
		break;

		case "s":
			$subject=$value;
		break;

		case "u":
			$user=$value;
		break;
		
	}
}

if(count($options) == 0) {

	echo "Invalid option or no option given (-s is mandatory)\n";
	help();
	die();
}

if (! isset($options["s"])) {

	die("Option -s is mandatory");
}

if (! isset($options["u"])) {

	die("Option -s is mandatory");
}

$desti = $argv[count($argv)-1];
if(preg_match("/^-/", $argv[count($argv)-2])) {

	die("Last argument must be recipient email address, not an option");
}

echo "gmail password for user ".$user.": ";

$handle = fopen ("php://stdin","r");
$pwd = fgets($handle);
if(trim($pwd) == ''){
    echo "no password, no job, bye...\n";
}
fclose($handle);

//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

$mail             = new PHPMailer();

$mail->CharSet = 'UTF-8';


$mail->SMTPDebug  = $debugLevel;                     // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only

$mail->IsSMTP(); // telling the class to use SMTP
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->SMTPSecure = "tls";                 // sets the prefix to the servier
$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
$mail->Port       = 587;                   // set the SMTP port for the GMAIL server
$mail->Username   = $user;  // GMAIL username
$mail->Password   = $pwd;            // GMAIL password

$mail->SetFrom($from);

$mail->AddReplyTo($from);

$mail->Subject    = $subject;

$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($body);

$mail->AddAddress($desti);

if($attachs) {
	if(is_array($attachs)) {
		foreach ($attachs as $attach) {
			$mail->AddAttachment(realpath($attachs));
		}
	} else {
		$mail->AddAttachment(realpath($attachs));
	}
}


if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}
    

 function help() {

global $optionsDefinitions;
    echo 'Usage ./mail.phar -u "smtpUser" -s "An amazing subject" recipient@domain.tld'. "\n";
 	foreach($optionsDefinitions as $option => $comment) {
 		$option = str_replace(":", "", $option);
 		printf("-%s %s\n", $option, $comment);

 	}
 }
