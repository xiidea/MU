<?php

/* class.mu.php
.---------------------------------------------------------------------------.
|  Software: MU - PHP multiple file uploading class                         |
|   Version: 2.0                                                            |
|   Contact: Roni Kumar Saha,mob:+8801817087873,email:roni.cse@gmail.com	|
|      Info: http://helpful-roni.com			                            |
| ------------------------------------------------------------------------- |
|    Author: Roni Kumar Saha (project admininistrator)                      |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'

/**
 * MU - PHP multiple file uploading class
 * NOTE: Designed for use with PHP version 5 and up
 * @package MU
 * @author Roni Kumar Saha
 */

/**	User manual **
 * MU - PHP multiple file uploading class
 * Constructor - The first parameter is essential $f_resource
 * $var_ref = new MU($_FILES['file1'])
 * $var_ref = new MU($_FILES['file1'],'file')
 * $var_ref = new MU($_FILES['file1'],'file',$n)			//The counteer will be start from n
 * 
 */


class MU{		//MULTIPLE UPLOAD
  /////////////////////////////////////////////////
  // PROPERTIES, PUBLIC
  /////////////////////////////////////////////////

  /**
   * upload directory
   * @var string
   */
	public $uploaddir;

   /**
   * array of allowed extentions.
   * @var string array '.txt','.jpeg'
   */
	public $allowed_extentions='';
	
   /**
   * array of denyed extentions. This will used whenever the allowed_extentions is empty;
   * @var string array '.txt','.jpeg'
   */
	public $denyed_extentions='';
	
	
   /**
   * array of target file names without extention.
   * @var string array '
   */	
	private $target_file_names;

  /////////////////////////////////////////////////
  // PROPERTIES, PRIVATE
  /////////////////////////////////////////////////

    /**
     * File Input Resource
     * @var array|null
     */
    private $file_resource			=null;

    /**
     * Input field count
     * @var int
     */
    private $file_count				=0;

    /**
     * Selected file count i.e no of uploaded file
     * @var int
     */
    private $file_selected			=0;

    /**
     * Uploaded file name array
     * @var array
     */
    private $source_file_names		=array();

    /**
     * Array to store size of uploaded files
     * @var array
     */
    private $file_sizes				=array();

    /**
     * Array to store extentions of uploaded files
     * @var array
     */
    private $extenssions			=array();

    /**
     * store error no
     * @var null
     */
    private $error_no				=null;

    /**
     * array to store all errors occured on uploading
     * @var array
     */
    private $errors					=array('TRUE','extention not allowed','Directory is inaccessible','probably upload attac!','no file to upload');

    /**
     * One time flag variable to use auto detect if the passed value is multiple or not
     * @var bool
     */
    private $isMultiple				=true;

    /**
     * A random file name will be used if true, the original file name other wise
     * @var bool
     */
    private $UseRandomName          =true;

    /**
     * File will be overwrite if true, another name will be used otherwise
     * @var bool
     */
    private $overwrite              =false;

    /**
     * Constructor of the library
     * @param string $f_resource the file input resource
     * @param string $base_name base name for uploaded files
     * @param int $base_count start count for multiple file upload
     */
    function __construct($f_resource,$base_name=null,$base_count=0){
        $this->init($f_resource,$base_name,$base_count);
	}


    /**
     * Initialize class
     * @param string $f_resource the file input resource
     * @param string $base_name base name for uploaded files
     * @param int $base_count start count for multiple file upload
     */
    function init($f_resource,$base_name="",$base_count=0){
        $this->isMultiple=(is_array($f_resource['name']));

        if(isset($f_resource['name'])){
            $this->file_count=count($f_resource['name']);
        }

        if($this->file_count>0){
            if($this->UseRandomName){
                list($usec, $sec) = explode(' ', microtime());
                $seed= (float) $sec + ((float) $usec * 100000);
                srand($seed);
                $randval = rand();
                $base_name=md5($randval.date('Y-m-d'));
            }

            for($i=0; $i<$this->file_count; $i++){
                $base_file_name=($base_name=="")?"":$base_name."_".($i+$base_count);
                if($this->isMultiple){
                    $this->file_resource=$f_resource;
                    if($f_resource['name'][$i]!='')
                        $this->file_selected++;
                    $this->source_file_names[$i]=$f_resource['name'][$i];
                    $this->target_file_names[$i]=$base_file_name;
                    $this->extenssions[$i]=strrchr($f_resource['name'][$i],'.');
                    $this->file_sizes[$i]=$f_resource['size'][$i];
                }
                else
                {
                    $this->file_resource['name'][0]=$f_resource['name'];
                    $this->file_resource['type'][0]=$f_resource['type'];
                    $this->file_resource['tmp_name'][0]=$f_resource['tmp_name'];
                    $this->file_resource['error'][0]=$f_resource['error'];
                    $this->file_resource['size'][0]=$f_resource['size'];

                    if($f_resource['name']!='')
                        $this->file_selected++;
                    $this->source_file_names[$i]=$f_resource['name'];
                    $this->target_file_names[$i]=$base_file_name;
                    $this->extenssions[$i]=strrchr($f_resource['name'],'.');
                    $this->file_sizes[$i]=$f_resource['size'];
                }
            }

        }
    }
  /////////////////////////////////////////////////
  // METHODS, VARIABLES
  /////////////////////////////////////////////////

  /**
   * Return private variable values
   * @param string $var_name
   * @return value of the variable
   */	
	function get_value($var_name){
		return $this->$var_name;
	}
	
