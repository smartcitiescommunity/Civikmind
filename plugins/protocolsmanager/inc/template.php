<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="charset=utf-8" />
<style type="text/css">
body
{
	font-size: <?php echo $fontsize; ?>;
}
footer 
{ 
	position: fixed; bottom: -10px; left: 0px; right: 0px; height: 50px; text-align: center; font-size: 8pt; color: gray;
}
#items
{
	border: solid 0.5px black; width:100%; position: relative; table-layout: auto;
}

#items th
{
	border: solid 0.5px black; padding: 2px;
}

#items td
{
	border: solid 0.5px black; padding: 2px;
	<?php 
	if ($breakword == 1) {
		echo 'word-wrap: break-word;';
	}
	?>
}

</style>
</head>
<body>
<?php 
	if ($islogo == 1) {

		echo '<img src="';
		echo $logo;
		echo '" style="display: block; width: 100%; height: 20mm;">';

	}
?>
	<table style="border: none; width: 100%;">
		<td style="height: 8mm; width: 70%;"><?php echo $prot_num; echo "-"; echo date('dmY'); ?></td>
		<td style="height: 8mm; width: 30%; text-align: right;"><?php echo $city." "; $date=date('d.m.Y'); echo $date; ?></td>
	</table>
	
	<table style="border:none; width: 100%;">
		<tr>
			<td style="border:none; text-align: center; width: 100%; font-size: 15pt;  height: 15mm;">
			<?php echo $title; ?>
			</td>
		</tr>
	</table>
<br>
<table>
	<tr>
		<td style="weight:100%;">
<?php echo $upper_content; ?>
		</td>
	</tr>
</table>
<br>
<table id="items" cellspacing="0">
<?php 
	//if no comments, there is no comments column
	if (empty(array_filter($comments))) {
		
		//if serial and inventory in different columns
		if ($serial_mode == 1) {
			
			echo '<tr>
				<th></th>
				<th>'; 
				echo __('Type');
				echo "</th><th>"; 
				echo __('Model');
				echo "</th><th>";
				echo __('Name');				
				echo "</th><th>"; 
				echo __('Serial number'); 
				echo "</th><th>";
				echo __('Inventory number'); 
				echo "</th>
			</tr>";
			
			$lp = 1;
			foreach ($number as $key) {
				if (isset($type_name[$key])) {
				echo '<tr><td>'. $lp . '</td>';
				echo '<td>' . $type_name[$key] .'</td>';
				echo '<td>'. $man_name[$key] .' '. $mod_name[$key]. '</td>';
				echo '<td>' .$item_name[$key] .'</td>';
				echo '<td>'. $serial[$key] .'</td>';
				echo '<td>'. $otherserial[$key] .'</td></tr>';
				}
				$lp++;
			}
		}

		//if serial and inventory in one column
		if ($serial_mode == 2) {
			
			echo "<tr>
				<th></th>
				<th>"; 
				echo __('Type');
				echo "</th><th>"; 
				echo __('Model'); 
				echo "</th><th>"; 
				echo __('Name'); 
				echo "</th><th>";
				echo __('Serial number');
				echo "</th>
			</tr>";
			
			$lp = 1;
			foreach ($number as $key) {
				if (empty($serial[$key])) {
					$serial[$key]=$otherserial[$key];
				} //if no serial, get inventory number
				if (isset($type_name[$key])){
				echo '<tr><td>'. $lp .'</td>';
				echo '<td>'. $type_name[$key] .'</td>';
				echo '<td>'. $man_name[$key] .' '. $mod_name[$key]. '</td>';
				echo '<td>'. $item_name[$key] .'</td>';
				echo '<td>'. $serial[$key] .'</td></tr>';
				}
				$lp++;
			}
		}

	}
	else {
		//if at least one comment, there will be comment column
		if ($serial_mode == 1) {
			
			echo "<tr>
				<th></th>
				<th>"; 
				echo __('Type');
				echo "</th><th>"; 
				echo __('Model'); 
				echo "</th><th>"; 
				echo __('Name'); 
				echo "</th><th>"; 
				echo __('Serial number'); 
				echo "</th><th>";
				echo __('Inventory number'); 
				echo "</th><th>";
				echo __('Comments'); 
				echo "</th>
			</tr>";
			
			$lp = 1;
			foreach ($number as $key){
				if (isset($type_name[$key])){
				echo '<tr><td>'. $lp . '</td>';
				echo '<td>'. $type_name[$key] .'</td>';
				echo '<td>'. $man_name[$key] .' '. $mod_name[$key]. '</td>';
				echo '<td>'. $item_name[$key]. '</td>';
				echo '<td>' . $serial[$key] .'</td>';
				echo '<td>'. $otherserial[$key] .'</td>';
				echo '<td>'. $comments[$key] .'</td></tr>';
				}
				$lp++;
			}
		}

		if ($serial_mode == 2) {
			
			echo "<tr>
				<th></th>
				<th>"; 
				echo __('Type');
				echo "</th><th>"; 
				echo __('Model'); 
				echo "</th><th>"; 
				echo __('Name'); 
				echo "</th><th>";
				echo __('Serial number'); 
				echo "</th><th>";
				echo __('Comments'); 
				echo "</th>
			</tr>";
			
			$lp = 1;
			foreach ($number as $key){
				if (empty($serial[$key])) {
					$serial[$key]=$otherserial[$key];
				} //if no serial, get inventory number
				if (isset($type_name[$key])){
				echo '<tr><td>'. $lp . '</td>';
				echo '<td>'. $type_name[$key] .'</td>';
				echo '<td>'. $man_name[$key] .' '. $mod_name[$key]. '</td>';
				echo '<td>'. $item_name[$key] .'</td>';
				echo '<td>' . $serial[$key] .'</td>';
				echo '<td>'. $comments[$key] .'</td></tr>';
				}
				$lp++;
			}
		}

	}
		

?>
</table>

<br>

<table>
	<tr>
		<td style="height: 10mm;"></td>
	</tr>
</table>

<table>
	<tr>
		<td style="weight:100%;">
<?php echo $content; ?>
		</td>
	</tr>
</table>

<table>
	<tr>
		<td style="height: 20mm;"></td>
	</tr>
</table>

<table style="border-collapse: collapse; width: 100%;">
	<tr>
		<td style="width:50%; border-bottom: 1px solid black;"><strong><?php echo __('Administrator').":"; ?></strong></td>
		<td style="width:50%; border-bottom: 1px solid black;"><strong><?php echo __('User').":"; ?></strong></td>
	</tr>
	<tr>
		<td style="border: 1px solid black; width:50%; vertical-align:top; height: 20mm">
			<?php echo $author; ?>
		</td>
		<td style="border: 1px solid black; width:50%; vertical-align:top; height: 20mm">
			<?php echo $owner; ?>
		</td>
	</tr>
</table>

<footer>
<?php echo $footer; ?>
</footer>
</body>
</html>