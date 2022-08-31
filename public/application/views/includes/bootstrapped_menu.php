<div class="fixed-header">
<nav class="app-header header-shadow" role="navigation">
    <div class="app-header__logo">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>        
        <li style="list-style-type: none;">
           
            <ul style="margin-top: -10px;border-top-left-radius: 0;border-top-right-radius: 0;">
                
                <?php
                $is_current_user_admin_cached = $this->session->userdata('is_current_user_admin_cached');
                if ($is_current_user_admin_cached) {
                    $is_current_user_admin = $this->session->userdata('is_current_user_admin');
                } else {
                    $is_current_user_admin = $this->User_model->is_admin($this->user_id);
                    $this->session->set_userdata(array('is_current_user_admin' => $is_current_user_admin, 'is_current_user_admin_cached' => true));
                }

                if($is_current_user_admin): ?>
                <li>
                    <a role="menuitem" tabindex="-1"  href="<?php echo base_url().'admin'; ?>">
                        <?php echo l('Dashboard', true); ?>
                    </a><hr>
                </li>
                <?php endif;
                
                 ?>
            </ul>
        </li>
    </div>
    
    <!-- Brand -->
    <div class="app-header__content">
        <ul class="app-header-left multi-properties hide_properties <?=$this->session->userdata('user_role') == "is_housekeeping" ? "hidden" : "";?>">
            <?php if(count($my_companies) > 1){ ?>
                <a href='#' id="myPropertyMenu" class="navbar-brand" data-toggle="dropdown" aria-expanded="true" tabindex="-1">
            <?php } else { ?>
                <a href='#'  id="myPropertyMenu">
            <?php } ?>
                
                <span class="logo"> <b><?php echo substr($this->company_name, 0, 30).((strlen($this->company_name)>30)?'...':''); ?></b>
                </span>
                <?php if(count($my_companies) > 1) { ?><span class="caret multi-prop"></span> <?php } ?>
                
            </a>
            <?php

            $user_permissions = $this->session->userdata('user_permissions');
            if (!$user_permissions) {
                $user_permissions = $this->Permission_model->_get_user_permissions($this->user_id, $this->company_id);
                $this->session->set_userdata(array('user_permissions' => $user_permissions));
            }
            ?>
            
            <?php if(count($my_companies) > 1){ ?>
                <ul class="dropdown-menu property-menu" role="menu" aria-labelledby="myAccountMenu">
                    <?php 
                        foreach($my_companies as $key => $values){ 
                            echo '<li>
                                <a class="my-companies" role="menuitem" tabindex="-1"  href="'.base_url().'menu/select_hotel/'.$values['company_id'].'">
                                '.$values['name'].'
                                </a></li>';
                        }
                    ?>
                </ul>
            <?php } ?>
        </ul>

        <form id="booking_search" method="GET" class="search-wrapper active" action="<?php echo base_url().'booking/show_bookings/'; ?>">
            <div class="">
                <div class="input-holder">
                    <input type="text" name="search_query" class="search-input" placeholder="Search Reservations">
                    <button type="submit" class="search-icon"><span></span></button>
                </div>
            </div>
        </form>

         
                                

        <ul class="app-header-right">
           
        <?php $languages = get_enabled_languages();
            $current_language = $this->session->userdata('language'); ?>
            <li class='nav-link current_language <?=$this->session->userdata('user_role') == "is_housekeeping" ? "hidden" : "";?>'
                data-toggle="popover" 
                data-placement="bottom" 
                data-trigger="manual" 
                data-animation="true" 
                data-content="Click here to change language.">
                <a href='#' id="languageSelection" data-toggle="dropdown" aria-expanded="true">
                    <img class="rounded-circle" src="<?php echo base_url().'images/language_flags/'.$current_language.'.png'; ?>">
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu" aria-labelledby="languageSelection">
            <?php if(!empty($languages)):
                foreach($languages as $key => $value): ?>
                    <li class="change-language" id="<?php echo $value['id'].','.strtolower($value['language_name']); ?>">
                        <a  role="menuitem" tabindex="-1" href="javascript:void(0)">
                            <img  width="42" class="rounded-circle" src="<?php echo base_url().'images/language_flags/'.$value['flag'].'.png'; ?>"><?php echo ' '.$value['language_name']; ?>
                        </a>
                    </li>
            <?php endforeach;
            endif; ?>
                </ul>
            </li>
           
            <li >
                <a href='#' id="myAccountMenu" data-toggle="dropdown" aria-expanded="true">
                    <span id="user_email" class="widget-heading"><?php echo $this->session->userdata('email'); ?></span>
                    <input id='user_id' value='<?php echo $this->user_id; ?>' style='display:none;' />
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu" role="menu" aria-labelledby="myAccountMenu">
                    <li role="presentation" ng-class="{ active: isActive('/settings')}" class="<?=$this->session->userdata('user_role') == "is_housekeeping" ? "hidden" : "";?>">
                        <a role="menuitem" tabindex="-1" href="<?php echo base_url(); ?>account_settings" id="account-link" class="<?=$this->session->userdata('user_role') == "is_housekeeping" ? "hidden" : "";?>">
                            <i class="glyphicon glyphicon-user"></i> <?php echo l('my_account'); ?>
                        </a>
                </li>
                    
                    
                    <li role="presentation" ng-class="{ active: isActive('/settings')}">
                        <a role="menuitem" tabindex="-1" href="<?php echo base_url(); ?>auth/logout" >
                            <i class="glyphicon glyphicon-log-out"></i>
                            <?php echo l('logout'); ?>
                        </a>
                    </li>
                </ul>
            </li>
          </ul>
    </div>
    </div>
