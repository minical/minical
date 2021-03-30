Hi<?php if (strlen($username) > 0) { ?> <?php echo $username; ?><?php } ?>,

Forgot your password, huh? No big deal.
To create a new password, just follow this link:

<?php echo site_url('/auth/reset_password/'.$user_id.'/'.$new_pass_key); ?>


You received this email, because it was requested by a <?php echo $site_name; ?> user. This is part of the procedure to create a new password on the system. If you DID NOT request a new password then please ignore this email and your password will remain the same.


Thank you,
The <?php echo $site_name; ?> Team