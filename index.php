<!DOCTYPE html> 
<html>
<head>
<meta charset="utf-8">
<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'></script>
<script type="text/javascript">
// File determination
function Valid(){
	if(document.form.file.value == ""){
		alert('Please select a file.');
		return false;
	}

	re = new RegExp(".*xlz$|.*sdlrpx$|.*wsxz$|.*zip$", "i");
	if(document.form.file.value.search(re) == -1){
		alert('Choose xlz, sdlrpx, wsxz or zip format.');
		return false;
	}
}
// Alert when the specified bytes are exceeded
limit_size = 209715200;
$(function(){
    $('input[type=file]').change(function(){
        if($(this).val()){
            var file = $(this).prop('files')[0];
            file_size = file.size;
        }
		if(limit_size < file_size){
            alert('You cannot upload a file that is larger than 200MB.');
            $(this).val('');
        }
    });
});
</script>
</head>

<body>
<h1>XML-Lint</h1>
<form name="form" enctype="multipart/form-data" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="104857600">
<input name="file" type="file" id="file1" accept=".zip,.sdlrpx,.xlz,.wsxz">
<input type="submit" name="_upload" value="Upload" onclick="return Valid();">
</form>

<hr size="1">
<h2>README</h2>
<li>The types of files that will be accepted as uploads are sdlrpx, xlz, wsxz and zip.</li>
<li>Execute xmllint on the sdlxliff, xml or xlf files in the above file.</li>

<?php
$cwd = getcwd();
$path = './temp';
if (file_exists($path)){
	// not doing
} else{
	mkdir($path, 0777);
}
if (isset($_POST['_upload'])) {
	$filename = $_FILES['file']['name'];
	$folder = date('Ymdhis');
	
	mkdir("$path/$folder", 0777);

	$file_fullpath = "$path/$folder/$filename";

	if (move_uploaded_file($_FILES['file']['tmp_name'], $file_fullpath)) {
		$proc_folder = "$path/$folder";

		chdir($proc_folder);
		shell_exec("unzip \"$filename\"");
		unlink($filename);
		chdir($cwd);

		$files = getFiles($proc_folder);

		// Run xmllint if you have the sdlxliff file
		foreach ($files as $f){
			if ( preg_match('/\.(?:sdlxliff|xlf|xml)$/', $f) ){
				$real_p = realpath($f);
				shell_exec("xmllint --format --huge \"$real_p\" --output \"$real_p\"");
			}
		}

		chdir($proc_folder);
		shell_exec("zip -r \"$filename\" *");

		// Download
		mb_http_output( "pass" ) ;
		header("Content-Type: application/octet-stream");
		header("Content-Transfer-Encoding: Binary");
		header("Content-Length: ".filesize($filename));
		header('Content-Disposition: attachment; filename*=UTF-8\'\'' . $filename);
		ob_end_clean();
		readfile($filename);
		exit;
	} else {
		//Error
		echo 'It could not be uploaded' . '<br />';
	}
}

function getFiles($path) {
	$result = array();
	foreach(glob($path . "/*") as $file) {
		if (is_dir($file)) {
			$result = array_merge($result, getFiles($file));
		}
		$result[] = $file;
	}
	return $result;
}

?>

</body>
</html>