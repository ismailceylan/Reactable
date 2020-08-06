<div data-toggle="reactable" class="reactable"
	 data-id="<?php echo $subject_id; ?>"
	 <?php if( $this->value ): ?>data-value="<?php echo $value; ?>"<?php endif; ?>
	 <?php if( $this->service_uri ): ?>data-service-uri="<?php echo $service_uri; ?>"<?php endif; ?>
	 <?php if( $this->update ): ?>data-update="<?php echo $update; ?>"<?php endif; ?>
	 <?php if( $this->volume ): ?>data-volume="<?php echo $volume; ?>"<?php endif; ?>
	 <?php if( $this->mobile_direction ): ?>data-mobile-direction="<?php echo $mobile_direction; ?>"<?php endif; ?>
	 <?php if( $this->position ): ?>data-position="<?php echo $position; ?>"<?php endif; ?>
	 <?php if( $this->sound ): ?>data-sound="<?php echo $sound; ?>"<?php endif; ?>
	 <?php if( $this->feelings ): ?>data-feelings='<?php echo json_encode( $this->feelings ); ?>'<?php endif; ?>
	 <?php if( $this->subject_type ): ?>data-subject-type='<?php echo $this->subject_type; ?>'<?php endif; ?>
	 <?php if( $this->placeholder ): ?>data-placeholder='<?php echo $this->placeholder; ?>'<?php endif; ?>
	 <?php if( $this->items ): ?>data-items='<?php echo $this->items; ?>'<?php endif; ?>
>
	<div class="summary"></div>
	<span class="feelings"></span>
	<a href="react/ping?id=ID-1&reaction=like" class="label">Like</a>
</div>
