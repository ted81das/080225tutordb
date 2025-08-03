<fieldset class="bbp-form">
	<legend><?php _e( 'Upload Attachments', 'bbp-core' ); ?></legend>
	<div class="bbp-template-notice">
		<p>
		<?php

			$size = $file_size < 1024 ? $file_size . ' KB' : floor( $file_size / 1024 ) . ' MB';

			printf( __( 'Maximum file size allowed is %s.', 'bbp-core' ), $size );

		?>
			</p>
	</div>
	<p class="bbp-attachments-form">
		<label for="bbp_topic_tags">
			<?php _e( 'Attachments', 'bbp-core' ); ?>:
		</label><br/>
		<input type="file" size="40" name="bbpc_attachment[]"><br/>
		<a class="d4p-attachment-addfile" href="#"><?php _e( 'Add another file', 'bbp-core' ); ?></a>
	</p>
</fieldset>
