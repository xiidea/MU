MU
=====
PHP multiple file uploading class. Please use as you wish at your own risk.


Key Features
============
* Easy to integrate
* Supports Single Or Multiple File Input Array
* Supports File Type Validation
 

Current Active Version
======================
v 2.0

 
USES
====
The first parameter of Constructor is essential $f_resource

initiate with just $_FILE object

```php
    $var_ref = new MU($_FILES['file1']);
```
initiate with $_FILE object and a base name for upload file

```php
    $var_ref = new MU($_FILES['file1'],'file');
```
you can also provide a base counting no for multiple upload field. The counter will start from $n

```php
    $var_ref = new MU($_FILES['file1'],'file',$n);
```


TODO
====
* Over write handeler will be added
* File name unifier will be added

Dependencies
============
The Library has no known dependency and should work on any server runs php 

