<!DOCTYPE html SYSTEM 'about:legacy-compat'>
<head>
<meta charset='utf-8'>
</head>
<body>
<h1> Please wait </h1>
<script>  	
    var w = window,
    d = document,
    e = d.documentElement,
    g = d.getElementsByTagName('body')[0],
    x = w.innerWidth || e.clientWidth || g.clientWidth,
    y = w.innerHeight|| e.clientHeight|| g.clientHeight;
<?php 
	$code = $_GET['code'];
	$state = $_GET['state'];
	$txt = '&code='.$code.'&state='.$state;
    echo("txt = \"$txt\"");
?>   
    top.location.href = 'iconesExt.php?width='+x+'&height='+y+txt;
</script>
</body>
</html>
