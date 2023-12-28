<?php

class Channex_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function save_token($data)
    {
        $data = (object)$data;
        $this->db->insert("ota_manager", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
      
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {  
            return $result[0]['last_id'];
        }
        else
        {  
            return null;
        }
    }

    function update_token($data)
    {
        $this->db->where('email', $data['email']);
        $this->db->where('company_id', $data['company_id']);
        $this->db->update("ota_manager", $data);
    }

    function save_properties($data)
    {
        $this->db->insert("ota_properties", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }

    function update_properties($data)
    {
        $this->db->where('ota_manager_id', $data['channex_id']);
        $this->db->where('company_id', $data['company_id']);
        $this->db->update("ota_properties", $data);
    }

    function get_token($channex_id = null, $company_id = null){
        $this->db->from('ota_manager');

        if($channex_id)
           $this->db->where('id', $channex_id);

        if($company_id)
           $this->db->where('company_id', $company_id);

        $query = $this->db->get();

        $result = $query->row_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return null;
    }

    function get_channex_data($company_id){
        $this->db->where('company_id', $company_id);
        $query = $this->db->get('ota_manager');

        $result = $query->row_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return null;
    }

    function get_data_by_email($email, $company_id = null){
        $this->db->from('ota_manager');
        $this->db->where('email', $email);

        if($company_id)
            $this->db->where('company_id', $company_id);

        $query = $this->db->get();

        $result = $query->row_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return null;
    }

    function get_properties_by_company_id($company_id, $channex_id){
        $this->db->from('ota_properties');
        $this->db->where('company_id', $company_id);
        $this->db->where('ota_manager_id', $channex_id);

        $query = $this->db->get();

        $result = $query->row_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return null;
    }

    function get_channex_x_company($property_id = null, $company_id = null, $ota_id = null){

        $this->db->from('ota_x_company');

        if($company_id)
            $this->db->where('company_id', $company_id);

        // TODO: fix it so ota id is being added to where condition
//        if($ota_id)
//            $this->db->where('ota_manager_id', $ota_id);

        if($property_id)
            $this->db->where('ota_property_id', $property_id);

        $query = $this->db->get();

        $result = $query->row_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }
        return null;
    }

    function save_channex_company($data, $is_update = false)
    {
        if($is_update){

            $this->db->where('company_id', $data['company_id']);
            $this->db->where('ota_property_id', $data['ota_property_id']);
            $this->db->update('ota_x_company', $data);
            
            if ($this->db->_error_message())
                show_error($this->db->_error_message());
                
            //TO DO: error reporting for update fail.
            return TRUE;
        } else {
            $this->db->insert("ota_x_company", $data);

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }

            $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
            $result = $query->result_array();
            if(isset($result[0]))
            {  
                return $result[0]['last_id'];
            }
            else
            {  
                return null;
            }
        }
    }

    function create_or_update_room_type($ota_x_company_id, $ota_room_type_id, $minical_room_type_id, $company_id)
    {
        if($this->get_room_type($ota_x_company_id, $ota_room_type_id, null)){
            return $this->update_room_type($ota_x_company_id, $ota_room_type_id, $minical_room_type_id);
        }
        $data = array (
            'ota_x_company_id' => $ota_x_company_id,
            'ota_room_type_id' => $ota_room_type_id,
            'minical_room_type_id' => $minical_room_type_id,
            'company_id' => $company_id
        );
        
        $this->db->insert('ota_room_types', $data);
        
        // if there's an error in query, show error message
        if ($this->db->_error_message())
            show_error($this->db->_error_message());
        else // otherwise, return insert_id;
            return $this->db->insert_id();
    }
    
    function update_room_type($ota_x_company_id, $ota_room_type_id, $minical_room_type_id)
    {
        $this->db->where('ota_x_company_id', $ota_x_company_id);
        $this->db->where('ota_room_type_id', $ota_room_type_id);
        $data = array (
            'ota_x_company_id' => $ota_x_company_id,
            'ota_room_type_id' => $ota_room_type_id,
            'minical_room_type_id' => $minical_room_type_id
        );
        $data = (object) $data;
        $this->db->update('ota_room_types', $data);
        
        if ($this->db->_error_message())
            show_error($this->db->_error_message());
            
        //TO DO: error reporting for update fail.
        return TRUE;        
    }

    function get_room_type($ota_x_company_id, $ota_room_type_id, $minical_room_type_id = null)
    {
        if(isset($ota_room_type_id) && $ota_room_type_id)
        {
            $this->db->where('ota_room_type_id', $ota_room_type_id);
        }
        if(isset($minical_room_type_id) && $minical_room_type_id)
        {
            $this->db->where('minical_room_type_id', $minical_room_type_id);
        }
        $this->db->where('ota_x_company_id', $ota_x_company_id);
        
        $query = $this->db->get('ota_room_types');

        if ($query->num_rows() == 1)
        {
            return $query->row();
        }
        else
        {
            return NULL;
        }
    }

    function create_or_update_rate_plan($ota_x_company_id, $ota_room_type_id, $minical_rate_plan_id, $ota_rate_plan_id, $company_id)
    {
        if($this->get_rate_plan($ota_x_company_id, $ota_room_type_id, $minical_rate_plan_id, $ota_rate_plan_id)){
            return $this->update_rate_plan($ota_x_company_id, $ota_room_type_id, $minical_rate_plan_id, $ota_rate_plan_id, $company_id);
        } else {
            $data = array (
                'ota_x_company_id' => $ota_x_company_id,
                'ota_room_type_id' => $ota_room_type_id,
                'minical_rate_plan_id' => $minical_rate_plan_id,
                'ota_rate_plan_id' => $ota_rate_plan_id,
                'company_id' => $company_id
            );

            $this->db->insert('ota_rate_plans', $data);
            
            // if there's an error in query, show error message
            if ($this->db->_error_message())
                show_error($this->db->_error_message());
            else // otherwise, return insert_id;
                return $this->db->insert_id();
        }
        
    }
    
    function update_rate_plan($ota_x_company_id, $ota_room_type_id, $minical_rate_plan_id, $ota_rate_plan_id, $company_id)
    {
        $this->db->where('ota_x_company_id', $ota_x_company_id);
        $this->db->where('ota_room_type_id', $ota_room_type_id);
        $this->db->where('ota_rate_plan_id', $ota_rate_plan_id);
        $this->db->where('company_id', $company_id);

        $data = array (
            'ota_x_company_id' => $ota_x_company_id,
            'ota_room_type_id' => $ota_room_type_id,
            'minical_rate_plan_id' => $minical_rate_plan_id,
            'ota_rate_plan_id' => $ota_rate_plan_id
        );
        $data = (object) $data;
        $this->db->update('ota_rate_plans', $data);
        
        if ($this->db->_error_message())
            show_error($this->db->_error_message());
            
        //TO DO: error reporting for update fail.
        return TRUE;        
    }
    
    function get_rate_plan($ota_x_company_id, $ota_room_type_id, $minical_rate_plan_id, $ota_rate_plan_id)
    {
        $this->db->where('ota_x_company_id', $ota_x_company_id);
        $this->db->where('ota_room_type_id', $ota_room_type_id);
        if($ota_rate_plan_id)
            $this->db->where('ota_rate_plan_id', $ota_rate_plan_id);
        $query = $this->db->get('ota_rate_plans');
        
        if ($query->num_rows() == 1)
        {
            return $query->row();
        }
        else
        {
            return NULL;
        }
    }

    function get_channex_room_types($company_id, $ota_manager_id)
    {
        $this->db->from('ota_room_types as ort, ota_x_company as oxc');      
        $this->db->where('ort.ota_x_company_id = oxc.ota_x_company_id');
        $this->db->where('oxc.is_active = 1');
        $this->db->where('oxc.ota_manager_id', $ota_manager_id);
        $this->db->where('oxc.company_id', $company_id);
        
        $query = $this->db->get();
        
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
                    
        if ($query->num_rows >= 1) 
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_channex_rate_plans($company_id, $ota_manager_id)
    {
        $this->db->from('ota_rate_plans as orp, ota_x_company as oxc');      
        $this->db->where('orp.ota_x_company_id = oxc.ota_x_company_id');
        $this->db->where('oxc.is_active = 1');
        $this->db->where('oxc.ota_manager_id', $ota_manager_id);
        $this->db->where('oxc.company_id', $company_id);
        
        $query = $this->db->get();
        
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
                    
        if ($query->num_rows >= 1) 
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_channex_room_types_by_id($room_type_id = null, $company_id = null)
    {
        $this->db->from('ota_room_types'); 
        
        if($room_type_id)     
            $this->db->where_in('minical_room_type_id', $room_type_id);

        if($company_id)
            $this->db->where('company_id', $company_id);
        
        $query = $this->db->get();
        
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
                    
        if ($query->num_rows >= 1) 
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_channex_rate_plans_by_id($rate_plan_id = null, $company_id = null)
    {
        $this->db->from('ota_rate_plans');  

        if($rate_plan_id)
            $this->db->where('minical_rate_plan_id', $rate_plan_id);

        if($company_id)
            $this->db->where('company_id', $company_id);
        
        $query = $this->db->get();
        
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
                    
        if ($query->num_rows >= 1) 
        {
            $result =  $query->result_array();
            return $result;
        }
        return NULL;
    }

    function get_minical_room_type_id($ota_room_type_id, $ota_x_company_id)
    {
        $this->db->from("ota_room_types");
        $this->db->where('ota_room_type_id', $ota_room_type_id);
        if($ota_x_company_id) {
            $this->db->where('ota_x_company_id', $ota_x_company_id);
        }
        $query = $this->db->get();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        $result = $query->row_array(0);
        if (isset($result['minical_room_type_id']))
        {
            return $result['minical_room_type_id'];
        }
        else
        {
            return null;
        }   
    }

    function get_minical_rate_plan_id($ota_rate_plan_id, $ota_room_type_id = null, $ota_x_company_id = null)
    {
        $this->db->from("ota_rate_plans");
        
        $this->db->where('ota_rate_plan_id', "$ota_rate_plan_id");
        
        if($ota_room_type_id) {
            $this->db->where('ota_room_type_id', $ota_room_type_id);
        }
        if($ota_x_company_id) {
            $this->db->where('ota_x_company_id', $ota_x_company_id);
        }
        $query = $this->db->get();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        $result = $query->row_array(0);
        if (isset($result['minical_rate_plan_id']) && $result['minical_rate_plan_id'])
        {
            return $result['minical_rate_plan_id'];
        }
        else
        {
            return null;
        }       
    }

    function deconfigure_channex($company_id){

        $this->db->where('company_id', $company_id);
        $this->db->delete('ota_manager');

        $this->db->where('company_id', $company_id);
        $this->db->delete('ota_properties');

        $this->db->where('company_id', $company_id);
        $this->db->delete('ota_x_company');

        $this->db->where('company_id', $company_id);
        $this->db->delete('ota_room_types');

        $this->db->where('company_id', $company_id);
        $this->db->delete('ota_rate_plans');

        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
    }

    function get_room_type_by_rate_plan_id($rate_plan_id)
    {
        $this->db->from('rate_plan as rp');
        $this->db->join('room_type as rt','rp.room_type_id=rt.id');
        $this->db->where('rp.rate_plan_id', $rate_plan_id);
        
        $query = $this->db->get();
        if ($this->db->_error_message()) // error checking
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1) 
        {   
            $q = $query->result_array();
            return $q[0];
        }
        
        return NULL;
    }

    function get_related_pms_booking_ids($ota_booking_id, $booking_type = null)
    {
        $this->db->select('ob.pms_booking_id');
        $this->db->from('ota_bookings as ob');
        $this->db->join('booking as b', 'ob.pms_booking_id = b.booking_id');
        $this->db->where('ob.ota_booking_id', $ota_booking_id);
        $this->db->where('b.is_deleted', 0);

        if($booking_type){
            $this->db->where('booking_type', $booking_type);
        }

        $query = $this->db->get();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        //echo $this->db->last_query();
        $existing_pms_booking_ids = array();
            
        if($query->num_rows > 0) {
            $resulting_array = $query->result_array();
            foreach ($resulting_array as $booking_id)
            {
                $existing_pms_booking_ids[] = $booking_id['pms_booking_id'];
            }
        }
    
        return $existing_pms_booking_ids;
    }

    function delete_bookings($booking_ids_to_be_deleted)
    {
        $data = Array('is_deleted' => '1');

        $this->db->where_in('booking_id', $booking_ids_to_be_deleted);
        $this->db->update("booking", $data);
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }

    function cancel_booking($booking_id, $cancellation_fee = "")
    {
        $data = Array('state' => CANCELLED);

        if(is_numeric($cancellation_fee)){
            $data["booking_notes"] = "CONCAT('Cancellation fee: ".$cancellation_fee.",\n', booking_notes)";
        }
        
        $this->db->where('booking_id', $booking_id);
        $this->db->update("booking", $data);
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }

    function get_booking($booking_id)
    {
        $sql="
            SELECT 
                c.customer_name as booking_customer_name,
                c.email as booking_customer_email,
                c.customer_type as booking_customer_type,
                b.state,
                b.is_deleted,
                b.source,
                b.booking_id,
                b.booking_customer_id,
                b.adult_count,
                b.children_count,
                b.rate,
                b.booking_notes,
                b.company_id,
                b.use_rate_plan,
                b.rate_plan_id,
                b.color,
                b.charge_type_id,
                b.housekeeping_notes,
                b.invoice_hash,
                b.guest_review,
                b.balance,
                (
                    SELECT 
                        MIN(brh1.check_in_date) as check_in_date
                    FROM
                        booking_block as brh1
                    WHERE
                        brh1.booking_id = b.booking_id
                ) as check_in_date,
                (
                    SELECT 
                        MAX(brh2.check_out_date) as check_out_date
                    FROM
                        booking_block as brh2
                    WHERE
                        brh2.booking_id = b.booking_id
                ) as check_out_date
                
            FROM booking as b
            LEFT JOIN customer as c ON c.customer_id = b.booking_customer_id
            WHERE b.booking_id = '$booking_id'

            ";
        
        $query = $this->db->query($sql);    

        //echo $this->db->last_query();

        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        
        $q = $query->row_array(0);
        
        return $q;
    }

    function insert_charges($charge_data)
    {
        $this->db->insert_batch('charge', $charge_data); 
        
        if ($this->db->_error_message())
        {
            return show_error($this->db->_error_message());
        }
    }

    function insert_payments($payment_data)
    {
        $this->db->insert_batch('payment', $payment_data); 
        
        if ($this->db->_error_message())
        {
            return show_error($this->db->_error_message());
        }
    }

    function update_booking($data)
    {
        $data = $data;
        
        $this->db->where('booking_id', $data['booking_id']);
        $this->db->update("booking", $data);
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }   
    }

    function update_booking_balance($booking_id) 
    {       
        $sql = "SELECT *,
                    IFNULL(
                    (
                        SELECT
                            ROUND(SUM((ch.amount * (1+IFNULL(percentage_total * 0.01, 0))) + IFNULL(flat_tax_total, 0)), 2) as charge_total
                        FROM
                            charge as ch
                        LEFT JOIN ( 
                            SELECT 
                                ct.name, 
                                ct.id, 
                                sum(IF(tt.is_percentage = 1, tax_rate, 0)) as percentage_total, 
                                sum(IF(tt.is_percentage = 0, tax_rate, 0)) as flat_tax_total
                            FROM charge_type_tax_list AS cttl, charge_type as ct, tax_type AS tt
                            WHERE 
                                ct.id = cttl.charge_type_id AND
                                tt.tax_type_id = cttl.tax_type_id AND 
                                tt.is_deleted = '0' AND
                                ct.is_deleted = '0'
                            GROUP BY ct.id
                        )t ON ch.charge_type_id = t.id
                        WHERE
                            ch.is_deleted = '0' AND
                            ch.booking_id = b.booking_id 
                        GROUP BY ch.booking_id
                    ), 0    
                ) as charge_total,
                IFNULL(
                    (
                        SELECT SUM(p.amount) as payment_total
                        FROM payment as p, payment_type as pt
                        WHERE
                            p.is_deleted = '0' AND
                            pt.is_deleted = '0' AND
                            p.payment_type_id = pt.payment_type_id AND
                            p.booking_id = b.booking_id

                        GROUP BY p.booking_id
                    ), 0
                ) as payment_total
            FROM booking as b
            LEFT JOIN booking_block as brh ON b.booking_id = brh.booking_id
            WHERE b.booking_id = '$booking_id'
        ";
        
        $query = $this->db->query($sql);
        $result = $query->result_array();
        $booking = null;
        if ($query->num_rows >= 1 && isset($result[0]))
        {
            $booking =  $result[0];
        }
        
        if($booking)
        {
            $forecast = $this->forecast_charges->_get_forecast_charges($booking_id, true);
            $forecast_extra = $this->forecast_charges->_get_forecast_extra_charges($booking_id, true);
            $booking_charge_total_with_forecast = (floatval($booking['charge_total']) + floatval($forecast['total_charges']) + floatval($forecast_extra));
            $data = array(
                'booking_id' => $booking_id,
                'balance' => round(floatval($booking_charge_total_with_forecast) - floatval($booking['payment_total']), 2),
                'balance_without_forecast' => round(floatval($booking['charge_total']) - floatval($booking['payment_total']), 2)
            );
            $this->update_booking($data);
            return $data['balance'];
        }
        return null;
    }

    function get_charges($booking_id, $exclude_deleted_bookings = false, $company_id = null)
    {
        $this->db->select('c.*');
        
        if($exclude_deleted_bookings) {
            $this->db->join('booking as b', 'b.booking_id = c.booking_id', 'left');
            $this->db->where('b.is_deleted', '0');
        }

        if($company_id) {
            $this->db->join('booking as b', 'b.booking_id = c.booking_id', 'left');
            $this->db->where('b.company_id', $company_id);
        }
        
        $this->db->where('c.booking_id', $booking_id);

        $this->db->where('c.is_deleted', '0');
        
        $query = $this->db->get("charge as c");
                
        if ($query->num_rows() > 0) {
            return $query->result_array();
        }
        else{
            return null;
        }
    }

    function get_payments($booking_id, $exclude_deleted_bookings = false)
    {
        $this->db->select('p.*');
        if($exclude_deleted_bookings) {
            $this->db->join('booking as b', 'b.booking_id = p.booking_id', 'left');
            $this->db->where('b.is_deleted', '0');
        }
        
        $this->db->where('p.booking_id', $booking_id);
        $this->db->where('p.is_deleted', 0);
        $query = $this->db->get('payment as p');
        
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return null;
        }
    }

    function get_booking_detail($booking_id)
    {
        $sql = "
            SELECT 
                c.customer_name as booking_customer_name,
                c.email as booking_customer_email,
                c.customer_type as booking_customer_type,
                b.state,
                b.is_deleted,
                b.source,
                b.booking_id,
                c.customer_id as booking_customer_id,
                b.adult_count,
                b.children_count,
                b.rate,
                b.booking_notes,
                b.company_id,
                b.use_rate_plan,
                b.rate_plan_id,
                b.color,
                b.charge_type_id,
                b.housekeeping_notes,
                b.invoice_hash,
                b.guest_review, 
                b.pay_period,
                (
                    SELECT 
                        MIN(brh1.check_in_date) as check_in_date
                    FROM
                        booking_block as brh1
                    WHERE
                        brh1.booking_id = b.booking_id
                ) as check_in_date,
                (
                    SELECT 
                        MAX(brh2.check_out_date) as check_out_date
                    FROM
                        booking_block as brh2
                    WHERE
                        brh2.booking_id = b.booking_id
                ) as check_out_date
                
            FROM booking as b
            LEFT JOIN customer as c ON c.customer_id = b.booking_customer_id
            WHERE b.booking_id = '$booking_id' 

            ";
        
        $query = $this->db->query($sql);    

        //echo $this->db->last_query();

        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        
        $q = $query->row_array(0);
        
        return $q;
    }

    function get_bookings($ota_booking_id, $ota_type = null, $booking_type = null, $limit = null)
    {
        
        $this->db->from('ota_bookings');
        $this->db->where('ota_booking_id', $ota_booking_id);
        // $this->db->where('ota_type', $ota_type);
        $this->db->order_by("id", "DESC");
        if ($booking_type)
        {
            $this->db->where('booking_type', $booking_type);
        }
        if ($limit)
        {
            $this->db->limit($limit);
        }

        $results = $this->db->get()->result_array();
            
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        //return result set as an associative array
        if (!empty($results))
        {
            return $results;
        }       
        return null;
    }

    function save_logs($data){
        $this->db->insert("ota_xml_logs", $data);   
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {  
            return $result[0]['last_id'];
        }
        else
        {  
            return null;
        }
    }

    function get_otas($key = null){
        $this->db->from('otas');  

        if($key)
            $this->db->where('key', $key);

        $query = $this->db->get();
        
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
                    
        if ($query->num_rows >= 1) 
        {
            $result =  $query->row_array();
            return $result;
        }
        return NULL;
    }

    function get_ota_xml_logs($limit){
        $this->db->from('ota_xml_logs');
        $this->db->order_by('xml_log_id', 'asc');
        $this->db->limit($limit);

        $query = $this->db->get();
        $result = $query->result_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result;
        }

        return NULL;
    }

    function delete_old_xml_logs($xml_log_ids){
        $this->db->where_in('xml_log_id', $xml_log_ids);
        $this->db->delete('ota_xml_logs');
    }

    function get_channex_extra_charges($company_id)
    {
        $this->db->from('ota_x_company as oxc');      
        $this->db->where('oxc.is_active = 1');
        $this->db->where('oxc.company_id', $company_id);
        
        $query = $this->db->get();
        
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
                    
        if ($query->num_rows >= 1) 
        {
            $result =  $query->row_array();
            return $result;
        }
        return NULL;
    }
}
