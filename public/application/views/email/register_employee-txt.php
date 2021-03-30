Hi<?php if (strlen($username) > 0) { ?> <?php echo $username; ?><?php } ?>,

You have a new account at Minical!
 
To get started, please activate your account and choose a 
password by following the link below.
 
Activate your account: <?php echo site_url('/auth/reset_password/'.$user_id.'/'.$new_pass_key); ?>

If you have any questions, please contact support@minical.io.

-- 
Thanks!
 
-The Minical Team-
www.minical.io