<?php
# PHP message board (MBoard)
# Version: 1.3 from November 18, 2006  (fix from 16th Feb 2007)
# File name: mboard.php
# http://www.PHPJunkYard.com

##############################################################################
# COPYRIGHT NOTICE                                                           #
# Copyright 2004-2006 PHPJunkYard All Rights Reserved.                       #
#                                                                            #
# This script may be used and modified free of charge by anyone so long as   #
# this copyright notice and the comments above remain intact. By using this  #
# code you agree to indemnify Klemen Stirn from any liability that might     #
# arise from it's use.                                                       #
#                                                                            #
# Selling the code for this program without prior written consent is         #
# expressly forbidden. In other words, please ask first before you try and   #
# make money off this program.                                               #
#                                                                            #
# Obtain permission before redistributing this software over the Internet or #
# in any other medium. In all cases copyright and header must remain intact. #
# This Copyright is in full effect in any country that has International     #
# Trade Agreements with the United States of America or with                 #
# the European Union.                                                        #
##############################################################################

#############################
#     DO NOT EDIT BELOW     #
#############################

// Start the session
session_start();

error_reporting(E_ALL ^ E_NOTICE);
define('IN_SCRIPT',true);

require_once('settings.php');
$settings['verzija']='1.3';

if(empty($_REQUEST['a'])) {
    $a='';
} else {
    $a=htmlspecialchars($_REQUEST['a']);
}

if ($settings['autosubmit'] && ($a=='addnew' || $a=='reply')) {

    session_start();

    if (!empty($_SESSION['block'])) {
        printTopHTML();
        problem('You are not allowed to post on this message board!');
    }

    if (empty($_SESSION['checked'])) {
        $_SESSION['checked']='N';
        $_SESSION['secnum']=rand(10000,99999);
        $_SESSION['checksum']=crypt($_SESSION['secnum'],$settings['filter_sum']);
    }
    if ($_SESSION['checked'] == 'N')
    {
        print_secimg();
    }
    elseif ($_SESSION['checked'] == $settings['filter_sum'])
    {
        $_SESSION['checked'] = 'N';
        $mysecnum=pj_isNumber($_POST['secnumber']);

        if(empty($mysecnum))
        {
            print_secimg(1);
        }

        require('secimg.inc.php');
        $sc=new PJ_SecurityImage($settings['filter_sum']);
        if (!($sc->checkCode($mysecnum,$_SESSION['checksum']))) {
            print_secimg(2);
        }

        $_SESSION['checked']='';

    }
    else
    {
        problem('Internal script error. Wrong session parameters!');
    }

}

printTopHTML();

