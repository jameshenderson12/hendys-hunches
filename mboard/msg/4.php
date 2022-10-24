
<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Hendy&#39;s Hunches: Message Board</title>
<meta content="text/html; charset=windows-1250">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<META HTTP-EQUIV="Expires" CONTENT="-1">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<link href="http://www.hendyshunches.co.uk/mboard/style.css" type="text/css" rel="stylesheet">
<link href="http://www.hendyshunches.co.uk/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="http://www.hendyshunches.co.uk/mboard/javascript.js"></script>
<script language="javascript" type="text/javascript" src="http://www.hendyshunches.co.uk/js/iframeResizer.contentWindow.min.js"></script>
<!-- Bootstrap core CSS -->
<link href="../css/bootstrap.min.css" rel="stylesheet">
<!--<h3 align="center"></h3>-->

<!--<div align="center"><center>-->
<table border="0" width="100%" style="border-bottom: 1px solid #dff0d8;"><tr>
<td>

<p align="center">
<a href="http://www.hendyshunches.co.uk/mboard/mboard.php">Back to all messages</a></p>
<!--<hr>-->
<p align="center"><b>Re: Welcome</b></p>

<p><a href="http://www.hendyshunches.co.uk/mboard/mboard.php?a=delete&num=4&up=1"><img
src="http://www.hendyshunches.co.uk/mboard/images/delete.gif" width="16" height="14" border="0" alt="Delete this post"></a>
Submitted by Simon Riley   on Fri 15th June 12:15 in reply to <a href="1.php">Welcome</a> posted by James Henderson on Wed 13th June 23:22<br><font class="ip">128.243.2.39</font></p>

<p><b>Message</b>:</p>

<p>Thanks James. Awesome job on the site!</p>

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
<form method=post action="http://www.hendyshunches.co.uk/mboard/mboard.php" name="form" onSubmit="return mboard_checkFields();">
<input type="hidden" name="a" value="reply"><!--<b>Name:</b><br>--><input type="hidden" name="name" size=30 maxlength=30 class="form-control" value="<?php echo $_SESSION["firstname"]." ".$_SESSION["surname"]; ?>">
<!--E-mail (optional):<br><input type=text name="email" size=30 maxlength=50><br>-->
<b>Subject:</b><br><input type="text" name="subject" value="Re: Re: Welcome" size="35" width="100%" maxlength=100 class="form-control"><br>
<b>Message:</b><br><textarea cols="35" rows="3" width="100%" name="message" class="form-control" placeholder="Enter message"></textarea>
<input type="hidden" name="orig_id" value="4">
<input type="hidden" name="orig_name" value="Simon Riley">
<input type="hidden" name="orig_subject" value="Re: Welcome">
<input type="hidden" name="orig_date" value="Fri 15th June 12:15"><br>
<!--
Insert styled text: <a href="Javascript:insertspecial('B')"><b>Bold</b></a> |
<a href="Javascript:insertspecial('I')"><i>Italic</i></a> |
<a href="Javascript:insertspecial('U')"><u>Underlined</u></a><br>
<input type="checkbox" name="nostyled" value="Y"> Disable styled text</p>-->

<p><input type="submit" class="btn btn-sm btn-default" value="Submit reply">
</form>
</td>
</tr></table>
</center></div>
<br><!--<br><hr>-->
