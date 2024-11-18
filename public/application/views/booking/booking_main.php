
<div class="main-card mb-3 card">
    <div class="card-body" style="padding: 1.2rem 1.2rem 1.2rem 1.2rem;">
<?php 
    $flag = 1;
    $permissions = $this->session->userdata('permissions');
    if(isset($permissions) && $permissions != NULL)
    {
        if(in_array('bookings_view_only', $permissions) && !(in_array('access_to_bookings', $permissions)))
        {
            $flag = 0;
        }
    }    
?>



<div id="filter-booking" style="padding-top: 0px; padding-bottom:10px;display: none;">
    <div class="pull-left h4">
        <strong><?php echo l('filters'); ?></strong>
    </div>
    <div class="col-sm-3 col-md-2 col-lg-2 col-xs-12">
        <select name="room-type" class="form-control" onchange="filter_data()" id="filter-sapce">
            <option value=""><?php echo l('all_room_types'); ?></option>
            <?php
                foreach($room_types as $room_type)
                {
                    echo "<option value='".$room_type['id']."'>".$room_type['name'].'('.$room_type['acronym'].")</option>\n";
                }
            ?>
        </select>
    </div> 

     <div class="col-sm-2 col-md-2 col-lg-2 col-xs-2">
        <select name="room-floor" class="form-control col-sm-4 col-md-4 col-lg-4 col-xs-4" onchange="filter_data()">
            <option value=""><?php echo l('all_floors'); ?></option>    
            <?php
                foreach($room_floor as $key => $floors)
                {
                    echo "<option value='".$floors['id']."'>".$floors['floor_name']."</option>";
                }
            ?>
        </select>
    </div>
        
    <div class="col-sm-2 col-md-2 col-lg-2 col-xs-2">
        <select name="room-location" class="form-control col-sm-4 col-md-4 col-lg-4 col-xs-4" onchange="filter_data()">
            <option value=""><?php echo l('all_locations'); ?></option>    
            <?php
                foreach($room_location as $key => $locations)
                {
                    echo "<option value='".$locations['id']."'>".$locations['location_name']."</option>";
                }
            ?>
        </select>
    </div>    

    <div class="col-sm-3 col-md-2 col-lg-2 col-xs-12">
        <select name="reservation-type" class="form-control col-sm-4 col-md-4 col-lg-4 col-xs-4" onchange="filter_data()" id="filter-sapce">
            <option value=""><?php echo l('all_bookings_except_cancelled'); ?></option>
            <option value="-1"><?php echo l('all_bookings'); ?></option>
            <option value="0"><?php echo l('reservation'); ?></option>
            <option value="1"><?php echo l('checked_in'); ?></option>
            <option value="2"><?php echo l('checked_out'); ?></option>
            <option value="4"><?php echo l('cancelled'); ?></option>
            <option value="7"><?php echo l('unconfirmed_reservation'); ?></option>
            <option value="5"><?php echo l('no_show'); ?></option>
        </select>
    </div>

    <div class="col-sm-3 col-md-2 col-lg-2 col-xs-12">
        <select name="booking-source" class="form-control" onchange="filter_data()" id="new-select">
            <option value=""><?php echo l('All Booking Sources'); ?></option>
            <?php
                foreach($booking_sources as $booking_source)
                {
                    echo "<option value='".$booking_source['id']."'>".$booking_source['name']."</option>\n";
                }
            ?>
        </select>
    </div>    
    <button class="btn btn-light" style="padding: 6px 8px;margin-left: 4px;" onclick="$(this).openSearchGroupModel();"><?php echo l('Search Groups', true); ?></button>
</div>    
<!--<input name="create_new_booking" type="hidden" value="<?php //echo l('create_new_booking'); ?>" />-->
<div id="calendar" class="tab_btn_calendar"><?php echo l('Loading', true); ?>...</div>
<div id="overview_calendar" class="tab_btn_calendar"></div>
<div id="notification-drag-box">
    <p><?php echo l('from'); ?>: <span class="from"></span></p>
	<p><?php echo l('to'); ?>: <span class="to"></span></p>
	<p><?php echo l('room'); ?>: <span class="room"></span></p>
