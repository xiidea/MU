<?php

/* class.mu.php
.---------------------------------------------------------------------------.
|  Software: MU - PHP multiple file uploading class                         |
|   Version: 2.1                                                            |
|   Contact: Roni Kumar Saha,mob:+8801817087873,email:roni.cse@gmail.com	|
|      Info: http://helpful-roni.com			                            |
| ------------------------------------------------------------------------- |
|    Author: Roni Kumar Saha (project administrator)                      |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (GPL)     |
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

/**    User manual **
 * MU - PHP multiple file uploading class
 * Constructor - The first parameter is essential $f_resource
 * $var_ref = new MU($_FILES['file1'])
 * $var_ref = new MU($_FILES['file1'],'file')
 * $var_ref = new MU($_FILES['file1'],'file',$n)            //The counteer will be start from n
 *
 */


class MU
{
    /**
     * upload directory
     *
     * @var string
     */
    private $uploadDir;

    /**
     * array of allowed extensions.
     *
     * @var string array '.txt','.jpeg'
     */
    private $allowedExtensions = '';

    /**
     * array of denied extensions. This will used whenever the allowedExtensions is empty;
     *
     * @var string array '.txt','.jpeg'
     */
    private $deniedExtensions = '';


    /**
     * array of target file names without extension.
     *
     * @var string array '
     */
    private $targetFileNames;

    /////////////////////////////////////////////////
    // PROPERTIES, PRIVATE
    /////////////////////////////////////////////////

    /**
     * File Input Resource
     *
     * @var array|null
     */
    private $fileResource = NULL;

    /**
     * Input field count
     *
     * @var int
     */
    private $fileCount = 0;

    /**
     * Selected file count i.e no of uploaded file
     *
     * @var int
     */
    private $fileSelected = 0;

    /**
     * Uploaded file name array
     *
     * @var array
     */
    private $sourceFileNames = array();

    /**
     * Array to store size of uploaded files
     *
     * @var array
     */
    private $fileSizes = array();

    /**
     * Array to store extensions of uploaded files
     *
     * @var array
     */
    private $extensions = array();

    /**
     * store error no
     *
     * @var null
     */
    private $errorNo = NULL;

    /**
     * array to store all errors occurred on uploading
     *
     * @var array
     */
    private $errors = array('TRUE',
        'extension not allowed',
        'Directory is inaccessible',
        'probably upload attack!',
        'no file to upload'
    );

    /**
     * One time flag variable to use auto detect if the passed value is multiple or not
     *
     * @var bool
     */
    private $isMultiple = TRUE;

    /**
     * A random file name will be used if true, the original file name other wise
     *
     * @var bool
     */
    private $useRandomName = FALSE;

    /**
     * File will be overwrite if true, another name will be used otherwise
     *
     * @var bool
     */
    private $overwrite = FALSE;

    /**
     * Constructor of the library
     *
     * @param string $f_resource the file input resource
     * @param string $base_name  base name for uploaded files
     * @param int    $base_count start count for multiple file upload
     */
    function __construct($f_resource, $base_name = NULL, $base_count = 0)
    {
        $this->init($f_resource, $base_name, $base_count);
    }


    /**
     * Initialize class
     *
     * @param string $f_resource the file input resource
     * @param string $base_name  base name for uploaded files
     * @param int    $base_count start count for multiple file upload
     */
    function init($f_resource, $base_name = "", $base_count = 0)
    {
        $this->isMultiple = (is_array($f_resource['name']));

        if (isset($f_resource['name'])) {
            $this->fileCount = count($f_resource['name']);
        }

        if ($this->fileCount > 0) {
            if ($this->useRandomName) {
                list($usec, $sec) = explode(' ', microtime());
                $seed = (float)$sec + ((float)$usec * 100000);
                srand($seed);
                $randomValue = rand();
                $base_name = md5($randomValue . date('Y-m-d'));
            }

            for ($i = 0; $i < $this->fileCount; $i++) {
                $base_file_name = ($base_name == "") ? "" : $base_name . "_" . ($i + $base_count);
                if ($this->isMultiple) {
                    $this->fileResource = $f_resource;
                    if ($f_resource['name'][$i] != '') {
                        $this->fileSelected++;
                    }
                    $this->sourceFileNames[$i] = $f_resource['name'][$i];
                    $this->targetFileNames[$i] = $base_file_name;
                    $this->extensions[$i] = strrchr($f_resource['name'][$i], '.');
                    $this->fileSizes[$i] = $f_resource['size'][$i];
                } else {
                    $this->fileResource['name'][0] = $f_resource['name'];
                    $this->fileResource['type'][0] = $f_resource['type'];
                    $this->fileResource['tmp_name'][0] = $f_resource['tmp_name'];
                    $this->fileResource['error'][0] = $f_resource['error'];
                    $this->fileResource['size'][0] = $f_resource['size'];

                    if ($f_resource['name'] != '') {
                        $this->fileSelected++;
                    }
                    $this->sourceFileNames[$i] = $f_resource['name'];
                    $this->targetFileNames[$i] = $base_file_name;
                    $this->extensions[$i] = strrchr($f_resource['name'], '.');
                    $this->fileSizes[$i] = $f_resource['size'];
                }
            }

        }
    }

