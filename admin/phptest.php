<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>

<h3>Array Sort Test</h3>
<?php
$arr = array(5, 2, 1, 4, 3);

function insertionSort(&$arr){
   for($i=0;$i<count($arr);$i++){
      $val = $arr[$i];
      $j = $i-1;
      while($j>=0 && $arr[$j] > $val){
         $arr[$j+1] = $arr[$j];
         $j--;
      }
      $arr[$j+1] = $val;
   }
}
insertionSort($arr);
foreach($arr as $value) {
  print $value." ";
}
?>

</body>
</html>