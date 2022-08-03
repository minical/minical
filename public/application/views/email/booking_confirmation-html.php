<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title><?php echo $company_name; ?> <?php echo l('Booking Confirmation', true); ?>: <?php echo $booking_id;?></title>
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
    <h2>
        <?php echo l('Thanks for booking your stay at', true); ?> <?php echo $company_name; ?>.

        <h2>
            <?php echo $booking_confirmation_email_header; ?>
        </h2>

    </h2>

    <?php if($customer_modify_booking){ ?>
        <p><?php echo l('Please visit the following link to view and modify your booking', true); ?>: <?php echo $booking_modify_link; ?></p>
        <br />
    <?php } ?>

    <h3><?php echo l('Guest Information', true); ?></h3>
    <div class="group">
        <span class="label"><?php echo l('Name', true); ?>:</span>
        <span id="customer-name"><?php echo $customer_name; ?></span>
        <br />
        <br />
        <span class="label"><?php echo l('Address', true); ?>:</span>
        <br />
        <?php echo $customer_address; ?>,
        <br />
        <?php echo $customer_city; ?>, <?php echo $customer_region; ?>, <?php echo $customer_country; ?>
        <br />
        <?php if (!empty($customer_postal_code)) : ?>
            <?php echo $customer_postal_code; ?>
            <br />
        <?php endif ?>
        <br />

        <span class="label"><?php echo l('Phone', true); ?>:</span>
        <?php echo $customer_phone; ?>
        <br />
        <span class="label"><?php echo l('Email', true); ?>:</span>
        <?php echo $customer_email; ?>
        <br />
    </div>
    <br />

    <h3><?php echo l('Reservation Information', true); ?></h3>
    <?php
    if(isset($group_id) && $group_id != NULL)
    {
        foreach ($reservation_info as $res)
        {
            ?>
            <div class="group">
                <span class="label"><?php echo l('Booking ID', true); ?>:</span>
                <?php echo $res['booking_id']; ?>
                <br />
                <br />
                <?php echo $res['customer_name']; ?>

                <?php
                if($res['staying_customers'] != NULL)
                {
                    echo ", ".implode(',', $res['staying_customers']);
                }
                ?>
                <br />
                <br />
                <span class="label"><?php echo l('Check-in Date', true); ?>:</span>
                <?php echo $res['check_in_date']; ?>
                <br />
                <span class="label"><?php echo l('Check-out Date', true); ?>:</span>
                <?php echo $res['check_out_date']; ?>
                <br />
                <br />

                <span class="label"><?php echo $company_room;?>: </span>
                <?php echo $res['name']; ?>
                <br />
                <br />

                <span class="label"><?php echo l('Average daily rate', true); ?>*: </span>
                <?php echo number_format($res['average_daily_rate'], 2, ".", ","); ?>
                <br />
                <span class="label"><?php echo l('Total', true); ?>*: </span>
                <?php echo number_format($res['rate'], 2, ".", ","); ?>
                <br />
                <span class="label"><?php echo l('Total with taxes', true); ?>*: </span>
                <?php echo number_format($res['rate_with_taxes'], 2, ".", ","); ?>
                <br />
                <br />

            </div>
            <br />
            <?php
        }
    }
    else
    {
        ?>
        <div class="group">
            <span class="label"><?php echo l('Booking ID', true); ?>:</span>
            <?php echo $booking_id; ?>
            <br />
            <br />

            <span class="label"><?php echo l('Check-in Date', true); ?>:</span>
            <?php echo $check_in_date; ?>
            <br />
            <span class="label"><?php echo l('Check-out Date', true); ?>:</span>
            <?php echo $check_out_date; ?>
            <br />
            <br />

            <span class="label"><?php echo $company_room;?>:</span>
            <?php echo $room_type; ?>
            <br />
            <br />

            <span class="label"><?php echo l('Average daily rate', true); ?>*: </span>
            <?php echo number_format($average_daily_rate, 2, ".", ","); ?>
            <br />
            <span class="label"><?php echo l('Total', true); ?>*: </span>
            <?php echo number_format($rate, 2, ".", ","); ?>
            <br />
            <span class="label"><?php echo l('Total with taxes', true); ?>*: </span>
            <?php echo number_format($rate_with_taxes, 2, ".", ","); ?>
            <br />
            <br />

        </div>
        <br />
        <?php
    }
    ?>


    <h3><?php echo l('Contact Information', true); ?></h3>
    <div class="group">
        <span id="company-name"><?php echo $company_name; ?></span>
        <br />
        <br />

        <?php echo $company_address; ?>,
        <br />
        <?php echo $company_city; ?>, <?php echo $company_region; ?>, <?php echo $company_country; ?>
        <br />
        <?php if (!empty($company_postal_code)) : ?>
            <?php echo $company_postal_code; ?>
            <br />
        <?php endif ?>
        <br />

        <span class="label"><?php echo l('Phone', true); ?>:</span>
        <?php echo $company_phone; ?>
        <br />
        <?php if (!empty($company_fax)) : ?>
            <span class="label"><?php echo l('Fax', true); ?>:</span>
            <?php echo $company_fax; ?>
            <br />
        <?php endif ?>
        <br />

        <span class="label"><?php echo l('Email', true); ?>:</span>
        <?php echo $company_email; ?>
        <br />
        <span class="label"><?php echo l('Website', true); ?>:</span>
        <?php echo $company_website; ?>
        <br />

    </div>

    <br />


    <h3><?php echo l('Reservation Policies', true); ?></h3>
    <div class="group">
        <?php echo $reservation_policies; ?>
        <br />
    </div>

    <br />

    <?php if($room_instructions){ ?>
    
        <h3><?php echo l($this->default_room_singular).' '.l('Instructions', true); ?></h3>
        <div class="group">
            <?php echo $room_instructions; ?>
            <br />
        </div>
        <br/>

    <?php } ?>

    <div id="disclaimer">
        <span>*<?php echo l('Estimated total before tax and service charges.', true); ?></span>
    </div>
</div>
</body>
</html>