    /////////////////////////////////////////////////
    // METHODS, VARIABLES
    /////////////////////////////////////////////////

    /**
     * Return private variable values
     *
     * @param string $var_name
     *
     * @return value of the variable
     */
    public function get_value($var_name)
    {
        return $this->$var_name;
    }

    /**
     * Return error description array
     *
     * @return array
     */
    function mu_error()
    { //file index
        if (is_array($this->errorNo)) {
            $errors_array = array();
            foreach ($this->errorNo as $value) { //Start extract loop of valid extensions
                $errors_array[] = $this->errors[$value];
            }

            return $errors_array;
        } elseif ($this->errorNo == '') {
            return 'TRUE';
        } else {
            return $this->errors[$this->errorNo];
        }
    }

    /**
     * Return coma seperated successfully uploaded file names
     *
     * @return string
     */
    function uploaded_files()
    {
        $fileName = "";
        $j = 0;
        for ($i = 0; $i < $this->fileCount; $i++) {
            if ($this->errorNo[$i] == 0) {
                if ($j > 0) {
                    $fileName .= ",";
                }
                $fileName .= $this->targetFileNames[$i] . $this->extensions[$i];
                $j++;
            }
        }

        return $fileName;
    }

    /**
     * Return a aray contains all the file details as the following structure
     * $f=$muresource->uploaded_files_details()
     * $f[$i]['name']=physical file name
     * $f[$i]['size']=size
     * $f[$i]['oname']=original file name uploaded by user
     *
     * @return array
     */
    function uploaded_files_details()
    {
        $fileDetails = array();
        $j = 0;
        for ($i = 0; $i < $this->fileCount; $i++) {
            if ($this->errorNo[$i] == 0) {
                $fileDetails[$j]['name'] = $this->targetFileNames[$i] . $this->extensions[$i];
                $fileDetails[$j]['size'] = $this->fileSizes[$i];
                $fileDetails[$j]['oname'] = $this->sourceFileNames[$i];
                $j++;
            }
        }

        return $fileDetails;
    }

    /////////////////////////////////////////////////
    // METHODS, FILE UPLOADING
    /////////////////////////////////////////////////

    private function unifyFileName($fname = "", $upDir = "", $ext = "")
    {
        if ($fname == "") {
            return "";
        }
        $path_parts = pathinfo($fname);

        if (isset($path_parts['extension']) && $path_parts['extension'] != "") {
            $ext = $path_parts['extension']; //Try to get path from file name
        }

        $fileName_a = $path_parts['filename'];

        $upDir = rtrim($upDir, '/\\');

        $new_name = $fileName_a;

        $count = 1;
        while (file_exists($upDir . DIRECTORY_SEPARATOR . $new_name . "." . $ext)) {
            $new_name = $fileName_a . "_$count";
            $count++;
        }

        return $new_name; //. ".".$ext;
    }