if ($a) {

    if (!empty($_SESSION['block'])) {
        problem('You are not allowed to visit this forum!');
    }

    if ($a=='delete') {
        $num=pj_isNumber($_REQUEST['num'],'Internal script error: Wrong data type for $num');
        $up=pj_isNumber($_REQUEST['up'],'Internal script error: Wrong data type for $num');
        confirmDelete($num,$up);
    }
    if ($a=='confirmdelete') {
        $pass=pj_input($_REQUEST['pass'],'Please enter your admin password!');
        $num=pj_isNumber($_REQUEST['num'],'Internal script error: Wrong data type for $num');
        $up=pj_isNumber($_REQUEST['up'],'Internal script error: Wrong data type for $num');
        doDelete($pass,$num,$up);
    }

    $name=pj_input($_POST['name'],'Please enter your name!');
    $message=pj_input($_POST['message'],'Please write a message!');

    if(!empty($_POST['email']))
    {
        $email=pj_input($_POST['email']);
            if(!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/",$email))
            {
                problem('Please enter a valid e-mail address!');
            }
        $char = array('.','@');
        $repl = array("&#46;","&#64;");
        $email=str_replace($char,$repl,$email);
    }
    else {$email='NO';}

    /* Check the message with JunkMark(tm)? */
    if ($settings['junkmark_use'])
    {
        $junk_mark=JunkMark($email,$message);

        if ($junk_mark >= $settings['junkmark_limit'])
        {
            $_SESSION['block'] = 1;
            problem('You are not allowed to post on this message board!');
        }
    }

    if ($a=='addnew')
    {
        $subject=pj_input($_POST['subject'],'Please write a subject!');
        addNewTopic($name,$email,$subject,$message);
    }
    elseif ($a=='reply')
    {
        $subject=pj_input($_POST['subject'],'Please write a subject!');
        $orig['id']=pj_isNumber($_POST['orig_id'],'Internal script error: Wrong data type for orig_id');
        $orig['name']=pj_input($_POST['orig_name'],'Internal script error: No orig_name');
        $orig['sub']=pj_input($_POST['orig_subject'],'Internal script error: No orig_subject');
        $orig['date']=pj_input($_POST['orig_date'],'Internal script error: No orig_date');
        addNewReply($name,$email,$subject,$message,$orig['id'],$orig['name'],$orig['sub'],$orig['date']);
    }
    else {problem('Internal script error: No valid action');}
}

?>
<!--<h3 align="center"><?php /* echo $settings['mboard_title']; */ ?></h3>

<!--<div align="center"><center>-->
<table border="0" width="100%" style="border-bottom: 1px solid #dff0d8;"><tr>
<td>

<!--<p><a href="#new"><b>Add a new topic</b></a></p>-->
<!--<hr>-->
<p align="center"><b>Recent topics</b></p>
<ul>
<?php
include_once 'threads.txt';
?>
</ul>
<!--<hr>--></td>
</tr></table>
<!--</center></div>-->

<p align="center"><a name="new"></a><b>Add new topic</b></p>
<div align="center"><center>
<table border="0"><tr>
<td>
<form method="post" action="mboard.php" name="form" onSubmit="return mboard_checkFields();">
<input type="hidden" name="a" value="addnew"><!--<b>Name:</b><br>--><input type="hidden" name="name" size=30 maxlength=30 class="form-control" value="<?php echo $_SESSION['firstname']." ".$_SESSION['surname']; ?>">
<!--E-mail (optional):<br><input type=text name="email" size=30 maxlength=50><br>-->
<b>Subject:</b><br><input type="text" name="subject" size="35" width="100%" maxlength="100" class="form-control" placeholder="Enter subject"><br>
<b>Message:</b><br><textarea cols="35" rows="3" name="message" width="100%" class="form-control" placeholder="Enter message"></textarea><br><!--
Insert styled text: <a href="Javascript:insertspecial('B')"><b>Bold</b></a> |
<a href="Javascript:insertspecial('I')"><i>Italic</i></a> |
<a href="Javascript:insertspecial('U')"><u>Underlined</u></a><br>
<input type="checkbox" name="nostyled" value="Y"> Disable styled text</p>-->
<?php
if ($settings['smileys']) {
    echo '';
}
?>
<p><input type="submit" class="btn btn-sm btn-default" value="Add new topic">
<!--<p style="color:#F00;"><strong>Message board currently under maintenance!</strong></p>-->
</form>
</td>
</tr></table>
</center></div>
<?php
printDownHTML();
exit();


// >>> START FUNCTIONS <<< //

function filter_bad_words($text) {
global $settings;
$file = 'badwords/'.$settings['filter_lang'].'.php';

    if (file_exists($file))
    {
        include_once($file);
    }
    else
    {
        problem("The bad words file ($file) can't be found! Please check the
        name of the file. On most servers names are CaSe SeNsiTiVe!");
    }

    foreach ($settings['badwords'] as $k => $v)
    {
        $text = preg_replace("/\b$k\b/i",$v,$text);
    }

return $text;
} // END filter_bad_words

function addNewReply($name,$email,$subject,$comments,$orig_id,$orig_name,$orig_subject,$orig_date) {
global $settings;
$date=date("D jS F G:i");

$comments = str_replace("\'","'",$comments);
$comments = str_replace("\&quot;","&quot;",$comments);
$comments = MakeUrl($comments);
$comments = str_replace("\r\n","<br>",$comments);
$comments = str_replace("\n","<br>",$comments);
$comments = str_replace("\r","<br>",$comments);

/* Let's strip those slashes */
$comments = stripslashes($comments);
$subject = stripslashes($subject);
$name = stripslashes($name);
$orig_name = stripslashes($orig_name);
$orig_subject = stripslashes($orig_subject);

/* Make text bold, italic and underlined text */
if ($_REQUEST['nostyled'] != "Y") {$comments=styledText($comments);}

if ($settings['smileys'] == 1 && $_REQUEST['nosmileys'] != "Y") {$comments = processsmileys($comments);}
if ($email != "NO") {$mail = "&lt;<a href=\"mailto:$email\">$email</a>&gt;";}
else {$mail=" ";}

if ($settings['filter']) {
$comments = filter_bad_words($comments);
$name = filter_bad_words($name);
$subject = filter_bad_words($subject);
}

$fp = fopen("count.txt","rb") or problem("Can't open the count file (count.txt) for reading!");
$count=fread($fp,6);
fclose($fp);
$count++;
$fp = fopen("count.txt","wb") or problem("Can't open the count file (count.txt) for writing! Please CHMOD this file to 666 (rw-rw-rw)");
if (flock($fp, LOCK_EX)) {
    fputs($fp,$count);
    flock($fp, LOCK_UN);
} else {
    echo "Error locking a file, please try again later!";
}
fclose($fp);

$threads = file("threads.txt");

for ($i=0;$i<=count($threads);$i++) {
    if(strstr($threads[$i],'<!--o '.$orig_id.'-->'))
        {
        preg_match("/<\!--(.*)-->\s\((.*)\)/",$threads[$i],$matches);
        $number_of_replies=$matches[2];$number_of_replies++;
        $threads[$i] = "<!--o $orig_id--> ($number_of_replies)\n";
        $threads[$i] .= "<!--z $count-->\n";
        $threads[$i] .= "<!--s $count--><ul><li><a href=\"msg/$count.$settings[extension]\">$subject</a> - <b>$name</b> <i>$date</i>\n";
        $threads[$i] .= "<!--o $count--> (0)\n";
        $threads[$i] .= "</li></ul><!--k $count-->\n";
        break;
        }
}

$newthreads=implode('',$threads);

$fp = fopen("threads.txt","wb") or problem("Couldn't open links file (threads.txt) for writing! Please CHMOD it to 666 (rw-rw-rw)!");
if (flock($fp, LOCK_EX)) {
    fputs($fp,$newthreads);
    flock($fp, LOCK_UN);
} else {
    echo "Error locking a file, please try again later!";
}
fclose($fp);

$other = "in reply to <a href=\"$orig_id.$settings[extension]\">$orig_subject</a> posted by $orig_name on $orig_date";
createNewFile($name,$mail,$subject,$comments,$count,$date,$other,$orig_id);

$oldfile="msg/".$orig_id.".".$settings['extension'];

$filecontent = file($oldfile);

for ($i=0;$i<=count($filecontent);$i++) {
    if(preg_match("/<!-- zacni -->/",$filecontent[$i]))
        {
        $filecontent[$i] = "<!-- zacni -->\n<!--s $count--><li><a href=\"$count.$settings[extension]\">$subject</a> - <b>$name</b> <i>$date</i></li>\n";
        break;
        }
}

$rewritefile=implode('',$filecontent);

$fp = fopen($oldfile,"wb") or problem("Couldn't open file $oldfile for writing! Please CHMOD the &quot;msg&quot; folder to 777 (rwx-rwx-rwx)!");
if (flock($fp, LOCK_EX)) {
    fputs($fp,$rewritefile);
    flock($fp, LOCK_UN);
} else {
    echo "Error locking a file, please try again later!";
}
fclose($fp);

?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p align="center"><b>Your message was successfully added!</b></p>
<p align="center"><a href="mboard.php">Click here to continue</a></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>

<?php
printDownHTML();
exit();
}

function createNewFile($name,$mail,$subject,$comments,$count,$date,$other="",$up="0") {
global $settings;
$header=implode('',file('header.txt'));
$footer=implode('',file('footer.txt'));
$content='
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Hendy&#39;s Hunches: Message Board</title>
<meta content="text/html; charset=windows-1250">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<link href="'.$settings['mboard_url'].'/style.css" type="text/css" rel="stylesheet">
<link href="'.$settings['website_url'].'css/bootstrap.min.css" type="text/css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="'.$settings['mboard_url'].'/javascript.js"></script>
<script language="javascript" type="text/javascript" src="'.$settings['website_url'].'js/iframeResizer.contentWindow.min.js"></script>
';
$content.=$header;

$content.='
<!--<h3 align="center">'.$settings['mboard_title'].'</h3>-->

<!--<div align="center"><center>-->
<table border="0" width="100%" style="border-bottom: 1px solid #dff0d8;"><tr>
<td>

<p align="center">
<a href="'.$settings['mboard_url'].'/mboard.php">Back to all messages</a></p>
<!--<hr>-->
<p align="center"><b>'.$subject.'</b></p>

<p><a href="'.$settings['mboard_url'].'/mboard.php?a=delete&num='.$count.'&up='.$up.'"><img
src="'.$settings['mboard_url'].'/images/delete.gif" width="16" height="14" border="0" alt="Delete this post"></a>
Submitted by '.$name.' '.$mail.' on '.$date.' '.$other;

if ($settings['display_IP']==1) {$content .= '<br><font class="ip">'.$_SERVER['REMOTE_ADDR'].'</font>';}

$content .= '</p>

<p><b>Message</b>:</p>

<p>'.$comments.'</p>

<!--<hr color="green" size="1">-->

<p align="center"><b>Replies to this post</b></p>
<ul>
<!-- zacni --><p>No replies yet</p>
</ul>
<!--<hr>--></td>
</tr></table>
<!--</center></div>-->

<p align="center"><a name="new"></a><b>Reply to this post</b></p>
<div align="center"><center>
<table border="0"><tr>
<td>
<form method=post action="'.$settings['mboard_url'].'/mboard.php" name="form" onSubmit="return mboard_checkFields();">
<input type="hidden" name="a" value="reply"><!--<b>Name:</b><br>--><input type="hidden" name="name" size=30 maxlength=30 class="form-control" value="<?php echo $_SESSION["firstname"]." ".$_SESSION["surname"]; ?>">
<!--E-mail (optional):<br><input type=text name="email" size=30 maxlength=50><br>-->
<b>Subject:</b><br><input type="text" name="subject" value="Re: '.$subject.'" size="35" width="100%" maxlength=100 class="form-control"><br>
<b>Message:</b><br><textarea cols="35" rows="3" width="100%" name="message" class="form-control" placeholder="Enter message"></textarea>
<input type="hidden" name="orig_id" value="'.$count.'">
<input type="hidden" name="orig_name" value="'.$name.'">
<input type="hidden" name="orig_subject" value="'.$subject.'">
<input type="hidden" name="orig_date" value="'.$date.'"><br>
<!--
Insert styled text: <a href="Javascript:insertspecial(\'B\')"><b>Bold</b></a> |
<a href="Javascript:insertspecial(\'I\')"><i>Italic</i></a> |
<a href="Javascript:insertspecial(\'U\')"><u>Underlined</u></a><br>
<input type="checkbox" name="nostyled" value="Y"> Disable styled text</p>-->
';

if ($settings['smileys']) {
    $content.='';
}

$content.='
<p><input type="submit" class="btn btn-sm btn-default" value="Submit reply">
</form>
</td>
</tr></table>
</center></div>
<br><!--<br><hr>-->
';
/*
eval(gzinflate(base64_decode('DdDZkmNQAADQz+nu8oAba031Q8QWOxHEyxThIi6XIJavnzmfcM
pPhr6ro+khyubyO8+mkmP+FuUTF+X3l/TU06mbzfNZ9o8D8Wgt5Kt4GwRKUg5J4YahyLc2V2g7XMWknI
VhBh5RT8Q4WIi89reTbkykEKUhc2efAnSL0ha54NOOvhByLAT3XnufLpl61tkrtNpuZWYxz5V6YaKlGX
2VZmnqtmdMXMNX4SGyUMCgmjNACHoRbrMS+gIpvZmyrxZu6S55KNOywrbbcoL5pXS2/iqqT7Iar6+EUP
noPQMHIWRDGoCyeK3XyrdoFfQgmDnBDj87uKZxPXFvO2f9pfhs9+PWmBu/sc5KLqcUSDY4FwfG+rlt5L
rfp5gvHg616K+jGajiyMuIAgHzcErfS2k8Ea61p1tBCRTc3WPhOqiiVhsv1aggC6qPdy4OQcLkJtfgUd
m0YFSy1D4ivMVWks0BLxiX3Hg0przZmdpjbwlrKU9hA+iIiK/cyLuPGg8cQVkwmHKGMJ8npwzdjBbIm3
SZIhzJxlk7C+F8n7ExbmBTS3eNTGH20W0Jmj12+hNGB4Mfk5pgdnIbSjaQU0HbV4EokDrE3FAHfRaIse
+2zkaNettj2oy7pe+oQb/jpe3qG5Fqktm5IK0wtOTE0Z1Z5KDuEFRV1Hy294zrCkvKJ4qaPd8q00yt0b
HxXjneriiG5KkC8eA2e89HVytrpZqhWBkpq6xm9YnZo9vIIdY1qBdHkpji9OzWEjRewkSK54TNvLmvVC
E/hjZcstDRB8VI6qHXqeEVGIkc8PhgG3zjxP9lQTP7H42lkhmt5hYk6OK3DO4Ndaqs8wnyIkF6PEF+Tn
D9/f36+fn58w8=')));*/

$content.=$footer;
$content.='';

$newfile="msg/".$count.".".$settings['extension'];
$fp = fopen($newfile,"wb") or problem("Couldn't create file &quot;$newfile&quot;! Please CHMOD the &quot;msg&quot; folder to 666 (rw-rw-rw)!");
if (flock($fp, LOCK_EX)) {
    fputs($fp,$content);
    flock($fp, LOCK_UN);
} else {
    echo "Error locking a file, please try again later!";
}
fclose($fp);

unset($content);
unset($header);
unset($footer);

/* Notify admin */
if ($settings['notify'] == 1)
    {
    $message = "Hello!

Someone has just posted a new message on your forum! Visit the below URL to view the message:

$settings[mboard_url]/$newfile

End of message
";

    mail($settings['admin_email'],'New forum post',$message);
    }

/* Delete old posts */
$count -= $settings['maxposts'];
$newfile="msg/".$count.".".$settings['extension'];
if (file_exists($newfile))
    {
        deleteOld($count,$newfile);
    }

}

function addNewTopic($name,$email,$subject,$comments) {
global $settings;
$date=date("D jS F G:i");

$comments = str_replace("\'","'",$comments);
$comments = str_replace("\&quot;","&quot;",$comments);
$comments = MakeUrl($comments);
$comments = str_replace("\r\n","<br>",$comments);
$comments = str_replace("\n","<br>",$comments);
$comments = str_replace("\r","<br>",$comments);

/* Let's strip those slashes */
$comments = stripslashes($comments);
$subject = stripslashes($subject);
$name = stripslashes($name);

/* Make text bold, italic and underlined */
if ($_REQUEST['nostyled'] != "Y") {$comments=styledText($comments);}

if ($settings['smileys'] == 1 && $_REQUEST['nosmileys'] != "Y") {$comments = processsmileys($comments);}
if ($email != "NO") {$mail = "&lt;<a href=\"mailto&#58;$email\">$email</a>&gt;";}
else {$mail=" ";}

if ($settings['filter']) {
$comments = filter_bad_words($comments);
$name = filter_bad_words($name);
$subject = filter_bad_words($subject);
}

$fp = fopen("count.txt","rb") or problem("Can't open the count file (count.txt) for reading!");
$count=fread($fp,6);
fclose($fp);
$count++;
$fp = fopen("count.txt","wb") or problem("Can't open the count file (count.txt) for writing! Please CHMOD this file to 666 (rw-rw-rw)");
if (flock($fp, LOCK_EX)) {
    fputs($fp,$count);
    flock($fp, LOCK_UN);
} else {
    echo "Error locking a file, please try again later!";
}
fclose($fp);

$addline = "<!--z $count-->\n";
$addline .= "<!--s $count--><p><li><a href=\"msg/$count.$settings[extension]\">$subject</a> - <b>$name</b> <i>$date</i>\n";
$addline .= "<!--o $count--> (0)\n";
$addline .= "</li><!--k $count-->\n";

$fp = @fopen("threads.txt","rb") or problem("Can't open the log file (threads.txt) for reading!");
$threads = @fread($fp,filesize("threads.txt"));
fclose($fp);
$addline .= $threads;
$fp = fopen("threads.txt","wb") or problem("Couldn't open links file (threads.txt) for writing! Please CHMOD it to 666 (rw-rw-rw)!");
if (flock($fp, LOCK_EX)) {
    fputs($fp,$addline);
    flock($fp, LOCK_UN);
} else {
    echo "Error locking a file, please try again later!";
}
fclose($fp);
createNewFile($name,$mail,$subject,$comments,$count,$date);

?>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p align="center"><b>Your message was successfully added!</b></p>
<p align="center"><a href="mboard.php">Click here to continue</a></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<?php
printDownHTML();
exit();
}

function deleteOld($num,$file) {
global $settings;

    if ($settings['keepoldmsg'] == 0) {unlink($file);}

// Delete input from threads.txt
$keep = 'YES';
$threads = file('threads.txt');

$newthreads='';
foreach ($threads as $mythread) {
    if (strstr($mythread,'<!--z '.$num.'-->')) {$keep = 'NO'; continue;}
    elseif (strstr($mythread,'<!--k '.$num.'-->')) {$keep = 'YES'; continue;}
    elseif ($keep == 'NO') {continue;}
    else {$newthreads.=$mythread;}
}

$fp = fopen("threads.txt","wb") or problem("Couldn't open links file (threads.txt) for writing! Please CHMOD it to 666 (rw-rw-rw)!");
if (flock($fp, LOCK_EX)) {
    fputs($fp,$newthreads);
    flock($fp, LOCK_UN);
} else {
    echo "Error locking a file, please try again later!";
}
fclose($fp);

}

function doDelete($pass,$num,$up) {
global $settings;
if ($pass != $settings[apass]) {problem("Wrong password! The entry hasn't been deleted.");}

    if ($settings['keepoldmsg'] == 0)
    {
        unlink("msg/$num.$settings[extension]") or problem("Can't delete this post,
        access denied or post doesn't exist!");
    }

// Delete input from threads.txt
$keep = 'YES';
$threads = file('threads.txt');

$newthreads='';
foreach ($threads as $mythread) {
    if (!empty($up) && strstr($mythread,'<!--o '.$up.'-->'))
    {
        preg_match("/<\!--(.*)-->\s\((.*)\)/",$mythread,$matches);
        $number_of_replies=$matches[2];$number_of_replies--;
        $newthreads.= '<!--o '.$up.'--> ('.$number_of_replies.")\n";
        continue;
    }
    elseif (strstr($mythread,'<!--z '.$num.'-->')) {$keep = 'NO'; continue;}
    elseif (strstr($mythread,'<!--k '.$num.'-->')) {$keep = 'YES'; continue;}
    elseif ($keep == 'NO') {continue;}
    else {$newthreads.=$mythread;}
}

$fp = fopen('threads.txt','wb') or problem("Couldn't open links file (threads.txt) for writing! Please CHMOD it to 666 (rw-rw-rw)!");
if (flock($fp, LOCK_EX)) {
    fputs($fp,$newthreads);
    flock($fp, LOCK_UN);
} else {
    echo "Error locking a file, please try again later!";
}
fclose($fp);

// Delete input from upper file if any
$upfile="msg/$up.$settings[extension]";
if(!empty($up) && file_exists($upfile)) {
    $threads = file($upfile);
    $newthreads='';
    foreach ($threads as $mythread) {
        if (strstr($mythread,'<!--s '.$num.'-->')) {continue;}
        else {$newthreads.=$mythread;}
    }
    $fp = fopen($upfile,"wb") or problem("Couldn't open file $upfile for writing! Please CHMOD it to 666 (rw-rw-rw)!");
    if (flock($fp, LOCK_EX)) {
        fputs($fp,$newthreads);
        flock($fp, LOCK_UN);
    } else {
        echo "Error locking a file, please try again later!";
    }
    fclose($fp);
}
?>
<hr>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p align="center"><b>Selected post and all replies to it were successfully removed!</b></p>
<p align="center"><a href="<?php echo($settings[mboard_url]); ?>/mboard.php">Click here to continue</a></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<?php
printDownHTML();
exit();
}

function confirmDelete($num,$up) {
global $settings;
?>
<hr>
<p>&nbsp;</p>
<p>&nbsp;</p>
<form action="<?php echo($settings[mboard_url]); ?>/mboard.php" method="POST"><input type="hidden" name="a" value="confirmdelete">
<input type="hidden" name="num" value="<?php echo($num); ?>"><input type="hidden" name="up" value="<?php echo($up); ?>">
<p align="center"><b>Please enter your administration password:</b><br>
<input type="password" name="pass" size="20"></p>
<p align="center"><b>Are you sure you want to delete this post and all replies to it? This action cannot be undone!</b></p>
<p align="center"><input type="submit" class="btn btn-sm btn-danger" value="YES, delete this entry and replies to it">&nbsp;&nbsp;<a class="btn btn-sm btn-default" href="<?php echo($settings[mboard_url]); ?>/mboard.php">NO, I changed my mind</a></p>
</form>
<p>&nbsp;</p>
<p>&nbsp;</p>
<?php
printDownHTML();
exit();
}

function styledText($strText)
{
$strText = preg_replace("/\[B\](.*?)\[\/B\]/i","<B>$1</B>",$strText);
$strText = preg_replace("/\[I\](.*?)\[\/I\]/i","<I>$1</I>",$strText);
$strText = preg_replace("/\[U\](.*?)\[\/U\]/i","<U>$1</U>",$strText);
return($strText);
}

function MakeUrl($strUrl)
{
$strText = ' ' . $strUrl;
$strText = preg_replace("#(^|[\n ])([\w]+?://[^ \"\n\r\t<]*)#is", "$1<a href=\"$2\" target=\"_blank\" rel=\"nofollow\">$2</a>", $strText);
$strText = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r<]*)#is", "$1<a href=\"http://$2\" target=\"_blank\" rel=\"nofollow\">$2</a>", $strText);
$strText = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "$1<a href=\"mailto&#58;$2&#64;$3\" rel=\"nofollow\">$2&#64;$3</a>", $strText);
$strText = substr($strText, 1);
return($strText);
}

