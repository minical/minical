<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title><?php echo $company_name; ?> <?php echo l('Booking Cancellation', true); ?>: <?php echo $booking_id;?></title>
    <style type="text/css">
        h2 {font: bold 16px Arial, Helvetica, sans-serif; margin: 0; padding: 0 0 18px; color: black;}
        h3 {font: 16px Arial, Helvetica, sans-serif; margin: 0; color: black;}
        .group {border: 1px solid black; padding: 10px; }
        #customer-name, #company-name {font-weight: bold; }
    </style>
</head>
<body>
<div style="max-width: 600px;">

    <?php if ($company_logo_url) { ?>
        <div style="text-align: center">
            <img src="<?php echo $company_logo_url ?>" width='160'/>
        </div>
        <br><br>
    <?php } ?>
    <h3>Dear <?php echo $customer_name;?></h3><br/>

    </h4><?php echo l('Your booking has been cancelled', true); ?></h4>
    <h4> <?php echo $content; ?> </h4>


    <div >
        <span class="label"><?php echo l('Booking ID', true); ?>:</span>
        <?php echo $booking_id; ?>
        <br />
        <span class="label"><?php echo l('Check-in Date', true); ?>:</span>
        <?php echo $check_in_date; ?>
        <br />
        <span class="label"><?php echo l('Check-out Date', true); ?>:</span>
        <?php echo $check_out_date; ?>
        <br />
        <span class="label"><?php echo $company_room;?>: </span>
        <?php echo $room_type; ?>
    </div>


    <h4> <?php echo $content1; ?> </h4>


</div>
</body>
</html>