</nav>

<?php 
$user_activated = $this->ci->session->userdata('status');
$user_created = $this->ci->session->userdata('created');
if(
        $this->session->userdata('status') == 0 && 
        ($user_created && ((time() - strtotime($user_created)) > (60*60*24*3)))
) { ?>
<!-- <div class="alert alert-danger" style="margin: 10px;">
    <?php echo l('We have sent you a verification link to your email address. Please verify your email address', true); ?>. <a id="resend-verification-link" href="#"><?php echo l('CLICK HERE', true); ?></a> <?php echo l('to resend verification link', true); ?>.
</div> -->
<?php } ?>



<div id="dialogProcessingRequest">
     <?php echo l('processing_request_please_wait'); ?>
</div>


<!-- <div class="modal fade" id="dialog-update-billing" data-backdrop="static" 
   data-keyboard="false" 
   >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">
            <?php //echo l('Subscription Notice', true); ?>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </h4>        
      </div>
      <div class="modal-body">
          <?php //echo l('Dear customer', true); ?>, 
        <p class="message"></p>
      </div>
      <div class="modal-footer">
        <a class="btn btn-success" href="<?php //echo base_url(); ?>account_settings/subscription"><?php //echo l('Update payment details', true); ?></a>
      </div>
      
    </div>
  </div>
</div> -->

<div class="modal fade" id="dialog-verify-email-notification" data-backdrop="static"
     data-keyboard="false"
>
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <?php echo l('Please verify your email', true); ?>!
                </h4>
            </div>
            <div class="modal-body">
                <?php echo l('We have sent a verification link to your email address. Please verify your email to continue using Minical', true); ?>.
                <br/><a id="resend-verification-link" href="#"><?php echo l('RESEND VERIFICATION LINK', true); ?></a>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="dialog-timeout" data-backdrop="static"  data-keyboard="false" >
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-body">
          <p><?php echo l('This page has been inactive for over 30 minutes. Please refresh the page to see the latest changes', true); ?>.</p>
      </div>
      <div class="modal-footer">
        <a class="btn btn-light" href="javascript:window.location.reload()"><?php echo l('Refresh Page', true); ?></a>                        
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- Welcome message for new sign-ups -->

