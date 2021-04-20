<?php

include ('../../inc/includes.php');

Session::checkLoginUser();

$plugin = new Plugin();

if ($plugin->isActivated("mod")) {

	//if they DID upload a file...
	if($_FILES['photo2']['name'])
	{
		//if no errors...
		if(!$_FILES['photo2']['error'])
		{
			//now is the time to modify the future file name and validate the file
			$new_file_name = strtolower($_FILES['photo2']['name']); //rename file
			
			$info = getimagesize($_FILES['photo2']['tmp_name']);

			if($_FILES['photo2']['size'] > (1024000)) //can't be larger than 1 MB
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
				$target = '../../pics/' . basename($_FILES['photo2']['name']);
				
				move_uploaded_file($_FILES['photo2']['tmp_name'], $target);
				
				rename('../../pics/'.basename($_FILES['photo2']['name']), '../../pics/fd_logo.png');
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
			$message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['photo2']['error'];
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