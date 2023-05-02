<?php

class Room_type_model extends CI_Model {

    function __construct()
    {        
        parent::__construct(); // Call the Model constructor

        $this->load->model('Image_model');
        $this->load->model('Room_model');
        $this->load->library('PHPRequests');        
    }

    // return company's room_types

    function get_room_type_availability($company_id, $ota_id, $start_date, $end_date, $adult_count = null, $children_count = null, $filter_can_be_sold_online = true, $company_group_id = null, $get_max_availability = true, $get_inventorysold = true, $get_closeout_status = true, $get_inventory = true, $company_access_key = null , $ota_key = null)
    {
        $inventory = array();
        
        $room_types = $this->get_room_types($company_id, $adult_count, $children_count, $company_group_id);
        if(count($room_types)!=0){
            foreach ($room_types as $room_type)
            {
                $inventory[$room_type['id']] = $room_type;
            }
        }
        
        $query = http_build_query(
            array(
                'start_date'                => $start_date,
                'ota_id'                    => $ota_id,
                'max_adults'                => $adult_count,
                'max_children'              => $children_count,
                'end_date'                  => $end_date,
                'room_types'                => array_keys($inventory),
                'filter_can_be_sold_online' => $filter_can_be_sold_online ? true : false,
                'get_max_availability'      => $get_max_availability,
                'get_inventorysold'         => $get_inventorysold,
                'get_closeout_status'       => $get_closeout_status,
                'get_inventory'             => $get_inventory,
                'company_id'                => $company_id,
                'ota_key'                   => $ota_key,
                'X-API-KEY'                 => $this->company_api_key ? $this->company_api_key : $company_access_key
            )
        );

        $req = $this->call_api($this->config->item('api_url').'/v1/inventory/availability?'.$query, array(), array(), 'GET');

        // $req = Requests::get($this->config->item('api_url').'/v1/inventory/availability?'.$query);
        // if response empty aka no availability, ensure actual array is created anyway
        if(isset($_GET['dev_mode']) && $_GET['dev_mode'] === getenv('DEVMODE_PASS')){
            echo $this->config->item('api_url').'/v1/inventory/availability?'.$query;
        }
//      print_r($req);die;
        
        // $avail_array = $req->body ? json_decode($req->body, true) : null;
        $avail_array = $req ? json_decode($req, true) : null;
        $avail_array = empty($avail_array) ? array() : $avail_array;

        foreach ($avail_array as $room_type_id => $room_type_availability) {
            $inventory[$room_type_id]['availability'] = $room_type_availability;
        }
        
        return $inventory;
    }

