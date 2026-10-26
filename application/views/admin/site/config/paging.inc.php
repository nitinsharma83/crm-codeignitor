<?php

	if($reccnt > $pagesize){
		
	$num_pages=$reccnt/$pagesize;

	$PHP_SELF= end(explode("/",$_SERVER['PHP_SELF']));
	
	//$PHP_SELF= $_SERVER['SCRIPT_NAME'];
	//$PHP_SELF= str_replace(".php",".html",$PHP_SELF);
	
	$qry_str=$_SERVER['argv'][0];

	$m=$_REQUEST;
	unset($m['start']);
	unset($m['PHPSESSID']);
	$qry_str=qry_str($m);

	$j=$start/$pagesize-5;

	if($j<0) {
		$j=0;
	}
	$k=$j+10;
	if($k>$num_pages)	{
		$k=$num_pages;
	}
	$j=intval($j);
?>
<span class="black_txt">
<span class="black_txt">

<?php for($i=$j;$i<$k;$i++){
if($i==$j){?><span class="black_txt"><b>PAGE:&nbsp; </b></span><?php }
if(($pagesize*($i))!=$start){?>
<span class="page-numbers" aria-current="page">
<a href="<?php echo $PHP_SELF;?><?php echo $qry_str;?>&start=<?php echo $pagesize*($i);?>" class="txt"><strong><?php echo $i+1;?></strong></a>
</span>
<?php } else{?>
<a href="<?php echo $PHP_SELF;?><?php echo $qry_str;?>&start=<?php echo $pagesize*($i);?>" class="txt"><span class="page-numbers" aria-current="page"><?php echo $i+1;?>&nbsp;</span></a><?php } ?>
<?php }?> 

<!--<a href="<?php echo $PHP_SELF;?><?php echo $qry_str;?>&start=<?php echo $start+$pagesize;?>" class="txt">Next</a>-->

</span>

<?php }?>