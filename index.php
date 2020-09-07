<html lang="ja">
<head>
<meta charset="utf-8">
</head>
<body>

<h1>XML-Lint</h1>
<form enctype="multipart/form-data" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="104857600">
<input name="file" type="file">
<input type="submit" name="_upload" value="Upload">
</form>

<?php
$cwd = getcwd();
$path = './temp';
if (isset($_POST['_upload'])) {
	$filename = $_FILES['file']['name'];
	$folder = date('Ymdhis');
	mkdir("$path/$folder", 0777);

	$file_fullpath = "$path/$folder/$filename";

	if (move_uploaded_file($_FILES['file']['tmp_name'], $file_fullpath)) {
		// echo $_FILES['file']['name'] . 'をアップロードしました' . '<br />';
		$proc_folder = "$path/$folder";

		chdir($proc_folder);
		shell_exec("unzip $filename");
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