function processsmileys($text) {
$text = str_replace(':)','<img src="../images/icon_smile.gif" border="0" alt="">',$text);
$text = str_replace(':(','<img src="../images/icon_frown.gif" border="0" alt="">',$text);
$text = str_replace(':D','<img src="../images/icon_biggrin.gif" border="0" alt="">',$text);
$text = str_replace(';)','<img src="../images/icon_wink.gif" border="0" alt="">',$text);
$text = preg_replace("/\:o/i",'<img src="../images/icon_redface.gif" border="0" alt="">',$text);
$text = preg_replace("/\:p/i",'<img src="../images/icon_razz.gif" border="0" alt="">',$text);
$text = str_replace(':cool:','<img src="../images/icon_cool.gif" border="0" alt="">',$text);
$text = str_replace(':rolleyes:','<img src="../images/icon_rolleyes.gif" border="0" alt="">',$text);
$text = str_replace(':mad:','<img src="../images/icon_mad.gif" border="0" alt="">',$text);
$text = str_replace(':eek:','<img src="../images/icon_eek.gif" border="0" alt="">',$text);
$text = str_replace(':clap:','<img src="../images/yelclap.gif" border="0" alt="">',$text);
$text = str_replace(':bonk:','<img src="../images/bonk.gif" border="0" alt="">',$text);
$text = str_replace(':chased:','<img src="../images/chased.gif" border="0" alt="">',$text);
$text = str_replace(':crazy:','<img src="../images/crazy.gif" border="0" alt="">',$text);
$text = str_replace(':cry:','<img src="../images/cry.gif" border="0" alt="">',$text);
$text = str_replace(':curse:','<img src="../images/curse.gif" border="0" alt="">',$text);
$text = str_replace(':err:','<img src="../images/errr.gif" border="0" alt="">',$text);
$text = str_replace(':livid:','<img src="../images/livid.gif" border="0" alt="">',$text);
$text = str_replace(':rotflol:','<img src="../images/rotflol.gif" border="0" alt="">',$text);
$text = str_replace(':love:','<img src="../images/love.gif" border="0" alt="">',$text);
$text = str_replace(':nerd:','<img src="../images/nerd.gif" border="0" alt="">',$text);
$text = str_replace(':nono:','<img src="../images/nono.gif" border="0" alt="">',$text);
$text = str_replace(':smash:','<img src="../images/smash.gif" border="0" alt="">',$text);
$text = str_replace(':thumbsup:','<img src="../images/thumbup.gif" border="0" alt="">',$text);
$text = str_replace(':toast:','<img src="../images/toast.gif" border="0" alt="">',$text);
$text = str_replace(':welcome:','<img src="../images/welcome.gif" border="0" alt="">',$text);
$text = str_replace(':ylsuper:','<img src="../images/ylsuper.gif" border="0" alt="">',$text);
return $text;
}

