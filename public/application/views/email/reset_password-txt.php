Hi<?php if (strlen($username) > 0) { ?> <?php echo $username; ?><?php } ?>,

You have changed your password.
Please, keep it in your records so you don't forget it.
<?php if (strlen($username) > 0) { ?>

Your username: <?php echo $username; ?>
<?php } ?>

Your email address: <?php echo $email; ?>

<?php /* Your new password: <?php echo $new_password; ?>

*/ ?>

Thank you,
The <?php echo $site_name; ?> Team