<?php

class Option_model extends CI_Model {

    function add_option($option){
        $this->db->insert('options', $option);
        return $this->db->insert_id();
    }


    function get_option($option){

        $this->db->select('*');
        $this->db->where('option_name', $option);
        $query = $this->db->get('options');
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result_array = $query->result_array();

        return $result_array;

    }
    
    function get_option_by_company($option, $company_id, $is_require = false){

        $this->db->select('*');
        $this->db->where('option_name', $option);

        if(is_array($company_id)){
            $this->db->where_in('company_id', $company_id);
        } else {
            $this->db->where('company_id', $company_id);
        }

        if($is_require){
            $where = "(JSON_EXTRACT(option_value, '$.security_status') = 1)";
            $this->db->where($where);
        }
        $query = $this->db->get('options');
     
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result_array = $query->result_array();

        return $result_array;
    }

    function get_option_by_user($option, $user_id){

        $this->db->select('*');
        $this->db->where('option_name', $option);
        // $this->db->like('option_value', $user_id, 'both');

        $where = "(JSON_EXTRACT(option_value, '$.user_id') = $user_id)";
        $this->db->where($where);
        
        $query = $this->db->get('options');
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result_array = $query->result_array();

        return $result_array;
    }

    function get_options(){

        $this->db->select('*');
        $query = $this->db->get('options');
        $result_array = $query->result_array();
        return $result_array;

    }

    
    function update_option($option, $value, $autoload)
    {
        $data = array(
            "option_name" => $option,
            "option_value" => $value,
            "autoload" => $autoload
        );
        $this->db->where('option_name', $option);
        $this->db->update('options', $data);
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }else{
            if ($this->db->affected_rows() > 0){
                return TRUE;
            }else{
                return FALSE;
            }
        }
    }

    function update_option_company($option, $value, $company_id)
    {
        $data = array(
            "option_name" => $option,
            "option_value" => $value,
            "autoload" => 0
        );
        $this->db->where('company_id', $company_id);
        $this->db->where('option_name', $option);
        $this->db->update('options', $data);
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }else{
            if ($this->db->affected_rows() > 0){
                return TRUE;
            }else{
                return FALSE;
            }
        }
    }

    function delete_option($option, $user_id = null)
    {
        $this->db->where('option_name', $option);

        if($user_id){
            $this->db->like('option_value', $user_id, 'both');
        }
        $this->db->delete('options');
        if ($this->db->affected_rows() > 0){
            return TRUE;
        }else{
            return FALSE;
        }   
    }

    function get_data_by_json($option_name, $company_id)
    {
        $sql = "SELECT * FROM `options`
                WHERE 
                    option_name = 'loyalty_customer' AND
                    company_id = '$company_id';
                ";
        
        $query = $this->db->query($sql);
        if($query->num_rows() >= 1)
        {
            return $query->result_array();
        }
        
        return NULL;
    }

    function get_option_by_json_data($option_name, $option_value, $company_id){

        $this->db->select('*');
        //$this->db->where('option_name', $option);
        $this->db->where('company_id', $company_id);
    
        if($option_value){
            $where = "(JSON_EXTRACT(option_value, '$.".$option_name."') = '".$option_value."')";
            $this->db->where($where);
        }
        $query = $this->db->get('options');
       //echo  $this->db->last_query();
      
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result_array = $query->result_array();

        return $result_array;
    }

    function get_reset_by_json_data($option,$getdata){

        $this->db->select('*');
        $this->db->where('option_name', $option);
        //$this->db->where('company_id', $company_id);
        $where1 = "(JSON_EXTRACT(option_value, '$.email') = '".$getdata['email']."')";
        $this->db->where($where1);
        $where2 = "(JSON_EXTRACT(option_value, '$.attempt_time') > '".$getdata['attempt_time']."')";
        $this->db->where($where2);
        
        $query = $this->db->get('options');
       //echo  $this->db->last_query();
      
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        $resultArray = $query->result_array();
        $rowCount = count($resultArray); 
        return $rowCount;
    }

    function update_rateplan_policy_codes($old_option_ids, $new_option_ids) {

        $i = 0;
        $data = [];
        foreach ($old_option_ids as $key => $value) {
            if ($value) {
                $data[] = "WHEN policy_code = $value THEN $new_option_ids[$i]";
            }
            $i++;
        }

        $sql = "UPDATE `rate_plan` AS rp
                SET `policy_code` = CASE ";

        foreach ($data as $val) {
            $sql .= $val . " ";
        }

        $sql .= "ELSE policy_code END
                 WHERE `rp`.`company_id` = '$this->company_id'
                 AND `policy_code` IN (" . implode(',', $old_option_ids) . ")";

        $q = $this->db->query($sql);
    }

    function update_payment_policy_company_id($old_company_id) {
        $this->db->where('option_name', 'payment_policy');
        $result = $this->db->get('options')->result_array();

        if ($result) {
            foreach ($result as $row) {
                $data = json_decode($row['option_value'], true);
                $changed = false;

                if (is_array($data) && isset($data['company_id'])) {
                    if ((string)$data['company_id'] === (string)$old_company_id) {
                        $data['company_id'] = (string)$this->company_id;
                        $changed = true;
                    }
                }

                if ($changed) {
                    $updated_json = json_encode($data);
                    // Only update this specific row by ID
                    $this->db->where('option_id', $row['option_id']);
                    $this->db->update('options', ['option_value' => $updated_json]);
                }
            }
        }
    }

    function update_option_group_ids($old_ids, $new_ids) {
        if (count($old_ids) !== count($new_ids)) {
            throw new Exception("old_ids and new_ids count must match.");
        }

        $option_cases = [];
        $option_conditions = [];

        for ($i = 0; $i < count($old_ids); $i++) {
            $old = (int)$old_ids[$i];
            $new = (int)$new_ids[$i];

            $search = 'group_booking_total_counts_' . $old;
            $replace = 'group_booking_total_counts_' . $new;

            // Escape strings for safety
            $escaped_search = $this->db->escape_str($search);
            $escaped_replace = $this->db->escape_str($replace);

            $option_cases[] = "WHEN option_name LIKE '%$escaped_search%' THEN REPLACE(option_name, '$escaped_search', '$escaped_replace')";
            $option_conditions[] = "option_name LIKE '%$escaped_search%'";
        }

        if (!empty($option_cases)) {
            $escaped_company_id = $this->db->escape_str($this->company_id);

            $sql_posts = "
                UPDATE options
                SET option_name = CASE
                    " . implode("\n", $option_cases) . "
                    ELSE option_name
                END
                WHERE option_name LIKE '%group_booking_total_counts_%'
                  AND company_id = '$escaped_company_id'
                  AND (" . implode(" OR ", $option_conditions) . ")
            ";

            $this->db->query($sql_posts);
        }
    }

    function update_option_room_type_ids($old_ids, $new_ids) {
        if (count($old_ids) !== count($new_ids)) {
            throw new Exception("old_ids and new_ids count must match.");
        }

        // Build old → new room_type_id map
        $id_map = array_combine($old_ids, $new_ids);

        // Fetch only relevant options
        $this->db->where('company_id', $this->company_id);
        $this->db->like('option_name', 'group_booking_total_counts_');
        $query = $this->db->get('options');

        if ($query->num_rows()) {
            $options = $query->result_array();

            foreach ($options as $option) {
                $updated = false;

                $value = json_decode($option['option_value'], true);

                // Skip if not valid JSON
                if (!is_array($value)) continue;

                if (isset($value['room_type_id']) && isset($id_map[$value['room_type_id']])) {
                    $value['room_type_id'] = $id_map[$value['room_type_id']];
                    $updated = true;
                }

                if ($updated) {
                    $this->db->where('option_id', $option['option_id']);
                    $this->db->update('options', [
                        'option_value' => json_encode($value),
                    ]);
                }
            }
        }
    }

    function update_custom_currency_booking_ids($old_ids, $new_ids) {
        if (count($old_ids) !== count($new_ids)) {
            throw new Exception("old_ids and new_ids count must match.");
        }

        $option_cases = [];
        $option_conditions = [];

        for ($i = 0; $i < count($old_ids); $i++) {
            $old = (int)$old_ids[$i];
            $new = (int)$new_ids[$i];

            $search = 'custom_currency_' . $old;
            $replace = 'custom_currency_' . $new;

            // Escape strings for safety
            $escaped_search = $this->db->escape_str($search);
            $escaped_replace = $this->db->escape_str($replace);

            $option_cases[] = "WHEN option_name LIKE '%$escaped_search%' THEN REPLACE(option_name, '$escaped_search', '$escaped_replace')";
            $option_conditions[] = "option_name LIKE '%$escaped_search%'";
        }

        if (!empty($option_cases)) {
            $escaped_company_id = $this->db->escape_str($this->company_id);

            $sql_posts = "
                UPDATE options
                SET option_name = CASE
                    " . implode("\n", $option_cases) . "
                    ELSE option_name
                END
                WHERE option_name LIKE '%custom_currency%'
                  AND company_id = '$escaped_company_id'
                  AND (" . implode(" OR ", $option_conditions) . ")
            ";

            $this->db->query($sql_posts);
        }
    }

    function update_option_currency_booking_ids($old_ids, $new_ids) {
        if (count($old_ids) !== count($new_ids)) {
            throw new Exception("old_ids and new_ids count must match.");
        }

        // Build old → new room_type_id map
        $id_map = array_combine($old_ids, $new_ids);

        // Fetch only relevant options
        $this->db->where('company_id', $this->company_id);
        $this->db->like('option_name', 'custom_currency_');
        $query = $this->db->get('options');

        if ($query->num_rows()) {
            $options = $query->result_array();

            foreach ($options as $option) {
                $updated = false;

                $value = json_decode($option['option_value'], true);

                // Skip if not valid JSON
                if (!is_array($value)) continue;

                if (isset($value['currency_booking']) && isset($id_map[$value['currency_booking']])) {
                    $value['currency_booking'] = $id_map[$value['currency_booking']];
                    $value['company_id'] = $this->company_id;
                    $updated = true;
                }

                if ($updated) {
                    $this->db->where('option_id', $option['option_id']);
                    $this->db->update('options', [
                        'option_value' => json_encode($value),
                    ]);
                }
            }
        }
    }

    function update_derived_rate_plan_ids($old_ids, $new_ids) {
        if (count($old_ids) !== count($new_ids)) {
            throw new Exception("old_ids and new_ids count must match.");
        }

        $option_cases = [];
        $option_conditions = [];

        for ($i = 0; $i < count($old_ids); $i++) {
            $old = (int)$old_ids[$i];
            $new = (int)$new_ids[$i];

            $search = 'derived_rate_' . $old;
            $replace = 'derived_rate_' . $new;

            // Escape strings for safety
            $escaped_search = $this->db->escape_str($search);
            $escaped_replace = $this->db->escape_str($replace);

            $option_cases[] = "WHEN option_name LIKE '%$escaped_search%' THEN REPLACE(option_name, '$escaped_search', '$escaped_replace')";
            $option_conditions[] = "option_name LIKE '%$escaped_search%'";
        }

        if (!empty($option_cases)) {
            $escaped_company_id = $this->db->escape_str($this->company_id);

            $sql_option = "
                UPDATE options
                SET option_name = CASE
                    " . implode("\n", $option_cases) . "
                    ELSE option_name
                END
                WHERE option_name LIKE '%derived_rate_%'
                  AND company_id = '$escaped_company_id'
                  AND (" . implode(" OR ", $option_conditions) . ")
            ";

            $this->db->query($sql_option);
        }
    }

    function update_derived_rate_option_value_data($old_ids, $new_ids) {
        if (count($old_ids) !== count($new_ids)) {
            throw new Exception("old_ids and new_ids count must match.");
        }

        // Build old → new room_type_id map
        $id_map = array_combine($old_ids, $new_ids);

        // Fetch only relevant options
        $this->db->where('company_id', $this->company_id);
        $this->db->like('option_name', 'derived_rate_');
        $query = $this->db->get('options');

        if ($query->num_rows()) {
            $options = $query->result_array();

            foreach ($options as $option) {
                $updated = false;

                $value = json_decode($option['option_value'], true);

                // Skip if not valid JSON
                if (!is_array($value)) continue;

                if (isset($value['rate_plan_id']) && isset($id_map[$value['rate_plan_id']])) {
                    $value['rate_plan_id'] = $id_map[$value['rate_plan_id']];
                    $value['company_id'] = $this->company_id;
                    $updated = true;
                }

                if ($updated) {
                    $this->db->where('option_id', $option['option_id']);
                    $this->db->update('options', [
                        'option_value' => json_encode($value),
                    ]);
                }
            }
        }
    }

    function update_derived_parent_rate_option_value_data($old_ids, $new_ids) {
        if (count($old_ids) !== count($new_ids)) {
            throw new Exception("old_ids and new_ids count must match.");
        }

        // Build old → new room_type_id map
        $id_map = array_combine($old_ids, $new_ids);

        // Fetch only relevant options
        $this->db->where('company_id', $this->company_id);
        $this->db->like('option_name', 'derived_rate_');
        $query = $this->db->get('options');

        if ($query->num_rows()) {
            $options = $query->result_array();

            foreach ($options as $option) {
                $updated = false;

                $value = json_decode($option['option_value'], true);

                // Skip if not valid JSON
                if (!is_array($value)) continue;

                if (isset($value['parent_rate_plan']) && isset($id_map[$value['parent_rate_plan']])) {
                    $value['parent_rate_plan'] = $id_map[$value['parent_rate_plan']];
                    $updated = true;
                }

                if ($updated) {
                    $this->db->where('option_id', $option['option_id']);
                    $this->db->update('options', [
                        'option_value' => json_encode($value),
                    ]);
                }
            }
        }
    }

    function update_derived_parent_room_type_option_value_data($old_ids, $new_ids) {
        if (count($old_ids) !== count($new_ids)) {
            throw new Exception("old_ids and new_ids count must match.");
        }

        // Build old → new room_type_id map
        $id_map = array_combine($old_ids, $new_ids);

        // Fetch only relevant options
        $this->db->where('company_id', $this->company_id);
        $this->db->like('option_name', 'derived_rate_');
        $query = $this->db->get('options');

        if ($query->num_rows()) {
            $options = $query->result_array();

            foreach ($options as $option) {
                $updated = false;

                $value = json_decode($option['option_value'], true);

                // Skip if not valid JSON
                if (!is_array($value)) continue;

                if (isset($value['parent_room_type']) && isset($id_map[$value['parent_room_type']])) {
                    $value['parent_room_type'] = $id_map[$value['parent_room_type']];
                    $updated = true;
                }

                if ($updated) {
                    $this->db->where('option_id', $option['option_id']);
                    $this->db->update('options', [
                        'option_value' => json_encode($value),
                    ]);
                }
            }
        }
    }
}

?>