<?php
	/*
	# Copyright 2012 NodeSocket, LLC
	#
	# Licensed under the Apache License, Version 2.0 (the "License");
	# you may not use this file except in compliance with the License.
	# You may obtain a copy of the License at
	#
	# http://www.apache.org/licenses/LICENSE-2.0
	#
	# Unless required by applicable law or agreed to in writing, software
	# distributed under the License is distributed on an "AS IS" BASIS,
	# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	# See the License for the specific language governing permissions and
	# limitations under the License.
	*/
		
	($_SERVER['SCRIPT_NAME'] !== "/controller.php") ? require_once(__DIR__ . "/classes/Requires.php") : Links::$pretty = true;
	
	Functions::check_required_parameters(array($_GET['param1']));
	
	$file = null;
	MongoConnection::connect();
	MongoConnection::grid_fs();
	$results = MongoConnection::grid_fs_find(array("_id" => new MongoId($_GET['param1'])));
	MongoConnection::close();
	
	foreach($results as $result) {
		$file = $result->file;
		$file['data'] = $result->getResource();
	}
	
	if(empty($file)) {
		Error::halt(404, 'not found', 'File \'' . $_GET['param1'] . '\' does not exist.');
	}
	
	$content = null;
	while (!feof($file['data'])) {
		$content .= fread($file['data'], 8192);
	}
		
	if(!empty($file['type'])) {
		header("Content-Type: " . $file['type']);
	}
    
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=\"" . $file['real_filename'] . "\"");
    
    if(strpos($file['type'], 'text') !== false || $file['type'] === "application/json") {
    	header("Content-Transfer-Encoding: quoted-printable");
    } else {
    	header("Content-Transfer-Encoding: binary");
    }
    
    header("Content-Length: " . $file['length']);
	
	echo $content;
?> 