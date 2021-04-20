<?php

include ('../../inc/includes.php');

Session::checkLoginUser();

$plugin = new Plugin();

if ($plugin->isActivated("mod")) {

	//if they DID upload a file...
	if($_FILES['photo']['name'])
	{
		//if no errors...
		if(!$_FILES['photo']['error'])
		{
			//now is the time to modify the future file name and validate the file
			$new_file_name = strtolower($_FILES['photo']['name']); //rename file
			
			$info = getimagesize($_FILES['photo']['tmp_name']);

			if($_FILES['photo']['size'] > (1024000)) //can't be larger than 1 MB
			{
				$valid_file = false;
				$message = 'Oops!  Your file\'s size is to large.';
			}
			elseif($info === false) {	
				$valid_file = false;						
   			die("Unable to determine image type of uploaded file");
			}
			else { $valid_file = true; }
			
			//if the file has passed the test
			if($valid_file)
			{
				//move it to where we want it to be
				$currentdir = getcwd();
				$target = '../../pics/' . basename($_FILES['photo']['name']);
				
				move_uploaded_file($_FILES['photo']['tmp_name'], $target);
				
				rename('../../pics/'.basename($_FILES['photo']['name']), '../../pics/login_logo_glpi.png');
				$message = 'Congratulations!  Your file was accepted.';
				header('Location: ../../plugins/mod/config.php ');
				//echo $message;
			}
		}
		//if there is an error...
		else
		{
			//set that to be the returned message
			header('Location: ../../plugins/mod/config.php ');
			$message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['photo']['error'];
			echo $message;
		}
	}
	else
		{
			//set that to be the returned message
			header('Location: ../../plugins/mod/config.php ');			
		}
}

?>
