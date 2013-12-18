<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Multiple file uploader demo</title>
</head>
<body>
<?php
if(isset($_POST['submit'])){
	include("../src/class.mu.php");
	$out_put="";
	$obj = new MU($_FILES['myfile']);
	
	$fselected=$obj->getFileSelected();
	if($fselected>0)
	{ 
		$obj
            ->setDeniedExtensions(array(
            ".php",".html",".js",".htm",".htmls",".dhtml",".php3",".phps",".php4",".php5",".asp",".aspx",".htaccess",".vb",".htpasswd"))
            ->setUploadDir(".".DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR)
            ->upload_files();

		$up_error=$obj->mu_error();
		
		$file_name=$obj->uploaded_files();
		$file_details=$obj->uploaded_files_details();

        if($file_name!=''){
			$j=0;
			$error_str='';
			for($fcount=0; $fcount<count($_FILES['myfile']['error']); $fcount++){
					if($_FILES['myfile']['error'][$fcount]==0 && $up_error[$fcount]=='TRUE'){
						//Everything fine
					}
					elseif($_FILES['myfile']['name'][$fcount]!='')
					{
						$error_str.=$_FILES['myfile']['name'][$fcount]." - ".$up_error[$fcount]."\n";
					}
				}
			$file_name_arr=explode(',',$file_name);
			$c=count($file_name_arr);
			if($c>0){
				$out_put.="<h3>uploaded file list</h3>\n<ol>";			
				for($j=0; $j<$c; $j++)
				{
					$oname=urldecode($file_details[$j]['oname']);
					$size=$file_details[$j]['size'];
					$out_put.= "<li>$oname ($size)</li>";
				}
				$out_put.= "</ol>\n";
			}
			$out_put.= "<span style='color:green'><strong>$c file(s) uploaded successfully!</strong></span>\n";
			if($c<$fselected){			//SOME FILE HAVENOT UPLOADED
				$out_put.="<h3>upload error details</h3>\n";
				$out_put.= "<span style='color:red'><strong>$error_str</strong></span>";
			}
		}else{			
			$out_put.= "no file uploaded!\n";
			//ERROR DETAILS
			for($fcount=0; $fcount<count($_FILES['myfile']['name']); $fcount++){
					if($up_error[$fcount]!='TRUE' && $_FILES['myfile']['name'][$fcount]!=''){
						$out_put.= $_FILES['myfile']['name'][$fcount]." - ".$up_error[$fcount]."\n";
					}
				}
		
		}
	}else{
		$out_put.= "No file selected to upload";
	}
	echo nl2br($out_put."\n");
}
?>
<form action="" method="post" name="frm" enctype="multipart/form-data">
<div><label>Selected Multiple File</label></div>
<div><input name="myfile[]" type="file" /></div>
<div><input name="myfile[]" type="file" /></div>
<div><input name="myfile[]" type="file" /></div>
<div><input name="myfile[]" type="file" /></div>
<input name="submit" type="submit" value="upload" />
</form>
</body>
</html>
