<?php if(isset($this->company_subscription_level) && $this->company_subscription_level == ELITE)
    {
        $subs_plan =  'Elite';
    }
    elseif(isset($this->company_subscription_level) && $this->company_subscription_level == PREMIUM)  
    {
        $subs_plan =  'Premium';
    }
    elseif(isset($this->company_subscription_level) && $this->company_subscription_level == BASIC)    
    {
        $subs_plan =  'Basic';
    }
    else
    {
        $subs_plan =  'Starter';
    }           
        
?>
<div class='container' style="text-align: center;">
    <br/><br/>
    <h1><?php echo l('Restricted Area', true); ?></h1>
    <br/>
    <img style="max-width: 100px;" src="<?php echo base_url().'images/restriction.png' ?>">
    <br/><br/>
    <br />
    <div style="font-size: 18px;">
        <p>
            <?php echo l('You do not access to this page with your current subscription plan', true); ?> <b>"<?php echo $subs_plan; ?>"</b>.

            </br></br>
            <?php echo l('Please upgrade to a higher plan to access this feature and a lot more', true); ?>.
        </p>
        <br/><br/>
        <p>
            <a href="<?php echo base_url(); ?>account_settings/subscription" class="btn btn-lg btn-primary"><?php echo l('Upgrade Now'); ?></a>
        </p>
    </div>
</div>