    /**
     * Upload a file with a target name. If the file is
     * not upload successfully then it returns error number.  Use the mu_error()
     * method variable to view description of the error.
     *
     * @param string $temp_file_name
     * @param string $file_name  name of file to upload
     * @param string $extension  file extension
     * @param string $upload_dir the upload directory
     *
     * @return int 0 on success any value >0 indicate some error
     */
    function upload_file($temp_file_name, $file_name, $extension, $upload_dir)
    {
        $ext_array = $this->allowedExtensions;
        $deny_ext_array = $this->deniedExtensions;

        $upload_dir = $this->addDirectorySeparator($upload_dir);

        $valid_extension = $this->isValidExtension($extension, $ext_array, $deny_ext_array);

        if ($valid_extension != "TRUE") {
            return 1;
        } else {
            if (is_uploaded_file($temp_file_name)) {
                if (@move_uploaded_file($temp_file_name, $upload_dir . $file_name . $extension)) {
                    return 0; //SUCCESSFUL
                } else {
                    return 2; //DIRECTORY INACCESSIBLE
                }
            } else {
                return 3; //FAILED probably upload attach
            }
        }
    }

    //UPLOAD FUNCTION

    /**
     * Upload all files in file resource and generate the error_no variable. if files
     * not upload successfully then it returns 0. upon successful upload returns 1
     * use mu_error method variable to view description of the error.
     *
     * @return int
     */
    function upload_files()
    {
        $success_value = 1;
        if ($this->fileSelected == 0) {
            $this->errorNo = 4;
            $success_value = 0;
        } else {
            for ($i = 0; $i < $this->fileCount; $i++) {
                if ($this->fileResource['name'][$i] != '') {
                    if ($this->targetFileNames[$i] == "") {
                        $this->targetFileNames[$i] = $this->sourceFileNames[$i];
                    }

                    if (!$this->overwrite) {
                        $this->targetFileNames[$i] = $this->unifyFileName($this->targetFileNames[$i], $this->uploadDir, $this->extensions[$i]);
                    }
                    $this->errorNo[$i] = $this->upload_file(
                        $this->fileResource['tmp_name'][$i],
                        $this->targetFileNames[$i],
                        $this->extensions[$i],
                        $this->uploadDir);
                } else {
                    $this->errorNo[$i] = 4;
                }
                if ($this->errorNo[$i] != 0) {
                    $success_value = 0;
                }
            }
        }

        return $success_value;
    }

    /**
     * @param string $allowed_extensions
     *
     * @return $this
     */
    public function setAllowedExtensions($allowed_extensions)
    {
        $this->allowedExtensions = $allowed_extensions;

        return $this;
    }

    /**
     * @param string $uploadDir
     *
     * @return $this
     */
    public function setUploadDir($uploadDir)
    {
        $this->uploadDir = $uploadDir;

        return $this;
    }

    /**
     * @param string $deniedExtensions
     *
     * @return $this
     */
    public function setDeniedExtensions($deniedExtensions)
    {
        $this->deniedExtensions = $deniedExtensions;

        return $this;
    }

    /**
     * @param $upload_dir
     *
     * @return string
     */
    private function addDirectorySeparator($upload_dir)
    {
        $last_slash = substr($upload_dir, strlen($upload_dir) - 1, 1); //Get Last Character

        if ($last_slash <> DIRECTORY_SEPARATOR) { //Check to see if the last character is a slash
            $upload_dir = $upload_dir . DIRECTORY_SEPARATOR;

            return $upload_dir; //Add a backslash if not present
        }

        return $upload_dir;
    }

    /**
     * @param $extension
     * @param $ext_array
     * @param $deny_ext_array
     *
     * @return bool|string
     */
    private function isValidExtension($extension, $ext_array, $deny_ext_array)
    {
        $extension = strtolower($extension);

        if (empty($ext_array)) {

            if (is_array($deny_ext_array) && !empty($deny_ext_array)) {
                $deny_ext_array = array_map('strtolower', $deny_ext_array);

                return !in_array($extension, $deny_ext_array);
            }

            return TRUE;

        } elseif (is_array($ext_array)) {
            $ext_array = array_map('strtolower', $ext_array);

            return in_array($extension, $ext_array);
        } else {
            return FALSE;
        }

    }

    /**
     * @return int
     */
    public function getFileSelected()
    {
        return $this->fileSelected;
    }

    /**
     * @param boolean $useRandomName
     *
     * @return $this
     */
    public function setUseRandomName($useRandomName)
    {
        $this->useRandomName = $useRandomName;

        return $this;
    }

    /**
     * @param boolean $overwrite
     *
     * @return $this
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = $overwrite;

        return $this;
    }
}
