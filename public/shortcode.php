
<form class="ajax-form" name="referral-registration-form" id="referral-registration-form">
    <?php  wp_nonce_field( 'referral_save', 'referral_name' ) ?>
    <h2><?php _e('Create new account') ?></h2>

    <div class="form-group">
        <label for="register_user_first_name"><?php _e('First Name', 'textdomain') ?></label>
        <input type="text" class="form-control" id="register_user_first_name" name="user_first_name"  />
        <span class="error register_user_first_name"></span>
    </div>
    <div class="form-group">
        <label for="register_user_last_name"><?php _e('Last Name', 'textdomain') ?></label>
        <input type="text" class="form-control" id="register_user_last_name" name="user_last_name"  />
        <span class="error register_user_last_name"></span>
    </div>

    <div class="form-group">
        <label for="register_user_email"><?php _e('Your Email', 'textdomain') ?></label>
        <input type="email" class="form-control" id="register_user_email" name="user_email"  />
        <span class="error register_user_email"></span>
    </div>
    <div class="form-group">
        <label for="register_user_password"><?php _e('Your Password', 'textdomain') ?></label>
        <input type="password" class="form-control" id="register_user_password" name="user_password"  />
        <span class="error register_user_password"></span>
    </div>

    <div class="form-group">
        <label for="register_user_referral_code"><?php _e('Referral Code', 'textdomain') ?></label>
        <input type="text" class="form-control" id="register_user_referral_code" name="user_referral_code"  />
        <span class="error register_user_referral_code"></span>
    </div>

    <div class="checkbox">
        <label>
            <input type="checkbox" name="accept_terms" > <?php _e('Accept Terms & Conditions') ?>
            <span class="error register_accept_terms"></span>
        </label>
    </div>
    <!-- <input type="hidden" name="action" value="send_register_form"> -->
    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block"><?php _e('Register', 'textdomain') ?></button>
    </div>

    <div class="form-group">
        <div class="ajax-response"></div>
    </div>
</form>