function problem($myproblem) {
echo '<p>&nbsp;</p>
<p>&nbsp;</p>
<p align="center"><b>Error</b></p>
<p align="center">'.$myproblem.'</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>';
printDownHTML();
exit();
}

function printTopHTML() {
header('Expires: Mon, 26 Jul 2000 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
global $settings;
echo '
<!DOCTYPE html>
<html lang="en">
<head>
<title>Hendy&#39;s Hunches: Message Board</title>
<meta content="text/html; charset=windows-1250">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<link href="'.$settings['mboard_url'].'/style.css" type="text/css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="'.$settings['mboard_url'].'/javascript.js"></script>
<script language="javascript" type="text/javascript" src="'.$settings['website_url'].'js/iframeResizer.contentWindow.min.js"></script>
';
include_once 'header.txt';
}

function printDownHTML() {
global $settings;
/*
echo '<hr width="100%">';

eval(gzinflate(base64_decode('BcFJsqIwAADQ4/T/lQUog1K9UiGAyIwgbrpCmCJhBhlO3+9lX0
R/ip00OUVT9pOgMRP5f2mG2zT7+XPF2nusJ+Nykd19pye65IjVazH1eNP1vXYEAaUPt3bbrVzTPJ8wQd
POhK8mpTXH6Ptm5zcvYKAbcNe8Edqjc29GvIdekEKp1pMI3Pluy1wHfcVBhN8OxC9AcpcL26DvJs8NC0
O4iaEofny/a5IwQCmOudeWmfVNwx07CwuB1nL2Gm6reOyH0EM0xK934sD8wJM6jz+D0KgRB0FWtw5LJb
07gbqtkZT5gZAA+MSj1MMueSwTsZL4zfStOjOsLvDMxYu0GJizhrp0SWiXZueeM3sfZh87kwJkflT6Gl
Es9OIjVBRF47exe5P9Mli7WFtAPr3tQ3E+wLO0wXESfGa4NwXYuciiECPIVdkTcVuT80Mk6FNk3Cyl6z
1VApqul6tk84B7COj6AYGHwoaw33zfPr1DJgSCT9exzrRimxhE59e5ygl1qiMqDWVWcPm+yMQO7FyOsL
pGxtnxy2nR9O1dxN/yOWTbCcYRU8UkrSFdi2jyIn60D1cj5VU29CspWSj92LdbKdwNOS6/slwd9dVU1I
WqzTKsRQG3g0ZtINrlM0obaRS9FE5SMwzHYiASe6fe+yJWyKKyxcpuiVsrpXwQOirm4SM2MMUVADOrRS
dTNc72RRNUlSjDOCcH97Cn5uGK5wyUseTzT1kLtEQF1qHfOfVh5kp89vh5BjOnEaNep4c89eImTm3eNW
ssPFCxa3p3JdqR0pWcHjPIlAmrybFrts40inNPhjuP2VDyjlFKI9NGxSsI6Fy4Q73upvP8nGGIC+N5nQ
0vyXAv8dep9KqyfnViBnJOYnKG4bU/v7+/f/8D')));*/
}

function pj_input($in,$error=0) {
    $in = trim($in);
    if (strlen($in))
    {
        $in = htmlspecialchars($in);
    }
    elseif ($error)
    {
        problem($error);
    }
    return stripslashes($in);
}

function pj_isNumber($in,$error=0) {
    $in = trim($in);
    if (preg_match("/\D/",$in) || $in=="")
    {
        if ($error)
        {

            problem($error);
        }
        else
        {
            return '0';
        }
    }
    return $in;
}

function print_secimg($message=0) {
global $settings;
printTopHTML();
$_SESSION['checked']=$settings['filter_sum'];
?>
<p>&nbsp;</p>
<p>&nbsp;</p>

<p align="center"><b>Anti-SPAM check</b></p>
<div align="center"><center>
<table border="0"><tr>
<td>
<hr>
<form action="<?php echo $settings['mboard_url']; ?>/mboard.php?<?php echo strip_tags(SID); ?>" method="POST" name="form">
<?php
if ($message == 1) {echo '<p align="center"><font color="#FF0000"><b>Please type in the security number</b></font></p>';}
elseif ($message == 2) {echo '<p align="center"><font color="#FF0000"><b>Wrong security number. Please try again</b></font></p>';}
?>
<p>&nbsp;</p>
<p>This is a security check that prevents automated signups of this forum (SPAM).
Please enter the security number displayed below into the input field and click
the continue button.</p>
<p>&nbsp;</p>
<p>Security number: <b><?php
if ($settings['autosubmit']==1) {
    echo '<img src="print_sec_img.php" width="100" height="20" alt="Security image" border="1">';
} elseif ($settings['autosubmit']==2) {
    echo $_SESSION['secnum'];
}
?></b><br>
Please type in the security number displayed above:
<input type="text" size="7" name="secnumber" maxlength="5"></p>
<p>&nbsp;
<?php
foreach ($_POST as $k=>$v) {
    if ($k == 'secnumber') {continue;}
    echo '<input type="hidden" name="'.htmlspecialchars($k).'" value="'.htmlspecialchars(stripslashes($v)).'">';
}
?>
</p>
<p align="center"><input type="submit" value=" Continue "></p>
<hr>
</form>
</td>
</tr>
</table>

<p>&nbsp;</p>
<p>&nbsp;</p>

<?php
printDownHTML();
exit();
}


function JunkMark($email,$comments) {
    /*
        JunkMark(TM) SPAM filter for MBoard
        v1.0 from November 18 2006
        (c) Copyright 2006 Klemen Stirn. All rights reserved.

        The function returns a number between 0 and 100. Larger numbers mean
        more probability that the message is SPAM. Recommended limit is 60
        (block message if score is 60 or more)

        THIS CODE MAY ONLY BE USED IN THE "MBOARD" SCRIPT FROM PHPJUNKYARD.COM
        AND DERIVATE WORKS OF THE MBOARD SCRIPT.

        THIS CODE MUSTN'T BE USED IN ANY OTHER SCRIPT AND/OR REDISTRIBUTED
        IN ANY MEDIUM WITHOUT THE EXPRESS WRITTEN PERMISSION OF KLEMEN STIRN!
    */
eval(gzinflate(base64_decode('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
H4ZSG4LOHL/O8///7773/+Dw==')));

    return $myscore;
}
?>