   /**
   * Return error description array
   * @return array
   */	
	function mu_error(){			//file index
		if (is_array($this->error_no)) {
			$erorr_array=array();
			foreach ($this->error_no as $key => $value) {	                       //Start extract loop of valid extensions  
				$erorr_array[]=$this->errors[$value];
		    }  
			return $erorr_array;
		}
		elseif($this->error_no=='')
			return 'TRUE';
		else
			return $this->errors[$this->error_no];
	}
	
   /**
   * Return coma seperated successfully uploaded file names
   * @return string
   */  
	function uploaded_files(){
		$fname="";
		$j=0;
		for($i=0; $i<$this->file_count; $i++){
			if($this->error_no[$i]==0){
				if($j>0)
					$fname.=",";
				$fname.=$this->target_file_names[$i].$this->extenssions[$i];
				$j++;
			}
		}
		return $fname;
	}
	
   /**
   * Return a aray contains all the file details as the following structure
   * $f=$muresource->uploaded_files_details()
   * $f[$i]['name']=physical file name
   * $f[$i]['size']=size
   * $f[$i]['oname']=original file name uploaded by user
   * @return array
   */  
	function uploaded_files_details(){
		$fdetails=array();
		$j=0;
		for($i=0; $i<$this->file_count; $i++){
			if($this->error_no[$i]==0){
				$fdetails[$j]['name']=$this->target_file_names[$i].$this->extenssions[$i];
				$fdetails[$j]['size']=$this->file_sizes[$i];
				$fdetails[$j]['oname']=$this->source_file_names[$i];
				$j++;
			}
		}
		return $fdetails;
	}
  /////////////////////////////////////////////////
  // METHODS, FILE UPLOADING
  /////////////////////////////////////////////////

    private function unifyFileName($fname="",$updir="",$ext=""){
        if($fname==""){
            return "";
        }
        $path_parts = pathinfo($fname);

        if(isset($path_parts['extension']) && $path_parts['extension']!=""){
            $ext=$path_parts['extension'];  //Try to get path from file name
        }

        $fileName_a = $path_parts['filename'];

        $updir=rtrim($updir,'/\\');

        $new_name=$fileName_a;

        $count=1;
        while (file_exists($updir . DIRECTORY_SEPARATOR . $new_name . ".".$ext)){
            $new_name=$fileName_a."_$count";
            $count++;
        }
        return $new_name. ".".$ext;
    }

    /**
     * Upload a file with a target name. If the file is
     * not upload successfully then it returns error number.  Use the mu_error()
     * method variable to view description of the error.
     * @param string $temp_file_name
     * @param string $file_name name of file to upload
     * @param $extension file extention
     * @param $upload_dir the upload directory
     * @return int 0 on success any valu >0 indicate some error
     */
	function upload_file($temp_file_name,$file_name, $extension ,$upload_dir) {  
		$ext_array=$this->allowed_extentions;								//Assign allowed extentions
		$deny_ext_array=$this->denyed_extentions;								//Assign denyed extentions
	    //Figure if last character for the upload directory is a back slash  
	    $last_slash = substr($upload_dir,strlen($upload_dir)-1,1);          //Get Last Character  
	    if ($last_slash <> DIRECTORY_SEPARATOR) {                                           //Check to see if the last character is a slash  
	        $upload_dir = $upload_dir.DIRECTORY_SEPARATOR;                                  //Add a backslash if not present  
	    } 
	    
		//Validate Extension  
		if ($ext_array=='') {												
			$valid_extension = "TRUE";										//Allowed all file
			if($deny_ext_array!='')											
			{																//Excepet the denyed listed extentions
				foreach ($deny_ext_array as $key => $value) {	            //Start extract loop of valid extensions  
					if (strtolower($value) == strtolower($extension)) {  	//If extension is equal to any in the array  
						$valid_extension = "FALSE";
						break;                                        		//Set valid extension to TRUE  
					}
				}
			}
		}
		else {
			$valid_extension=FALSE;
			foreach ($ext_array as $key => $value) {	                       //Start extract loop of valid extensions  
		        if (strtolower($value) == strtolower($extension)) {  						//If extension is equal to any in the array  
		            $valid_extension = "TRUE";
		            break;                                        //Set valid extension to TRUE  
		        }
		    }
		}  
	    
		if ($valid_extension!="TRUE") {  
			return 1;							//EXTENTION Invalid Extention
	    }  
	    else {  
	        if (is_uploaded_file($temp_file_name)) {  
	            if (@move_uploaded_file($temp_file_name,$upload_dir.$file_name.$extension)) {  
					return 0; 		//SUCCESSFUL
	            } else {  
	                return 2; 		//DIRECTORY INACCESSIBLE
	            }  
	        } else {  
	            return 3; 			//FAILED probably upload attac
	        }  
	 	}  
 	}//UPLOAD FUNCTION

  /**
   * Upload all files in file resource and generate the error_no variable. if files
   * not upload successfully then it returns 0. upon successful upload returns 1
   * use mu_error method variable to view description of the error.
   * @return int
   */	
	function upload_files(){
		$success_value=1;
		if($this->file_selected==0){
			$this->error_no=4;
			$success_value=0;	
		}
		else{
			for($i=0; $i<$this->file_count; $i++){
				if($this->file_resource['name'][$i]!=''){
                    if($this->target_file_names[$i]==""){
                        $this->target_file_names[$i]=$this->source_file_names[$i];
                    }

                    if(!$this->overwrite){
                        $this->target_file_names[$i]=$this->unifyFileName($this->target_file_names[$i],$this->uploaddir,$this->extenssions[$i]);
                    }
					$this->error_no[$i]=$this->upload_file($this->file_resource['tmp_name'][$i],$this->target_file_names[$i],$this->extenssions[$i],$this->uploaddir);
                }else{
					$this->error_no[$i]=4;
                }
				if($this->error_no[$i]!=0)
					$success_value=0;
			}
		}
		return $success_value;
	}	
}
