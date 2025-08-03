<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// TODO: We need to get rid of these things
class GDATTDefaults {
	
}

$bbpc_upload_error_messages = [
	__( 'File exceeds allowed file size.', 'bbp-core' ),
	__( 'File not uploaded.', 'bbp-core' ),
	__( 'Upload file size exceeds PHP maximum file size allowed.', 'bbp-core' ),
	__( 'Upload file size exceeds FORM specified file size.', 'bbp-core' ),
	__( 'Upload file only partially uploaded.', 'bbp-core' ),
	__( "Can't write file to the disk.", 'bbp-core' ),
	__( 'Temporary folder for upload is missing.', 'bbp-core' ),
	__( 'Server extension restriction stopped upload.', 'bbp-core' ),
];