</div>


</div>

</div>

<div class="modal fade" id="dialog-onhold-message" data-backdrop="static" 
   data-keyboard="false" 
   >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">
            <?php echo l('Notice', true); ?>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </h4>        
      </div>
      <div class="modal-body">
        <?php echo l('Dear customer', true); ?>, 
        <p class="message"></p>
      </div>
<!--      <div class="modal-footer">
        <a class="btn btn-success" href="<?php echo base_url(); ?>account_settings/subscription">Update payment details</a>
      </div>-->
      
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<div class="modal fade" id="dialog-onhold-message-newsignup" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-lg" style="width: 1130px;">
        <div class="modal-content">
            <div class="modal-header text-center" style="padding: 30px;">
                <h4 class="modal-title">
                    <?php echo l('Thank you for booking your free Demo, Please check your emails for the details and Mark your calendar', true); ?>.
                </h4>
            </div>
            <div class="modal-body message"></div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade"  id="csv-export-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo l('export_bookings_csv'); ?></h4>
			</div>
			<div class="modal-body form-horizontal">
				<div class="form-group" id="option-to-add-multiple-payments">
					<label for="pay_for" class="col-sm-4 control-label"><?php echo l('booking_type'); ?></label>
					<div class="col-sm-8">
						<select class="form-control" name="select-csv-export">
							<option>-- <?php echo l('select_booking_type'); ?> --</option>
							<option value="download-reservation-csv"><?php echo l('reservations'); ?></option>
							<option value="download-inhouse-csv"><?php echo l('in_house'); ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" id="add_payment_button">
					<?php echo l('download'); ?>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<?php echo l('close'); ?>
				</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade"  id="csv-export-rate-plan-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo l('export_bookings_csv'); ?></h4>
            </div>
            <div class="modal-body form-horizontal">
                <div class="form-group" id="option-to-add-multiple-payments">
                    <label for="pay_for" class="col-sm-4 control-label"><?php echo l('Rate plan Type', true); ?></label>
                    <div class="col-sm-8">
                        <select class="form-control" name="select-rate-plan-export">
                            <!-- <option>-- <?php echo l('Select rate plan'); ?> --</option> -->
                            <option value="select_all">Select All</option>
                            <option value="ota_rate_plan">OTA rate plan</option>
                            <option value="fix_rate_plan">Fix rate plan</option>
                            <option value="per_person_type_plan">Per person type plan</option>
                            <hr>
                            <?php foreach ($rate_plans as $key => $value) { ?>
                                <option value="<?php echo $value['rate_plan_id']; ?>"><?php echo $value['rate_plan_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="export_bookings_by_rate_plan">
                    <?php echo l('download'); ?>
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo l('close'); ?>
                </button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div>

<div class="modal fade"  id="add-daily-charges-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:11111;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo l('Pay Period settings'); ?></h4>
			</div>
			<div class="modal-body form-horizontal">
				<div class="form-group" id="option-to-add-multiple-payments">
                    <div class="col-sm-12">
                        <input type="checkbox" class="add-daily-charge" name="add-daily-charge" value="1">
                        <?php echo l('Allow Daily Room Charges to be added for remaining days of a Monthly/Weekly period bookings.'); ?>
                    </div>
                </div>
                <div class="form-group" id="residual_rate_div">
					<div class="col-sm-12">
						<?php echo l('Rate on remaining days'); ?>
                        <input type="number" class="residual_rate" name="residual_rate" value="">
					</div>
				</div>
			</div>
			<div class="modal-footer">
                <span class="daily_charge_msg" style="color: green;display: none;margin: 32px;"><?php echo l('Details Saved', true); ?></span>
				<button type="button" class="btn btn-success" id="add_save_daily_charge_button">
					<?php echo l('Ok'); ?>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<?php echo l('close'); ?>
				</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div class="modal fade"  id="room-edit-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><?php echo l('edit_room'); ?></h4>
			</div>
			<div class="modal-body form-horizontal">
				<div class="form-group">
					<label for="room_edit_room_name" class="col-sm-4 control-label"><?php echo l('room_name'); ?></label>
					<div class="col-sm-8">
                        <input type="text" class="form-control" name="room_edit_room_name" placeholder='<?php echo l("room_name"); ?>' disabled>
					</div>
				</div>
				<div class="form-group">
					<label for="room_edit_room_type_id" class="col-sm-4 control-label"><?php echo l('room_type'); ?></label>
					<div class="col-sm-8">
						<select class="form-control" name="room_edit_room_type_id" disabled>
							<option>-- <?php echo l('select_room_type'); ?> --</option>
						 	<?php
					        	foreach ($room_types as $room_type):
				            ?>
						            <option value="<?php echo $room_type['id']; ?>" title="<?php echo $room_type['acronym']; ?>">
						                <?php echo $room_type['name']." (".$room_type['acronym'].")"; ?>
						            </option>

					        <?php
					        	endforeach;
					        ?>
					        <option value="create_new">-- <?php echo l('add_new_room_type'); ?> --</option>
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="room_edit_room_status" class="col-sm-4 control-label"><?php echo l('status'); ?></label>
					<div class="col-sm-8">
						<select class="form-control" name="room_edit_room_status">
							<option value="Clean"><?php echo l('clean'); ?></option>
							<option value="Dirty"><?php echo l('dirty'); ?></option>
                                                        <option value="Inspected"><?php echo "Inspected"; ?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" id="room_edit_save">
					<?php echo l('save'); ?>
				</button>
				<button type="button" class="btn btn-success" id="room_edit_save_and_proceed">
					<?php echo l('save_and_proceed'); ?>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<?php echo l('close'); ?>
				</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- Property update model -->
<div class="modal fade" id="update-property-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form id="update_company_form">
                
                <div class="alert alert-success" style="display:none"></div>
                <div class="alert alert-danger" style="display:none"></div>
                <div class="modal-booking-step">
                    <div class="text-center" style="padding: 15px;">
                        <div class="panel panel-success" style='margin-bottom: 0;'>
                            <div class='text-center'>
                                <h3><?php echo l('Create a property', true); ?></h3>
                                <h5><?php echo l('Step 3 of 3', true); ?></h5>
                            </div>
                            <div class="panel-body form-horizontal">
                                <div class="form-group">
                                    <label for="first_name" class="col-sm-3 control-label">
                                        <?php echo l('first_name'); ?><span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="first_name" id="first_name">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="last_name" class="col-sm-3 control-label">
                                        <?php echo l('last_name'); ?><span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="last_name" id="last_name">
                                    </div>
                                </div>
                               
                                <div class="form-group">
                                    <label for="country" class="col-sm-3 control-label">
                                        <?php echo l('Country',true); ?><span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="country" id="country">
                                            <option value="0"><?php echo l('select_country'); ?></option>
                                            <?php
                                            $countries = array( 'AF' => 'Afghanistan', 'AX' => 'Aland Islands', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa', 'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua and Barbuda', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BQ' => 'Bonaire, Saint Eustatius and Saba', 'BA' => 'Bosnia and Herzegovina', 'BW' => 'Botswana', 'BV' => 'Bouvet Island', 'BR' => 'Brazil', 'IO' => 'British Indian Ocean Territory', 'VG' => 'British Virgin Islands', 'BN' => 'Brunei', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island', 'CC' => 'Cocos Islands', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica', 'HR' => 'Croatia', 'CU' => 'Cuba', 'CW' => 'Curacao', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'CD' => 'Democratic Republic of the Congo', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'TL' => 'East Timor', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GF' => 'French Guiana', 'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala', 'GG' => 'Guernsey', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HM' => 'Heard Island and McDonald Islands', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IM' => 'Isle of Man', 'IL' => 'Israel', 'IT' => 'Italy', 'CI' => 'Ivory Coast', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JE' => 'Jersey', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'XK' => 'Kosovo', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Laos', 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macao', 'MK' => 'Macedonia', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' => 'Micronesia', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island', 'KP' => 'North Korea', 'MP' => 'Northern Mariana Islands', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestinian Territory', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn', 'PL' => 'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'CG' => 'Republic of the Congo', 'RE' => 'Reunion', 'RO' => 'Romania', 'RU' => 'Russia', 'RW' => 'Rwanda', 'BL' => 'Saint Barthelemy', 'SH' => 'Saint Helena', 'KN' => 'Saint Kitts and Nevis', 'LC' => 'Saint Lucia', 'MF' => 'Saint Martin', 'PM' => 'Saint Pierre and Miquelon', 'VC' => 'Saint Vincent and the Grenadines', 'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome and Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'RS' => 'Serbia', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SX' => 'Sint Maarten', 'SK' => 'Slovakia', 'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' => 'South Georgia and the South Sandwich Islands', 'KR' => 'South Korea', 'SS' => 'South Sudan', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard and Jan Mayen', 'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syria', 'TW' => 'Taiwan', 'TJ' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TG' => 'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad and Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TC' => 'Turks and Caicos Islands', 'TV' => 'Tuvalu', 'VI' => 'U.S. Virgin Islands', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' => 'United States', 'UM' => 'United States Minor Outlying Islands', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VA' => 'Vatican', 'VE' => 'Venezuela', 'VN' => 'Vietnam', 'WF' => 'Wallis and Futuna', 'EH' => 'Western Sahara', 'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe');

                                            $info_by_ip = null;
                                            if ($this->session->userdata('is_registration_page')) {
                                                // detect country using ip
                                                $ip_address = file_get_contents('https://api.ipify.org');

                                                if ($ip_address) {
                                                    $info_by_ip = file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip_address);
                                                    $info_by_ip = $info_by_ip ? @json_decode($info_by_ip) : null;
                                                }
                                            }

                                            foreach ($countries as $country_code => $country):
                                                ?>
                                                <option data-country-code="<?=$country_code;?>" value="<?php echo $country; ?>" <?php if($info_by_ip && isset($info_by_ip->geoplugin_countryName) && $info_by_ip->geoplugin_countryName == $country) { echo 'selected'; } elseif(!$info_by_ip && $country_code == 'US')  { echo 'selected'; } ?>><?php echo $country; ?></option>
                                                <?php
                                            endforeach;
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="language" class="col-sm-3 control-label">
                                        <?php echo l('Language'); ?><span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <?php $languages = get_enabled_languages();
                                        $lang_obj = json_encode(array('Korean' => array('KP' => 'North Korea','KR' => 'South Korea'),'Portuguese' => array('BR' => 'Brazil','MZ' => 'Mozambique','AO' => 'Angola','PT' => 'Portugal','GQ' => 'Equatorial Guinea','GW' => 'Guinea-Bissau','TL' => 'East Timor','MO' => 'Macao','CV' => 'Cape Verde','ST' => 'Sao Tome and Principe'),'Spanish' => array('AR' => 'Argentina','BO' => 'Bolivia','CL' => 'Chile','CO' => 'Colombia','CR' => 'Costa Rica','CU' => 'Cuba','DO' => 'Dominican Republic','EC' => 'Ecuador','SV' => 'El Salvador','GT' => 'Guatemala','HN' => 'Honduras','MX' => 'Mexico','NI' => 'Nicaragua','PA' => 'Panama','PY' => 'Paraguay','PE' => 'Peru','PR' => 'Puerto Rico','ES' => 'Spain','UY' => 'Uruguay','VE' => 'Venezuela'),'French' => array('BE' => 'Belgium','BJ' => 'Benin','BF' => 'Burkina Faso','BI' => 'Burundi','CM' => 'Cameroon','CA' => 'Canada','CF' => 'Central African Republic','TD' => 'Chad','CM' => 'Comoros','CD' => 'Democratic Republic of the Congo','DJ' => 'Djibouti','FR' => 'France','GN' => 'Guinea','HT' => 'Haiti','LU' => 'Luxembourg','MG' => 'Madagascar','ML' => 'Mali','MC' => 'Monaco','NE' => 'Niger','CG' => 'Republic of the Congo','CI' => 'Ivory Coast','GA' => 'Gabon','RW' => 'Rwanda','LC' => 'Saint Lucia','SN' => 'Senegal','SC' => 'Seychelles','CH' => 'Switzerland','TG' => 'Togo','VU' => 'Vanuatu'),'Russian' => array('BY' => 'Belarus','KG' => 'Kyrgyzstan','KZ' => 'Kazakhstan','RU' => 'Russia')), true); ?>
                                        
                                        <select name="language" id="language" class="form-control" data-lang_group = '<?php echo $lang_obj; ?>'>
                                            <?php if(!empty($languages)):
                                                foreach($languages as $key => $value): ?>
                                                    <option data-lang_id="<?php echo $value['id'].','.strtolower($value['language_name']); ?>" value="<?php echo $value['language_name']; ?>" ><?php echo $value['language_name']; ?></option>
                                            <?php endforeach; endif; ?>
                                        </select>

                                        <?php 

                                         ?>
                                            
                                    </div>
                                </div>



                                <?php
                                $is_hosted_prod_service = getenv('IS_HOSTED_PROD_SERVICE');
                                if($is_hosted_prod_service || $_SERVER['HTTP_HOST'] == "app.minical.io" || $_SERVER['HTTP_HOST'] == "demo.minical.io"){?>
                                    <br/><br/>
                                    <div class="form-group" >
                                    <label for="property_type" class="col-sm-3 control-label">
                                        <?php echo l('Property Type', true); ?><span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <select class="form-control" name="property_type" id="property_type">
                                            <option value="0"><?php echo l('---Select property type---', true); ?></option>
                                            <option id="hotel" value="1"><?php echo l('Hotel', true); ?></option>
                                            <option id="hostel" value="2"><?php echo l('Hostel', true); ?></option>
                                            <option id="vacation_retail" value="3"><?php echo l('Vacation Rental', true); ?></option>
                                            <option id="apartment" value="4"><?php echo l('Apartment', true); ?></option>
                                            <option id="car_rental" value="5"><?php echo l('Car Rental', true); ?></option>
                                            <option id="office_space" value="6"><?php echo l('Office Space', true); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div style="display: none;" class="property">
                                 <div class="form-group" >
                                    <label for="property_name" class="col-sm-3 control-label property_name">
                                        <?php echo l('property_name'); ?><span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="property_name" id="property_name">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="number_of_rooms" class="col-sm-3 control-label number_of_rooms">
                                        <?php echo l('no_of_rooms'); ?><span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="number_of_rooms" id="number_of_rooms">
                                    </div>
                                </div>
                            </div>
                                    
                            <?php }else{?>

                                


                            <div class="form-group">
                                    <label for="property_name" class="col-sm-3 control-label">
                                        <?php echo l('property_name'); ?><span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="property_name" id="property_name">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="number_of_rooms" class="col-sm-3 control-label">
                                        <?php echo l('no_of_rooms'); ?><span style="color: red;">*</span>
                                    </label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="number_of_rooms" id="number_of_rooms">
                                    </div>
                                </div>

                                
                        <?php }?>
                                <div class="form-group">
                                    <label for="" class="col-sm-3 control-label"></label>
                                    <div class="col-sm-9">
                                        <button type="button" class="btn btn-lg btn-success btn-block" id="update_property_button" onclick="ajax_submit('<?php echo base_url()?>auth/update_company','#update_company_form','update_company')">
                                           <?php echo l('Start Using '); if($whitelabel_detail){  echo ucfirst($whitelabel_detail['name']); }else{
                                                    echo $this->config->item('branding_name');
                                            }?>
                                        </button>
                                    </div>
                                </div>



                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Property update model ends -->

<div class="modal fade" id="confirm-blacklist-customer" data-backdrop="static" 
   data-keyboard="false" style="z-index: 9999;"
   >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">
            <?php echo l('This customer is blacklisted'); ?>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </h4>
      </div>
      <div class="modal-body blacklist_customer hidden">
        <!-- This customer is blacklisted : customer notes -->
      </div>
      <div class="modal-footer">
        <a class="btn btn-success confirm-customer" flag="ok" href=""><?php echo l('OK', true); ?></a>
        <a class="btn btn-danger confirm-customer" flag="cancel" href=""><?php echo l('Cancel', true); ?></a>
      </div>
      
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>

<div class="modal fade" id="reservation-message" data-backdrop="static" 
   data-keyboard="false" style="z-index: 9999;"
   >
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">
        	<span class="message-heading"><?php echo l('message'); ?></span>
        	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </h4>
        
      </div>
      <div class="modal-body message">
      	
      </div>
      <div class="modal-footer">
        <a class="btn btn-danger confirm-customer hidden" flag="cancel" href=""><?php echo l('Cancel', true); ?></a>
        <a class="btn btn-success confirm-customer" flag="ok" href=""><?php echo l('OK', true); ?></a>        
      </div>
      
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div>

<?php if ($this->session->userdata('is_registration_page') != '') { ?>
<div class="modal" id="tutorial-video-modal" tabindex="-1" role="dialog" aria-labelledby="tutorial-video-modal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="myModalLabel">
                    <?=l("A quick Introduction to Minical");?>
                    <button type="button" class="btn btn-success pull-right" data-dismiss="modal" aria-hidden="true"><?=l("Skip");?></button>
                </h3>
            </div>
            <script src="https://fast.wistia.com/embed/medias/bkn09yxmrp.jsonp" async></script><script src="https://fast.wistia.com/assets/external/E-v1.js" async></script><div class="wistia_responsive_padding" style="padding:56.25% 0 0 0;position:relative;"><div class="wistia_responsive_wrapper" style="height:100%;left:0;position:absolute;top:0;width:100%;"><div class="wistia_embed wistia_async_bkn09yxmrp videoFoam=true autoPlay=true" style="height:100%;position:relative;width:100%"><div class="wistia_swatch" style="height:100%;left:0;opacity:0;overflow:hidden;position:absolute;top:0;transition:opacity 200ms;width:100%;"><img src="https://fast.wistia.com/embed/medias/bkn09yxmrp/swatch" style="filter:blur(5px);height:100%;object-fit:contain;width:100%;" alt="" onload="this.parentNode.style.opacity=1;" /></div></div></div></div>
        </div>
    </div>
</div>
<?php } ?>

<input type="hidden" id="registration_session" value="<?php echo $this->session->userdata('is_registration_page'); ?>"/>
<input type="hidden" id="trial_expiry_date" value="<?php echo $company_data['trial_expiry_date']; ?>"/>
<input type="hidden" id="subscription_state" value="<?php echo $company_data['subscription_state']; ?>"/>
<input type="hidden" id="support_email" value="<?php echo $support_email; ?>"/>

<style>
	.group_booking_confirm_dialog{
		z-index: 11111 !important;
		top: 30px !important;
		left: 460px !important;
	}
</style>
<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
<script>
	$(document).ready(function(){
            var registration_session = $('#registration_session').val();
            if(registration_session!=''){
                $('#update-property-modal').modal({
                        backdrop: 'static',
                        keyboard: false
                });
                
                // user just registered, fire Intercom event
                // Intercom('trackEvent', 'Signup_step_1');
            }
            
            var subscription_state = $('#subscription_state').val();
            var support_email = $('#support_email').val();
            if(registration_session == '' && subscription_state == 'on_hold'){
                $("#dialog-onhold-message").find(".close").hide();
                $("#dialog-onhold-message").modal("show");
                $('#dialog-onhold-message .message').html('The account is put on hold please contact us at '+ support_email+' to reactivate the account.');
            }
            
            var trial_expiry_date = $('#trial_expiry_date').val();
            if(trial_expiry_date != '' && subscription_state == 'trialing')
            {
                 const startDate = new Date(trial_expiry_date);
                 const currentDate = new Date();

                // Calculate the time difference in milliseconds
                const timeDiff = Math.abs(currentDate.getTime() - startDate.getTime());

                // Convert the time difference to days
                const days = Math.ceil(timeDiff / (1000 * 3600 * 24));
                
                var url = '<?php echo base_url(); ?>settings/company/view_subscription';
                if(days <= 5 && days >= 0)
                {
                    if(days == '0')
                    {
                        $('.trial_period').html(l('Your trial will expire today. To avoid service interruption') + ', <a href="'+url+'">'+l("please click here")+'</a>')
                    }
                    else
                    {
                        $('.trial_period').html(l('Your trial will expire in')+' '+days+' '+l("days")+'. '+l("To avoid service interruption")+', <a href="'+url+'">'+l("please click here")+'</a>')
                    }
                    
                    $('.trial_period').css('display', 'block');
                }
            }
        
        innGrid.isDisplayTooltip = parseInt('<?=(isset($this->is_display_tooltip) ? $this->is_display_tooltip : 0)?>');        
        innGrid.isOverviewCalendar = parseInt('<?=(isset($is_overview_calendar) ? $is_overview_calendar : 0)?>');
        innGrid.isShowUnassignedRooms = parseInt('<?=(isset($is_show_unassigned_rooms) ? $is_show_unassigned_rooms : 0)?>');
        innGrid.hideDecimalPlaces = parseInt('<?=(isset($hide_decimal_places) ? $hide_decimal_places : 0)?>');
        innGrid.makeGuestFieldMandatory = parseInt('<?=(isset($make_guest_field_mandatory) ? $make_guest_field_mandatory : 0)?>');
        innGrid.bookingSources = JSON.parse('<?=addslashes(json_encode((isset($booking_sources) ? $booking_sources : [])))?>');
        innGrid.color = JSON.parse('<?=addslashes(json_encode((isset($date_colors) ? $date_colors : [])))?>');
        innGrid.roomsWithoutFilters = JSON.parse('<?=addslashes(json_encode((isset($rooms_without_filters) ? $rooms_without_filters : [])))?>');
        innGrid.isDarkTheme = parseInt('<?=(isset($this->company_ui_theme) && $this->company_ui_theme == THEME_DARK ? 1 : 0)?>');

        var flag = <?php echo $flag; ?>;
        innGrid.hasBookingPermission = <?php echo $flag; ?>; 

        <?php 
        if($this->company_name && (strtotime($this->company_creation_date) >= strtotime("2018-06-05")) && (!isset($_COOKIE['is_shown_tutorial_popover']) || !$_COOKIE['is_shown_tutorial_popover'])): ?>
            $('.help-link[data-toggle="popover"]').popover('show');
            $(document).on('click', function() {
                $('.help-link[data-toggle="popover"]').popover('hide');
            });
            $('li').on('click', function() {
                $('.help-link[data-toggle="popover"]').popover('hide');
            });
        <?php 
            setcookie("is_shown_tutorial_popover", true, time()+60*60*24*365*10, "minical.io"); // 86400 = 1 day
        endif; 
        ?>
	});
</script>

<?php 
$myVar = getenv('CARDKNOX_IFIELD_KEY');
    echo "<script>
    const myVar = '" . $myVar . "';
        </script>";
?>