<?php

class Import_mapping_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function insert_import_mapping($data)
    {

        $this->db->insert('import_mapping', $data);

        // $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        // $result = $query->result_array();
        // if(isset($result[0]))
        // {
        //     return $result[0]['last_id'];
        // }
        // else
        // {
        //     return null;
        // }
    }

    function get_mapping_customer_id($customer_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $customer_id);
        $this->db->where('type', 'customer');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }


    function get_mapping_booking_id($booking_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $booking_id);
        $this->db->where('type', 'booking');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_mapping_booking_room_history_id($booking_room_history_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $booking_room_history_id);
        $this->db->where('type', 'booking_block');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_mapping_group_booking_id($group_booking_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $group_booking_id);
        $this->db->where('type', 'group_booking');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_booking_extras($booking_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $booking_id);
        $this->db->where('type', 'extra_booking');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_extra_mapping($extra_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $extra_id);
        $this->db->where('type', 'extra');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_mapping_room_type_id($room_type_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $room_type_id);
        $this->db->where('type', 'room_type');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_mapping_room_id($room_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $room_id);
        $this->db->where('type', 'room');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_mapping_charge_id($charge_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $charge_id);
        $this->db->where('type', 'charge_type');

        $query = $this->db->get('import_mapping');
        // lq();
        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }
    function get_mapping_tax_id($tax_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $tax_id);
        $this->db->where('type', 'tax_type');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_mapping_charge($charge_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $charge_id);
        $this->db->where('type', 'charge');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_mapping_payment_id($payment_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $payment_id);
        $this->db->where('type', 'payment');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_rates_mapping_id($rate_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $rate_id);
        $this->db->where('type', 'rate');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_rate_plan_mapping_id($rate_plan_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $rate_plan_id);
        $this->db->where('type', 'rate_plan');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function get_mapping_statement_id($statement_id){

        $this->db->where('company_id', $this->company_id);
        $this->db->where('old_id', $statement_id);
        $this->db->where('type', 'statement');

        $query = $this->db->get('import_mapping');

        if ($query->num_rows >= 1)
            return $query->row_array();
        return NULL;

    }

    function delete_mapping_field($company_id){
        $this->db->where('company_id', $company_id);
        $this->db->delete("import_mapping");

    }


}