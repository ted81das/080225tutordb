<div class="bbpc-forum-information-wrapper"> 
    <div class="bbpc-section-heading"> <?php esc_html_e( 'Basic', 'bbp-core' ); ?> </div>
    <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
            <?php esc_html_e( 'Title:', 'bbp-core-pro' ); ?>
        </label>
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/>
    </p>

    <div class="bbpc-section-heading"> <?php esc_html_e( 'Information to show', 'bbp-core' ); ?> </div>

    <p class="bbpc-info-show">
        <span class="bbpc-info-show-item">
            <input class="checkbox" checked type="checkbox" <?php if ( ! empty( $instance['show_topics_count'] ) ){ checked( $instance['show_topics_count'], 'on' ); } ?> id="<?php echo $this->get_field_id('show_topics'); ?>" name="<?php echo $this->get_field_name( 'show_topics_count' ); ?>" />
            <label for="<?php echo $this->get_field_id('show_topics'); ?>"> <?php esc_html_e( 'Show Topics Count', 'bbp-core' ); ?> </label>
            
            <input class="checkbox" checked type="checkbox" <?php if ( ! empty( $instance['show_replies_count'] ) ){ checked( $instance['show_replies_count'], 'on' ); } ?> id="<?php echo $this->get_field_id('show_replies'); ?>" name="<?php echo $this->get_field_name( 'show_replies_count' ); ?>" />
            <label for="<?php echo $this->get_field_id('show_replies'); ?>"> <?php esc_html_e( 'Show replies count', 'bbp-core' ); ?> </label>

            <input class="checkbox" checked type="checkbox" <?php if ( ! empty( $instance['show_icons'] ) ){ checked( $instance['show_icons'], 'on' ); } ?> id="<?php echo $this->get_field_id('show_icon'); ?>" name="<?php echo $this->get_field_name( 'show_icons' ); ?>" />
            <label for="<?php echo $this->get_field_id('show_icon'); ?>"> <?php esc_html_e( 'Show Icons', 'bbp-core' ); ?> </label>
        </span>
        <span class="bbpc-info-show-item">
            <input class="checkbox" checked type="checkbox" <?php if ( ! empty( $instance['show_last_post_user'] ) ){ checked( $instance['show_last_post_user'], 'on' ); } ?> id="<?php echo $this->get_field_id('show_last_post'); ?>" name="<?php echo $this->get_field_name( 'show_last_post_user' ); ?>" />
            <label for="<?php echo $this->get_field_id('show_last_post'); ?>"> <?php esc_html_e( 'Show last post user', 'bbp-core' ); ?> </label>
            
            <input class="checkbox" checked type="checkbox" <?php if ( ! empty( $instance['show_last_activity'] ) ){ checked( $instance['show_last_activity'], 'on' ); } ?> id="<?php echo $this->get_field_id('show_activity'); ?>" name="<?php echo $this->get_field_name( 'show_last_activity' ); ?>" />
            <label for="<?php echo $this->get_field_id('show_activity'); ?>"> <?php esc_html_e( 'Show last activity', 'bbp-core' ); ?> </label>
        </span>
    </p>

    <div class="bbpc-section-heading"> <?php esc_html_e( 'Actions to show', 'bbp-core' ); ?> </div>
    <p class="bbpc-info-show show-subscribe">        
        <input class="checkbox" checked type="checkbox" <?php if ( ! empty( $instance['show_subscribe'] ) ){ checked( $instance['show_subscribe'], 'on' ); } ?> id="<?php echo $this->get_field_id('show_subscribe_btn'); ?>" name="<?php echo $this->get_field_name( 'show_subscribe' ); ?>" />
        <label for="<?php echo $this->get_field_id('show_subscribe_btn'); ?>"> <?php esc_html_e( 'Show subscribe button', 'bbp-core' ); ?> </label>
    </p>
</div>