    public function call_api($api_url, $data, $headers, $method_type = 'POST'){

        $url = $api_url;
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            
        if($method_type == 'GET'){

        } else {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
               
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    // get net availability

    function get_room_types($company_id, $adult_count = null, $children_count = null, $company_group_id = null)
    {
        $this->db->select("rt.*, count(rp.rate_plan_id) as rate_plan_count");
        $this->db->from("room_type as rt");
        $this->db->join("rate_plan as rp", "rp.room_type_id = rt.id and rp.is_deleted != '1' and rp.is_selectable = '1'", "left");
        if($company_group_id)
        {
            $this->db->join("company_groups_x_company as cgxc", "cgxc.company_id = rt.company_id", "left");
            $this->db->where('cgxc.company_group_id', $company_group_id);
        }
        else
        {
            $this->db->where('rt.company_id', $company_id);
        }
        if($adult_count){
            $this->db->where("rt.max_adults >= $adult_count");
        }
        if($children_count){
            $this->db->where("rt.max_children >= $children_count");
        }
        if($adult_count || $children_count){
            $total_occupants = (int)$adult_count + (int)$children_count;
            $this->db->where("rt.max_occupancy >= $total_occupants");
            $this->db->where("rt.min_occupancy <= $total_occupants");
        }
        $this->db->where('rt.is_deleted', 0);
        $this->db->group_by("rt.id"); // needs to be in asc order, so newly added room types are in the bottom of the list
        $this->db->order_by("rt.sort", "asc"); // needs to be in asc order, so newly added room types are in the bottom of the list

        $query = $this->db->get();

        if ($query->num_rows >= 1) {
            return $query->result_array();
        }

        return null;
    }
    
    // max availability set by hotels

    function get_room_types_and_availabilities($company_id, $check_in_date, $check_out_date)
    {
        // get total room count by room typeecho $this->db->last_query();
        $this->db->select("rt.id, rt.name, rt.acronym, COUNT(r.room_id) as availability, rt.max_adults, rt.max_children, rt.max_occupancy, rt.min_occupancy, rt.default_room_charge");
        $this->db->from('room_type as rt, room as r');
        $this->db->where('rt.id = r.room_type_id');
        $this->db->where('rt.company_id', $company_id);
        $this->db->where('rt.is_deleted', 0);
        $this->db->where('r.is_deleted', 0);
        $this->db->order_by("rt.sort, rt.id", "asc"); // needs to be in asc order, so sorted room types are in the list
        $this->db->group_by("rt.id"); // needs to be in asc order, so newly added room types are in the bottom of the list

        $query = $this->db->get();

        $available_room_types = $query->result_array();

        //echo $this->db->last_query();
        
        $sql = "SELECT
                        count(DISTINCT brh.room_id) AS occupancy, `rt`.`id`
                    FROM
                        `room_type` AS rt
                    LEFT JOIN `room` AS r ON r.room_type_id = rt.id
                    LEFT JOIN `booking_block` AS brh
                    ON  r.room_id = brh.room_id AND 
                        brh.check_in_date < '$check_out_date' AND 
                        brh.check_out_date > '$check_in_date'
                    LEFT JOIN `booking` AS b ON b.booking_id = brh.booking_id
                    WHERE (b.state < 4 OR b.state = 7) AND `b`.`is_deleted` != '1' AND
                        `rt`.`company_id` = '$company_id' AND `b`.`company_id` = '$company_id' AND `rt`.`is_deleted` = 0 AND `r`.`is_deleted` = 0
                    GROUP BY
                        `rt`.`id`";
        $query = $this->db->query($sql);
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        $occupancies = $query->result_array();
        return array('available_room_types' => $available_room_types, 'occupancies' => $occupancies);
    }
    
    // return company's room_types

    function get_room_types_and_its_available_rooms_for_online_reservation($company_id, $check_in_date, $check_out_date)
    {
        // get list of room types with its number of rooms that can be booked online
        $this->db->select('rt.id, count(r.room_id) as availability, image_group_id');
        $this->db->from('room_type as rt');
        $this->db->join("room as r", "r.room_type_id = rt.id AND r.is_deleted = '0' AND r.can_be_sold_online = '1'", "left");
        $this->db->where('rt.company_id', $company_id);
        $this->db->where('rt.is_deleted', 0);
        $this->db->where('r.is_deleted', 0);
        $this->db->group_by("room_type_id"); // needs to be in asc order, so newly added room types are in the bottom of the list

        $query = $this->db->get();

        if ($this->db->_error_message()) {
            show_error($this->db->_error_message());
        }

        // room types with room count
        $available_room_types = $query->result_array();

        // 1 is Online Booking Engines OTA ID
        $max_avails = $this->get_room_type_max_availability($company_id, 1, $check_in_date, $check_out_date);

        foreach ($available_room_types as $key => $room_type) {
            foreach ($max_avails[$room_type['id']]['availability'] as $availability) {
                $available_room_types[$key]['availability'] = min($room_type['availability'], $availability['availability']);
            }
        }

        // get occupancies grouped by room_type
        $this->db->select('rt.id, count(DISTINCT brh.room_id) as occupancy');
        $this->db->from('room_type as rt, room as r, booking as b');
        $this->db->join("booking_block as brh", "r.room_id = brh.room_id AND brh.check_in_date < '$check_out_date' AND brh.check_out_date > '$check_in_date' ", "left");
        $this->db->where('brh.booking_id = b.booking_id');
        $this->db->where('b.state < 4');
        $this->db->where('rt.company_id', $company_id);
        $this->db->where('rt.id = r.room_type_id');
        $this->db->where('rt.is_deleted', 0);
        $this->db->where('r.is_deleted', 0);
        $this->db->where('r.can_be_sold_online', 1);

        $this->db->group_by("room_type_id"); // needs to be in asc order, so newly added room types are in the bottom of the list

        $query = $this->db->get();

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        $occupancies =  $query->result_array();

        foreach($available_room_types as $key => $room_type)
        {
            if ($room_type['availability'] < 1) {
                unset($available_room_types[$key]);
            }

            foreach($occupancies as $occupancy)
            {
                $availability = $room_type['availability'] - $occupancy['occupancy'];
                if ($room_type['id'] == $occupancy['id'])
                {
                    if ($availability > 0) {
                        // update the availability of the available room type
                        $available_room_types[$key] = array(
                            'id'             => $room_type['id'],
                            'availability'   => $availability,
                            'image_group_id' => $room_type['image_group_id']
                        );
                    } else {
                        unset($available_room_types[$key]);
                    }
                }
            }

        }

        return $available_room_types;
    }

    // return company's room_types and its availabilities
    // used for online reservation

    /*
        Eventually replace this with Minical API's availability
    */

    function get_room_type_max_availability($company_id, $channel, $start_date, $end_date)
    {
        $inventory = array();

        foreach ($this->get_room_types($company_id) as $room_type)
        {
            $inventory[$room_type['id']] = array('acronym' => $room_type['acronym']);
        }

        $query = http_build_query(
            array(
                'start_date'                => $start_date,
                'channel'                   => $channel,
                'end_date'                  => $end_date,
                'room_types'                => array_keys($inventory),
                'filter_can_be_sold_online' => true,
                'X-API-KEY'                 => $this->config->item('api_key')
            )
        );

        $req = Requests::get($this->config->item('api_url').'/v1/inventory/max_availability?'.$query);

        // if response empty aka no availability, ensure actual array is created anyway
        $avail_array = json_decode($req->body, true);
        $avail_array = empty($avail_array) ? array() : $avail_array;

        foreach ($avail_array as $room_type_id => $room_type_availability)
        {
            $inventory[$room_type_id]['availability'] = $room_type_availability;
        }

        return $inventory;
    }
    
    // return company's room_types and its availabilities

    function get_room_types_with_occupancies($company_id, $date_start, $date_end)
    {
    
        return NULL;
    }
    
    function create_room_type($company_id, $room_type, $acronym = '', $max_adults = 4, $max_children = 4)
    {
        $get_max_sort_order = $this->db->query('SELECT MAX(sort) AS MaxSort FROM room_type WHERE company_id = '.$company_id)->row_array();
        $sort = (isset($get_max_sort_order['MaxSort']) && $get_max_sort_order['MaxSort']) ? $get_max_sort_order['MaxSort']+1 : 1;
        
        $data = array (
            'company_id' => $company_id,
            'name' => $room_type,
            'acronym' => $acronym,
            'max_adults' => $max_adults,
            'max_children' => $max_children,
            //'sort' => $sort,
            'image_group_id' => $this->Image_model->create_image_group(ROOM_TYPE_IMAGE_TYPE_ID)
        );
        
        $this->db->insert('room_type', $data);
        //$room_type_id = $this->db->insert_id();
    $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
    $result = $query->result_array();
    if(isset($result[0]))
    {  
      $room_type_id = $result[0]['last_id'];
    }
    else
    {  
      $room_type_id = null;
    }

        // if there's an error in query, show error message
        if ($this->db->_error_message())
            show_error($this->db->_error_message());
        else // otherwise, return insert_id;
            return $room_type_id;
    }

    function add_new_room_type($data){
        $this->db->insert('room_type', $data);
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {  
            $room_type_id = $result[0]['last_id'];
        }
        else
        {  
            $room_type_id = null;
        }

        // if there's an error in query, show error message
        if ($this->db->_error_message())
            show_error($this->db->_error_message());
        else // otherwise, return insert_id;
            return $room_type_id;
    }
    
    function update_room_type($room_type_id, $data)
    {
        $data = (object) $data;
        $this->db->where('id', $room_type_id);
        $this->db->update('room_type', $data);
        
        if ($this->db->_error_message())
            show_error($this->db->_error_message());
            
        //TO DO: error reporting for update fail.
        return TRUE;        
    }

    function update_room_charge_type($room_charge_id, $old_id)
    {
        $data = array(
            'default_room_charge' => $room_charge_id
        );
        $this->db->where('default_room_charge', $old_id);
        $this->db->update('room_type', $data);

        if ($this->db->_error_message())
            show_error($this->db->_error_message());

        //TO DO: error reporting for update fail.
        return TRUE;
    }

    function update_charge_type_room_types($old_charge_type_ids, $new_charge_type_ids){

        $i = 0; $data = array();
        foreach ($old_charge_type_ids as $key => $value) {
            if($value && isset($new_charge_type_ids[$i]) && $new_charge_type_ids[$i]) {
                $data[] = " WHEN default_room_charge = $value THEN $new_charge_type_ids[$i] ";
            }
            $i++;
        }

        $sql = "UPDATE `room_type` SET `default_room_charge` = CASE ";
        foreach ($data as $k => $val) {
            $sql .= $val;
        }

        $sql .= " ELSE `default_room_charge`
                    END";

        $q = $this->db->query($sql);
    }
    
    //Delete currency and all related room rate entries
    function delete_room_type($room_type_id)
    {       
        $this->db->query('
            UPDATE room_type
            SET is_deleted = 1
            WHERE id = ' . $room_type_id );
        
        return TRUE;
    }

    function delete_room_type_by_company($company_id)
    {
        $this->db->where('company_id', $company_id);
        $this->db->delete('room_type');
    }
    
    function get_room_type($room_type_id)
    {
        $this->db->where('id', $room_type_id);
        $query = $this->db->get('room_type');
        
        if ($query->num_rows() == 1)
        {
            return $query->row_array(0);
        }
        else
        {
            return NULL;
        }
    }
    
    function get_room_type_by_room_id($room_id)
    {
        $this->db->where('r.room_id', $room_id);
        $this->db->where('r.room_type_id = rt.id');
        $query = $this->db->get('room as r, room_type as rt');
        
        
        if ($query->num_rows() > 0)
        {
            $result = $query->row_array(0);         
            return $result;
        }
        else
        {
            return NULL;
        }
    }
        
        function get_bookings($company_id = NULL, $start_date = NULL , $end_date = NULL, $depositType = NULL)
        {
            $amount_condition = "";
            if($depositType == '1')
            {
                $amount_condition = " AND p.amount > 0";
            }
            else if($depositType == '2')
            {
                $amount_condition = " AND p.amount IS NULL";
            }
            
            $query = "SELECT *, p.date_time as deposit_date
                FROM booking_block as brh 
                LEFT JOIN booking as b ON (brh.booking_id = b.booking_id)
                LEFT JOIN customer as c ON (c.customer_id = b.booking_customer_id)
                LEFT JOIN payment as p ON (b.booking_id = p.booking_id AND p.is_deleted != '1')
                LEFT JOIN booking_log as bl ON (bl.booking_id = b.booking_id)
                LEFT JOIN user_profiles as up ON (up.user_id = bl.user_id)
                WHERE brh.check_in_date >= '$start_date' AND check_in_date <= '$end_date' AND b.company_id = '$company_id' AND b.is_deleted != '1' AND bl.log_type = '1' $amount_condition GROUP BY b.booking_id";            
            
            $q = $this->db->query($query);

            // error checking
            if ($this->db->_error_message()) {
                show_error($this->db->_error_message());
            }

            if ($q->num_rows() > 0) {
                $result = $q->result_array();
                return $result;
            }
            return null;
            
        }

    function get_room_type_name($name, $company_id = null){

        $this->db->where('rt.name',$name);
        if($company_id){
            
            $this->db->where('rt.company_id',$company_id);
            $this->db->where('rt.is_deleted',0);
        }
        $query = $this->db->get('room_type as rt');

        if ($query->num_rows() > 0)
        {
            $result = $query->result_array(0);
            return $result;
        }
        else
        {
            return NULL;
        }
    }

    function delete_room_types($company_id){

        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("room_type", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
        
}
