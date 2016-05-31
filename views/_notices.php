<?php if ( !empty($this->_messages) ) :?>
	<?php foreach ( $this->_messages as $message ):?>
		<div  class="updated notice is-dismissible below-h2 ">
			<p><?php echo $message; ?></p>
			<?php echo ($wp_version < 4.2 ? '' : '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __('Dismiss this notice.', \JustCustomFields::TEXTDOMAIN) . '</span></button>') ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>

<?php if ( !empty($this->_errors) ) :?>
	<?php foreach ( $this->_errors as $error ):?>
		<div  class="updated notice error is-dismissible below-h2">
			<p><?php echo $error; ?></p>
			<?php echo ($wp_version < 4.2 ? '' : '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __('Dismiss this notice.', \JustCustomFields::TEXTDOMAIN) . '</span></button>'); ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>