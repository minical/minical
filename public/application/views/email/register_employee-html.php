<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head><title>You have a new account at <?=$partner_name; ?>!</title></head>
<body>
<div style="max-width: 800px; margin: 0; padding: 30px 0;">
<table width="80%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td width="5%"></td>
<td align="left" width="95%" style="font: 13px/18px Arial, Helvetica, sans-serif;">
<h2 style="font: normal 20px/23px Arial, Helvetica, sans-serif; margin: 0; padding: 0 0 18px; color: black;">You have a new account at <?=$partner_name;?>!</h2>
<br /><br />
This is part of the procedure to create a new account. If you DID NOT expect this email, please ignore it.
<br /><br />
To get started, please activate your account and choose a password by following the link below.
<br />
<big style="font: 16px/18px Arial, Helvetica, sans-serif;"><b><a href="<?php echo site_url('/auth/activate_employee/'.$user_id.'/'.$new_pass_key); ?>" style="color: #3366cc;">Activate your account</a></b></big><br />
<br />
Link doesn't work? Copy the following link to your browser address bar:<br />
<nobr><a href="<?php echo site_url('/auth/activate_employee/'.$user_id.'/'.$new_pass_key); ?>" style="color: #3366cc;"><?php echo site_url('/auth/activate_employee/'.$user_id.'/'.$new_pass_key); ?></a></nobr><br />
<br />
<br />
If you have any questions, please contact <?php echo $support_email?>. <br />
<br />
<br />
Thank you,<br />
<?=$partner_name;?> Team
</td>
</tr>
</table>
</div>
</body>
</html>