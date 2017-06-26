<?php
/**
 * Notices views
 */



if ( ! empty( $this->_messages ) ) : ?>
	<?php foreach ( $this->_messages as $message ) : ?>
		<div class="updated notice is-dismissible below-h2 ">
			<p><?php echo esc_html( $message ); ?></p>
			<?php echo( $wp_version < 4.2 ? '' : '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'jcf' ) . '</span></button>' ) ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>

<?php if ( ! empty( $this->_errors ) ) : ?>
	<?php foreach ( $this->_errors as $error ) : ?>
		<div class="updated notice error is-dismissible below-h2">
			<p><?php echo esc_html( $error ); ?></p>
			<?php echo( $wp_version < 4.2 ? '' : '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'jcf' ) . '</span></button>' ); ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>