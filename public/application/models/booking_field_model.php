<?php
class Booking_field_model extends CI_Model {

    function create_booking_field($company_id, $name)
    {
        $data = array(
            'name' => $name,
            'company_id' => $company_id
        );

        $this->db->insert('booking_field', $data);
        return $this->db->insert_id();
    }

    function update_booking_field($id, $data)
    {
        $data = (object) $data;
        $this->db->where('id', $id);
        $this->db->update('booking_field', $data);
        return TRUE;
    }

    function get_booking_fields($company_id, $where_field = false){
        $this->db->where('company_id', $company_id);
        $this->db->where('is_deleted', 0);

        if ($where_field) {
            $this->db->where($where_field, 1);
            $this->db->order_by("id", "asc");
        }

        $query = $this->db->get('booking_field');

        if ($query->num_rows >= 1)
            return $query->result_array();
        return NULL;
    }
}