<div class="modal fade" id="myModal" data-backdrop="static" 
   data-keyboard="false" style="z-index:99999"
   >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body" style="position: relative; padding: 15px; overflow-y: scroll; max-height: 75vh;">
      </div>
      <div class="modal-footer">
        
      </div>
      
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade" id="access-restriction-message" data-backdrop="static" 
   data-keyboard="false" style="z-index: 9999999;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" style="text-align: center;">
                    <?php echo l('Access Restricted', true); ?>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </h4>
            </div>
            <div class="modal-body">
                <div style="text-align: center;">
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
                    <br/>
                    <img style="max-width: 100px;" src="<?php echo base_url().'images/restriction.png' ?>">
                    <br/>
                    <br />
                    <div style="font-size: 18px;">
                        <p>
                            <?php echo l('You do not access to this feature with your current subscription plan', true); ?> <b>"<?php echo $subs_plan; ?>"</b>.

                            </br>
                            <?php echo l('Please upgrade to a higher plan to access this feature and a lot more', true); ?>.
                        </p>
                        </br></br>
                        <p>
                            <a href="<?php echo base_url(); ?>account_settings/subscription" class="btn btn-lg btn-primary"><?php echo l('Upgrade Now'); ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<input type="hidden" id="currentCompanyId" value="<?php echo $this->company_id; ?>"/>
<input type="hidden" id="companySubscriptionLevel" value="<?php echo $this->company_subscription_level; ?>"/>
<input type="hidden" id="companySubscriptionState" value="<?php echo $this->company_subscription_state; ?>">
<input type="hidden" id="companyFeatureLimit" value="<?php echo $this->company_feature_limit; ?>">
<input type="hidden" id="RoomSingular" value="<?php echo $this->default_room_singular; ?>">
<input type="hidden" id="RoomPlular" value="<?php echo $this->default_room_plural; ?>">
<input type="hidden" id="RoomType" value="<?php echo $this->default_room_type; ?>">
<input type="hidden" id="CheckInTime" value="<?php echo $this->default_checkin_time; ?>">
<input type="hidden" id="CheckOutTime" value="<?php echo $this->default_checkout_time; ?>">

<script type="text/javascript">
    var is_current_user_admin = "<?php echo $is_current_user_admin; ?>" ;
    var is_current_user_superadmin = "<?php echo $this->user_id === SUPER_ADMIN_USER_ID; ?>" ;
    var is_current_user_activated = "<?=$user_activated;?>";

    var innGrid = innGrid || {};
    innGrid.enableNewCalendar = parseInt('<?=(isset($this->enable_new_calendar) ? $this->enable_new_calendar : 1)?>');
    innGrid.enableHourlyBooking = parseInt('<?=(isset($this->enable_hourly_booking) ? $this->enable_hourly_booking : 0)?>');

    innGrid.featureSettings = innGrid.featureSettings || {};
    innGrid.featureSettings.allow_free_bookings = parseInt('<?=(isset($this->allow_free_bookings) ? $this->allow_free_bookings : 0)?>');
    innGrid.featureSettings.selectedPaymentGateway = '<?=(isset($this->selected_payment_gateway) ? $this->selected_payment_gateway : '')?>';
    innGrid.featureSettings.bookingCancelledWithBalance = parseInt('<?=(isset($this->booking_cancelled_with_balance) ? $this->booking_cancelled_with_balance : 0)?>');
    innGrid.isCCVisualizationEnabled = parseInt('<?=(($this->is_cc_visualization_enabled) ? 1 : 0)?>');
    innGrid.featureSettings.cuurentLanguage = "<?=$this->session->userdata('language') ? $this->session->userdata('language') : ''?>";

    // subscription plans
    var STARTER = "<?php echo STARTER; ?>";
    var BASIC   = "<?php echo BASIC; ?>";
    var PREMIUM = "<?php echo PREMIUM; ?>";
    var ELITE   = "<?php echo ELITE; ?>";
</script>
