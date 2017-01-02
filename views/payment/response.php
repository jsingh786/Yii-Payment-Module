<h3>IPay88: Payment Result</h3>
<br/>
<?php 
if(count($resultData)> 0){
?>
<b>Transaction Id: </b><?php echo $resultData['TransId'];?><br/>
<?php 
echo "<pre>";
print_r($resultData);
}
else{
	echo "Payment Error";
}
?>


