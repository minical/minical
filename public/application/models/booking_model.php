<?php

class Booking_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->load->library("Forecast_charges");   
    }

    function insert_ota_booking($booking)
    {     
        $this->db->insert('ota_bookings', $booking);
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
    
    
    function get_latest_bookings($company_id = null)
    {
        $sql = "SELECT b.booking_id FROM `booking` as b
                LEFT JOIN booking_block as brh on b.booking_id = brh.booking_id
                WHERE 
                    (brh.check_out_date >= '2020-04-20' and brh.check_in_date <= '2020-04-21') AND
                    b.is_deleted = 0
                ";
        if($company_id){
            $sql .= " and b.company_id = '$company_id'";
        }
        $query = $this->db->query($sql);
        if($query->num_rows() >= 1)
        {
            return $query->result_array();
        }
        
        return NULL;
    }

    function get_bookings_by_group_id($group_id, $is_select_balacne = false)
    {
        $select_balacne = '';
        if($is_select_balacne)
        {
            $select_balacne = ", booking.balance";
        }
        $sql = "
                SELECT
                    booking_x_booking_linked_group.booking_id,
                    booking_x_booking_linked_group.booking_group_id,
                    booking_block.room_id,
                    room.room_name $select_balacne
                FROM
                    booking_x_booking_linked_group
                LEFT JOIN
                    booking_block ON booking_x_booking_linked_group.booking_id = booking_block.booking_id
                LEFT JOIN
                    booking ON booking.booking_id = booking_block.booking_id
                LEFT JOIN
                    room ON booking_block.room_id = room.room_id
                WHERE booking_group_id = $group_id AND booking.is_deleted = 0";
        $query = $this->db->query($sql);
        if($query->num_rows() >= 1)
        {
            return $query->result_array();
        }
        return NULL; 

              
    }

    function get_all_group_booking_customers($group_id)
    {
        $sql = "
                SELECT
                    DISTINCT c.customer_id, c.*,
                    CASE WHEN b.booking_customer_id = c.customer_id THEN 'Guest'
                    WHEN b.booked_by = c.customer_id THEN 'Booked By' END as cust_type
                FROM
                    customer as c
                LEFT JOIN
                    booking as b ON (b.booking_customer_id = c.customer_id OR b.booked_by = c.customer_id)
                LEFT JOIN
                    booking_x_booking_linked_group as bxblg ON bxblg.booking_id = b.booking_id
                WHERE bxblg.booking_group_id = $group_id AND b.is_deleted = 0";
        $query = $this->db->query($sql);
        if($query->num_rows() >= 1)
        {
            return $query->result_array();
        }
        return NULL; 
    }
    
//     function get_ota_bookings($ota_booking_id = null)
//     {
//         $sql = "SELECT * FROM booking as b
//                 left join booking_block as brh on brh.booking_id = b.booking_id
//                 where b.booking_notes LIKE 'created via Booking.com. Booking ID: ".$ota_booking_id."%'
//                 and b.state != 4 and b.is_deleted = 0";
        
//         $query = $this->db->query($sql);
// //        echo $this->db->last_query();
//         if($query->num_rows() >= 1)
//         {
//             return $query->result_array();
//         }
//         return array();
//     }
    
    //get booking table based on filters
    //elements of the filter array are: start_date, end_date, state, order_by, order, offset, num
    function get_bookings($filters, $company_id = 0, $group_by1 = NULL, $include_room_no_assigned = false, $include_customer_all_details = false)
    {
        if ($company_id == 0)
        {
            $company_id = $this->session->userdata('current_company_id');
        }
        
        $sql = $this->_build_get_bookings_sql_query_based_on_filters($filters, $company_id, $group_by1, $include_room_no_assigned, $include_customer_all_details);
//                echo $sql;
        $q = $this->db->query($sql);    

        //echo $this->db->last_query();
        
        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        

        $result = $q->result_array();       
        
        return $result;
    }
    
    function get_group_bookings($filters, $company_id = 0, $group_by1 = NULL)
    {
        if ($company_id == 0)
        {
            $company_id = $this->session->userdata('current_company_id');
        }
        
        $sql = $this->_build_get_group_bookings_sql_query_based_on_filters($filters, $company_id, $group_by1);

        $q = $this->db->query($sql);    

        //echo $this->db->last_query();
        
        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        

        $result = $q->result_array();       
//      echo "<pre>"; print_r($result);die;
        return $result;
    }
    
    
    function get_booking($booking_id, $is_company = true, $is_booking_history = true)
    {   
        $this->db->where('b.booking_id', $booking_id);
        $this->db->from('booking as b');

        if($is_booking_history)
            $this->db->join('booking_block as bb', 'bb.booking_id = b.booking_id');

        if($is_company)
            $this->db->join('company as c', 'c.company_id = b.company_id');

        $query = $this->db->get();
        $result = $query->result_array();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result[0];
        }
        return null;
    }
    

    function delete_booking($booking_id)
    {
        $data = Array('is_deleted' => '1');

        $this->db->where('booking_id', $booking_id);
        $this->db->update("booking", $data);
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }

    // check if the booking belongs to the company
    // called from email_invoice() in Invoice controller
    function booking_belongs_to_company($booking_id, $company_id) 
    {
        $this->db->where('company_id', $company_id);
        $this->db->where('booking_id', $booking_id);
        $query = $this->db->get('booking');
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        $q = sizeof($query->result());
        return $q != 0;
    }

    // get the bookings total based on customer id
    function customer_total_bookings($customer_id, $not_deleted = false)
    {
        $this->db->where('booking_customer_id', $customer_id);
        // To exclude deleted bookings
        if ($not_deleted)
            $this->db->where('is_deleted', 0);

        $query = $this->db->get('booking');

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        return $query->num_rows();
    }

    // return row count for pagination
    function get_found_rows()
    {
        $sql = "SELECT FOUND_ROWS() as count;";     
        $q = $this->db->query($sql);
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($q) 
        {
            $result = $q->row_array(0);
            return $result['count'];            
        }
        
        return 0;
    }
    
    function _build_get_bookings_sql_query_based_on_filters($filters, $company_id, $group_by1, $include_room_no_assigned = false, $include_customer_all_details = false) 
    {
        $booking_fields_join = $customer_fields_join = '';
        $booking_fields_where_condition = $customer_fields_where_condition = '';

        if(isset($filters['search_query']) && $filters['search_query'])
        {
            $search_query = $filters['search_query'];

            $booking_fields_join = ' LEFT JOIN booking_x_booking_field AS bxbf ON bxbf.booking_id = b.booking_id';
            $booking_fields_where_condition = " OR bxbf.value like '%$search_query%'";
            

            $customer_fields_join = ' LEFT JOIN customer_x_customer_field AS pcxcf ON pcxcf.customer_id = c.customer_id
                                      LEFT JOIN customer_x_customer_field AS scxcf ON scxcf.customer_id = sg.customer_id';
            $customer_fields_where_condition = " OR pcxcf.value like '%$search_query%' OR scxcf.value like '%$search_query%'";
        }

        $where_conditions = $this->_get_where_conditions($filters, $company_id, $include_room_no_assigned, $booking_fields_where_condition, $customer_fields_where_condition);
                
        $where_condition_for_balance = "";
        $where_statement_date = '';
        if(isset($filters['statement_date']) && $filters['statement_date']){
            $where_statement_date = " AND ch.selling_date < '".$filters['statement_date']."' ";
        }
        
        $where_booking_ids = "";
        if(isset($filters['booking_ids']) && $filters['booking_ids']){
            $booking_ids = implode(',', $filters['booking_ids']);
            $where_booking_ids = " AND b.booking_id IN ($booking_ids) ";
        }
        
        if (isset($filters['show_paid'])) 
        {
            if($filters['show_paid'] === "paid_only") 
            {
                $where_condition_for_balance = 'WHERE balance <= 0';
            } 
            elseif ($filters['show_paid'] === "unpaid_only") 
            {
                $where_condition_for_balance = 'WHERE balance > 0';
            }
        }
        

        $order_by = "ORDER BY room_name";
        
        //if both $start and $end are set, then return occupancies within range
        if (isset($filters['order_by'])) 
        {
            if ($filters['order_by'] != "") 
            {
                $order_by = "ORDER BY ".$filters['order_by'];
            }
        }
                
        $group_by = "GROUP BY booking_id";
                if($group_by1){
                    $group_by = $group_by . ' , '.$group_by1;
                }
        if (isset($filters['group_by'])) 
        {
            if ($filters['group_by'] == 'booking_room_history_id')
            {
                $group_by = 'GROUP BY booking_room_history_id';
            }
        }
        
        // set order
        $order = "ASC";
        if (isset($filters['order'])) 
        {
            if ($filters['order'] == 'DESC')
            {
                $order = "DESC";
            }
        }
        
        // set limit
        $limit = "";
        if (isset($filters['offset'])) 
        {
                    $limit = "LIMIT ".intval($filters['offset']);
                    if (isset($filters['per_page'])) 
                    {
                        $limit = $limit.", ".$filters['per_page'];
                    }
                }
        
        $select_statements = $select_group_concat_statements = $join_statements = $where_statements = "";
        if (isset($filters['with_booking_statements']) && $filters['with_booking_statements']) 
        {
            $select_statements = ", x.booking_statements";
            $select_group_concat_statements = ", GROUP_CONCAT(DISTINCT CONCAT( s.statement_id, '_',  s.statement_number)) as booking_statements";
            $join_statements = "LEFT JOIN booking_x_statement AS bs ON bs.booking_id = b.booking_id 
                                LEFT JOIN statement AS s ON bs.statement_id = s.statement_id AND s.is_deleted = 0 ";
        
            if (isset($filters['in_statement']) && $filters['in_statement']) 
            {
                $where_statements .= ' WHERE x.booking_statements IS NOT NULL ';
            }
            elseif (isset($filters['in_statement']) && !$filters['in_statement']) 
            {
                $where_statements .= ' WHERE x.booking_statements IS NULL ';
            }
        }
        
        $include_charge_payment_total = ", IFNULL(
                        (
                            IFNULL(
                                (
                                    SELECT SUM(p.amount) as payment_total
                                    FROM payment as p, payment_type as pt
                                    WHERE
                                        p.is_deleted = '0' AND
                                        pt.is_deleted = '0' AND
                                        p.payment_type_id = pt.payment_type_id AND
                                        p.booking_id = x.booking_id

                                    GROUP BY p.booking_id
                                ), 0
                            ) + x.balance_without_forecast
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
                                p.booking_id = x.booking_id
                                
                            GROUP BY p.booking_id
                        ), 0
                    ) as payment_total";
        
        if (isset($filters['not_include_charge_payment_total']) && $filters['not_include_charge_payment_total']) 
        {
            $include_charge_payment_total = "";
        }
        
        $external_select_customer_type = $select_customer_type = $join_customer_type = "";
        if (isset($filters['include_customer_type']) && $filters['include_customer_type']) 
        {
            $external_select_customer_type = "x.customer_type_name, 
                                            x.adult_count, 
                                            x.children_count, 
                                            x.value,
                                            x.custom_field_value,
                                            x.staying_customer_id,";
            
            $select_customer_type = "ct.name as customer_type_name, 
                                    b.adult_count,
                                    b.children_count,
                                    cxcf.value,
                                    GROUP_CONCAT(DISTINCT cxcf2.value, ' ') as custom_field_value,
                                    GROUP_CONCAT(DISTINCT sg.customer_id, ' ') as staying_customer_id,";
            
            $join_customer_type = "LEFT JOIN customer_type as ct ON ct.id = c.customer_type_id
                    LEFT JOIN customer_x_customer_field as cxcf ON cxcf.customer_id = b.booking_customer_id AND b.booking_customer_id != '0'
                    LEFT JOIN customer_field as cf ON cxcf.customer_field_id = cf.id
                    LEFT JOIN customer_x_customer_field as cxcf2 ON cxcf2.customer_field_id = cf.id AND bscl.customer_id = sg.customer_id AND cxcf2.customer_id = sg.customer_id
                    ";
            
        }
        
        $external_select_customer_details = $select_customer_details = "";
        if($include_customer_all_details)
        {
            $external_select_customer_details = "x.email,
                                                x.phone,
                                                x.phone2,
                                                x.fax,
                                                x.address,
                                                x.address2,
                                                x.city,
                                                x.region,
                                                x.country,
                                                x.postal_code,";

            $select_customer_details = "c.email,
                                        c.phone,
                                        c.phone2,
                                        c.fax,
                                        c.address,
                                        c.address2,
                                        c.city,
                                        c.region,
                                        c.country,
                                        c.postal_code,";
        }

        $select_room_name_combine = " GROUP_CONCAT(room_name) ";
        if (isset($filters['fetch_checkindate_with_room_name']) && $filters['fetch_checkindate_with_room_name'])
        {
            $select_room_name_combine = " GROUP_CONCAT(CONCAT(room_name, '|$|', check_in_date)) ";
        }

        // SQL_CALC_FOUND_ROWS is used to calculate # of rows ignoring LIMIT. SELECT FOUND_ROWS() Must be called right after this query.
        // Which is called from get_found_rows() function.
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS 
                *
            FROM
                (SELECT
                    x.booking_id,
                    x.customer_type_id,
                    $external_select_customer_type
                    x.booking_customer_id,
                    x.group_id,
                    x.room_id, 
                    x.r_room_type_id,
                    x.rate_plan_id,
                    x.check_in_date,
                    x.check_out_date,
                    x.room_name,
                    x.groups_id,
                    x.is_group_booking,
                    x.group_name,
                    x.state,
                    x.color,
                    x.rate,
                    x.balance,
                    x.balance_without_forecast,
                    x.pay_period,
                    x.charge_type_id,
                    x.use_rate_plan,
                    x.booking_notes,
                    $external_select_customer_details
                    x.customer_notes,
                    x.source,
                    IF(x.customer_name IS NULL, '', x.customer_name) as customer_name,
                    x.guest_count,
                    x.staying_customers,
                    x.guest_name,
                    x.brh_room_type_id
                    $include_charge_payment_total
                    $select_statements
                FROM
                (
                    SELECT 
                        b.booking_id,
                        b.is_ota_booking,
                        b.rate_plan_id,
                        up.first_name,
                        up.last_name,
                        bxs.group_id AS groups_id,
                        blg.id AS is_group_booking,
                        blg.name AS group_name,
                        brh.room_id,
                        brh.room_type_id as brh_room_type_id,
                        r.room_type_id as r_room_type_id,
                        (
                            /* Room name combine */
                            SELECT $select_room_name_combine FROM room
                            LEFT JOIN booking_block on room.room_id = booking_block.room_id
                            WHERE booking_id = b.booking_id
                            ORDER BY booking_block.check_in_date DESC
                        ) as room_name,
                        
                        min(check_in_date) as check_in_date,
                        max(check_out_date) as check_out_date,
                        (
                            SELECT r.group_id 
                            FROM room as r 
                            JOIN room_type as rt ON r.group_id=rt.id 
                            WHERE rt.acronym='SN' 
                            GROUP BY r.group_id 
                            order by r.group_id DESC
                            LIMIT 0,1
                        ) as group_id,
                        b.state,
                        b.color,
                        b.rate,
                        b.balance,
                        b.balance_without_forecast,
                        IF(b.source > 20, (SELECT bs.name FROM booking_source as bs WHERE bs.id = b.source LIMIT 1), b.source) as source,
                        b.pay_period,
                        b.charge_type_id,
                        b.use_rate_plan,
                        b.booking_notes,
                        b.booking_customer_id,
                        c.customer_type_id,
                        $select_customer_type
                        c.customer_name,
                        $select_customer_details
                        c.customer_notes,
                        count(DISTINCT sg.customer_id) as guest_count,
                        GROUP_CONCAT(DISTINCT sg.customer_name, ' ') as staying_customers,
                        (   SELECT c2.customer_name
                                FROM customer as c2, booking_staying_customer_list as bscl2
                                WHERE 
                                        c2.customer_id = bscl2.customer_id AND 
                                        bscl2.booking_id = b.booking_id
                                LIMIT 0,1
                        ) as guest_name
                        
                        $select_group_concat_statements
                    FROM booking as b
                    $booking_fields_join
                    LEFT JOIN booking_log as bl ON bl.booking_id = b.booking_id
                    LEFT JOIN user_profiles as up ON up.user_id = bl.user_id
                    LEFT JOIN booking_staying_customer_list as bscl ON bscl.booking_id = b.booking_id
                    LEFT JOIN customer as sg ON bscl.customer_id = sg.customer_id 
                    LEFT JOIN booking_block as brh ON brh.booking_id = b.booking_id AND brh.check_out_date = check_out_date
                    LEFT JOIN room as r ON brh.room_id = r.room_id 
                    LEFT JOIN customer as c ON c.customer_id = b.booking_customer_id AND b.booking_customer_id != '0'
                    $customer_fields_join
                    $join_customer_type
                    LEFT JOIN booking_x_group AS bxs ON bxs.booking_id = brh.booking_id 
                    LEFT JOIN booking_x_booking_linked_group AS bxblg ON bxblg.booking_id = brh.booking_id 
                    LEFT JOIN booking_linked_group AS blg ON blg.id = bxblg.booking_group_id
                    $join_statements
                    WHERE
                        $where_conditions
                        $where_booking_ids
                    $group_by

                )x
                $where_statements
            )y

            $where_condition_for_balance

            $order_by $order 
            
            $limit
        ";

        return $sql;
    }
    
    
    function _build_get_group_bookings_sql_query_based_on_filters($filters, $company_id, $group_by1) 
    {

        $where_conditions = $this->_get_group_where_conditions($filters, $company_id);
                
        $where_condition_for_balance = "";
        $where_statement_date = '';
        if(isset($filters['statement_date']) && $filters['statement_date']){
            $where_statement_date = " AND ch.selling_date < '".$filters['statement_date']."' ";
        }
        
        $where_booking_ids = "";
        if(isset($filters['booking_ids']) && $filters['booking_ids']){
            $booking_ids = implode(',', $filters['booking_ids']);
            $where_booking_ids = " AND b.booking_id IN ($booking_ids) ";
        }
        
        if (isset($filters['show_paid'])) 
        {
            if($filters['show_paid'] === "paid_only") 
            {
                $where_condition_for_balance = 'WHERE balance <= 0';
            } 
            elseif ($filters['show_paid'] === "unpaid_only") 
            {
                $where_condition_for_balance = 'WHERE balance > 0';
            }
        }
        

        $order_by = "ORDER BY group_id";
        
        //if both $start and $end are set, then return occupancies within range
        if (isset($filters['order_by'])) 
        {
            if ($filters['order_by'] != "") 
            {
                $order_by = "ORDER BY ".$filters['order_by'];
            }
        }
                
        $group_by = "GROUP BY booking_id";
                if($group_by1){
                    $group_by = $group_by . ' , '.$group_by1;
                }
        if (isset($filters['group_by'])) 
        {
            if ($filters['group_by'] == 'booking_room_history_id')
            {
                $group_by = 'GROUP BY booking_room_history_id';
            }
        }
        
        // set order
        $order = "ASC";
        if (isset($filters['order'])) 
        {
            if ($filters['order'] == 'DESC')
            {
                $order = "DESC";
            }
        }
        
        // set limit
        $limit = "";
        if (isset($filters['offset'])) 
        {
                    $limit = "LIMIT ".intval($filters['offset']);
                    if (isset($filters['per_page'])) 
                    {
                        $limit = $limit.", ".$filters['per_page'];
                    }
                }
        
        $select_statements = $select_group_concat_statements = $join_statements = $where_statements = "";
        if (isset($filters['with_booking_statements']) && $filters['with_booking_statements']) 
        {
            $select_statements = ", x.booking_statements";
            $select_group_concat_statements = ", GROUP_CONCAT(DISTINCT CONCAT( s.statement_id, '_',  s.statement_number)) as booking_statements";
            $join_statements = "LEFT JOIN booking_x_statement AS bs ON bs.booking_id = b.booking_id 
                                LEFT JOIN statement AS s ON bs.statement_id = s.statement_id AND s.is_deleted = 0 ";
        
            if (isset($filters['in_statement']) && $filters['in_statement']) 
            {
                $where_statements .= ' WHERE x.booking_statements IS NOT NULL ';
            }
            elseif (isset($filters['in_statement']) && !$filters['in_statement']) 
            {
                $where_statements .= ' WHERE x.booking_statements IS NULL ';
            }
        }
        
        $include_charge_payment_total = ", IFNULL(
                        (
                            SELECT 
                                (SUM((ch.amount * (1+IFNULL(percentage_total * 0.01, 0))) + IFNULL(flat_tax_total, 0))) as charge_total
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
                                ch.booking_id = x.booking_id 
                                $where_statement_date
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
                                p.booking_id = x.booking_id
                                
                            GROUP BY p.booking_id
                        ), 0
                    ) as payment_total";
        
        if (isset($filters['not_include_charge_payment_total']) && $filters['not_include_charge_payment_total']) 
        {
            $include_charge_payment_total = "";
        }
        
        
        // SQL_CALC_FOUND_ROWS is used to calculate # of rows ignoring LIMIT. SELECT FOUND_ROWS() Must be called right after this query.
        // Which is called from get_found_rows() function.
        $sql = "
            SELECT SQL_CALC_FOUND_ROWS 
                *
            FROM
                (SELECT
                    x.booking_id,
                                        x.customer_type_id,
                    x.booking_customer_id,
                    x.group_id,
                    x.room_id, 
                    x.rate_plan_id,
                    x.check_in_date,
                    x.check_out_date,
                    x.room_name,
                    x.groups_id,
                    x.is_group_booking,
                    x.group_name,
                    x.state,
                    x.color,
                    x.rate,
                    x.balance,
                    x.balance_without_forecast,
                    x.pay_period,
                    x.charge_type_id,
                    x.use_rate_plan,
                    x.booking_notes,
                    x.customer_notes,
                    x.source,
                    IF(x.customer_name IS NULL, '', x.customer_name) as customer_name,
                    x.guest_count,
                    x.staying_customers,
                    x.guest_name
                    $include_charge_payment_total
                    $select_statements
                FROM
                (
                    SELECT 
                        b.booking_id,
                        b.is_ota_booking,
                        b.rate_plan_id,
                        up.first_name,
                        up.last_name,
                        bxs.group_id AS groups_id,
                        blg.id AS is_group_booking,
                        blg.name AS group_name,
                        brh.room_id, 
                        (
                            /* Room name combine */
                            SELECT GROUP_CONCAT(room_name) FROM room
                            LEFT JOIN booking_block on room.room_id = booking_block.room_id
                            WHERE booking_id = b.booking_id
                            ORDER BY booking_block.check_in_date DESC
                        ) as room_name, 
                        min(check_in_date) as check_in_date,
                        max(check_out_date) as check_out_date,
                        (
                            SELECT r.group_id 
                            FROM room as r 
                            JOIN room_type as rt ON r.group_id=rt.id 
                            WHERE rt.acronym='SN' 
                            GROUP BY r.group_id 
                            order by r.group_id DESC
                            LIMIT 0,1
                        ) as group_id,
                        b.state,
                        b.color,
                        b.rate,
                        b.balance,
                        b.balance_without_forecast,
                        IF(b.source > 20, (SELECT bs.name FROM booking_source as bs WHERE bs.id = b.source LIMIT 1), b.source) as source,
                        b.pay_period,
                        b.charge_type_id,
                        b.use_rate_plan,
                        b.booking_notes,
                        b.booking_customer_id,
                        c.customer_type_id,
                        c.customer_name,
                        c.customer_notes,
                        count(DISTINCT sg.customer_id) as guest_count,
                        GROUP_CONCAT(DISTINCT sg.customer_name, ', ') as staying_customers,
                        (   SELECT c2.customer_name
                            FROM customer as c2, booking_staying_customer_list as bscl2
                            WHERE 
                                c2.customer_id = bscl2.customer_id AND 
                                bscl2.booking_id = b.booking_id
                            LIMIT 0,1
                        ) as guest_name
                        $select_group_concat_statements
                    FROM booking as b
                    LEFT JOIN booking_log as bl ON bl.booking_id = b.booking_id
                    LEFT JOIN user_profiles as up ON up.user_id = bl.user_id
                    LEFT JOIN booking_staying_customer_list as bscl ON bscl.booking_id = b.booking_id
                    LEFT JOIN customer as sg ON bscl.customer_id = sg.customer_id 
                    LEFT JOIN booking_block as brh ON brh.booking_id = b.booking_id AND brh.check_out_date = check_out_date
                    LEFT JOIN room as r ON brh.room_id = r.room_id
                    LEFT JOIN room_type as rt ON rt.id=r.room_id
                    LEFT JOIN customer as c ON c.customer_id = b.booking_customer_id AND b.booking_customer_id != '0'
                    LEFT JOIN booking_x_group AS bxs ON bxs.booking_id = brh.booking_id 
                    LEFT JOIN booking_x_booking_linked_group AS bxblg ON bxblg.booking_id = brh.booking_id 
                    LEFT JOIN booking_linked_group AS blg ON blg.id = bxblg.booking_group_id
                    $join_statements
                    WHERE
                        $where_conditions
                        $where_booking_ids
                    $group_by

                )x
                $where_statements
            )y

            $where_condition_for_balance

            $order_by $order 
            
            $limit
        ";
                
        return $sql;
    }
    
    // Called from invoice too
    function get_booking_detail($booking_id, $is_booking_review = false)
    {
        $get_booking_review = "";
        $select_column = "";
        if($is_booking_review)
        {
            $get_booking_review = "LEFT JOIN booking_review as br ON br.booking_id = b.booking_id";
            $select_column = "br.*,";
        }
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
                b.booked_by,
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
                b.add_daily_charge,
                b.residual_rate,
                b.balance,
                b.is_ota_booking,
                r.room_name,
                rt.name as room_type_name,
                $select_column
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
            LEFT JOIN booking_block as bb ON bb.booking_id = b.booking_id
            LEFT JOIN room as r ON r.room_id = bb.room_id
            LEFT JOIN room_type as rt ON r.room_type_id = rt.id
            $get_booking_review
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
    
    function get_booking_staying_customers($booking_id, $company_id)
    {
        $this->db->select('booking_staying_customer_list.customer_id');
        $this->db->select('customer_name');
        $this->db->from('booking_staying_customer_list');
        $this->db->join('customer', 'customer.customer_id = booking_staying_customer_list.customer_id', 'left');
        $this->db->where('booking_staying_customer_list.booking_id', $booking_id);
        $this->db->where('booking_staying_customer_list.company_id', $company_id);
        $results = $this->db->get();
        
        if (empty($results))
        {
            return array();
        }
        else
        {
            return $results->result_array();
        }
    }

    function get_bookings_by_booking_id($booking_id_array)
    {
        $booking_ids = implode(",", $booking_id_array);
        if ($booking_ids == "")
        {
            return null;
        }
        
        $company_id = $this->session->userdata('current_company_id');
        
        $sql = "
            SELECT
                b.booking_id,
                brh.room_id,                
                r.room_name as room_name,
                min(brh.check_in_date) as check_in_date,
                max(brh.check_out_date) as check_out_date,
                b.state,
                c.customer_name
            FROM booking as b
            LEFT JOIN customer as c ON c.customer_id = b.booking_customer_id
            LEFT JOIN booking_block as brh ON brh.booking_id = b.booking_id
            LEFT JOIN room as r ON r.room_id = brh.room_id 
            WHERE
                b.booking_id IN ($booking_ids)
            GROUP BY b.booking_id
            ORDER BY check_out_date     
        ";
        
        $q = $this->db->query($sql);
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        $result = $q->result();     
        return $result;
    }

    function update_booking($booking_id, $data) 
    {       
        $data = (object) $data;
        $this->db->where('booking_id', $booking_id);
        $this->db->update("booking", $data);        
    }
    
    function fix_older_bookings_balance($start_date = null, $end_date = null){
        $sql = "UPDATE booking as b
                    LEFT JOIN booking_block as bh ON b.booking_id = bh.booking_id
                    LEFT JOIN (
                                    SELECT b.booking_id,
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
                                WHERE DATE(brh.check_in_date) = '$start_date'
                        ) as r ON b.booking_id = r.booking_id

                        SET b.balance = (r.charge_total - r.payment_total)
                        where DATE(bh.check_in_date) = '$start_date'";
        
        return $this->db->query($sql);
    }
    
    function update_booking_balance($booking_id, $return_type = 'balance') 
    {       
        if (!$booking_id) {
            return null;
        }
        
        $sql = "SELECT *,
                    IFNULL(
                    (
                        SELECT 
                            SUM(charge_amount) as charge_total
                        FROM (
                            SELECT
                               (
                                   ch.amount +
                                   SUM(
                                        IF(tt.is_tax_inclusive = 1,
                                            0,
                                            (ch.amount * IF(tt.is_percentage = 1, IF(tt.is_brackets_active, tpb.tax_rate, tt.tax_rate), 0) * 0.01) +
                                            IF(tt.is_percentage = 0, IF(tt.is_brackets_active, tpb.tax_rate, tt.tax_rate), 0)
                                        )
                                    )
                               ) as charge_amount                     
                            FROM charge as ch
                            LEFT JOIN charge_type as ct ON ch.charge_type_id = ct.id AND ct.is_deleted = '0'
                            LEFT JOIN charge_type_tax_list AS cttl ON ct.id = cttl.charge_type_id 
                            LEFT JOIN tax_type AS tt ON tt.tax_type_id = cttl.tax_type_id AND tt.is_deleted = '0'
                            LEFT JOIN tax_price_bracket as tpb 
                                ON tpb.tax_type_id = tt.tax_type_id AND ch.amount BETWEEN tpb.start_range AND tpb.end_range
                            WHERE
                                ch.is_deleted = '0' AND
                                ch.booking_id = '$booking_id'  
                            GROUP BY ch.charge_id
                        ) as total
                    ), 0    
                ) as charge_total,
                IFNULL(
                    (
                        SELECT SUM(p.amount) as payment_total
                        FROM payment as p, payment_type as pt
                        WHERE
                            p.is_deleted = '0' AND
                            #pt.is_deleted = '0' AND
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
                'balance' => $this->jsround(floatval($booking_charge_total_with_forecast) - floatval($booking['payment_total']), 2),
                'balance_without_forecast' => $this->jsround(floatval($booking['charge_total']) - floatval($booking['payment_total']), 2)
            );
            $this->update_booking($booking_id, $data);
            return $data[$return_type];
        }
        return null;
    }
    
    function jsround($float, $precision = 0){
        $float = floatval(number_format($float, 12, '.', ''));
        if($float < 0){
           return round($float, $precision, PHP_ROUND_HALF_DOWN);
        }
        return round($float, $precision);
    }
    
    function fix_booking_balance($company_id){
        $sql = "UPDATE booking as b 
                SET b.balance_without_forecast = 
                                ( 
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
                                )
                            ) - (
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
                                    )
                                )
                WHERE b.company_id = '$company_id'                    
                ";
        $this->db->query($sql);
        
    }
    
    function get_selling_date($company_id) {
        $selling_date = null;
        $this->db->select("selling_date");
        $this->db->where('company_id', $company_id);

        $q = $this->db->get("company");

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        foreach ($q->result() as $row) {
            $selling_date = $row->selling_date;
        }
        //echo $this->db->last_query();
        return $selling_date;
    }
    
    /*
    function update_booking($data, $company_id)
    {
        if (isset($data['staying_customer_ids']))
        {
            $staying_customer_ids = $data['staying_customer_ids'];
            unset($data['staying_customer_ids']);
        }
        
        $data = (object) $data;
        
        if ($company_id != null) 
        {
            $this->db->where('company_id', $company_id);
        }
        
        $this->db->where('booking_id', $data->booking_id);
        $this->db->update("booking", $data);
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        //Update booking staying customer list (delete then add)        
        //NOTE: this needs to be changed so that it checks for actually changes
        //and then only update if there is a difference.
        $this->db->where('booking_id', $data->booking_id);
        $this->db->delete('booking_staying_customer_list');

        if(isset($staying_customer_ids))
        {
            if ($staying_customer_ids != '')
            {
                foreach ($staying_customer_ids as $staying_customer_id) 
                {
                    $link_data = array(
                        'booking_id' => $data->booking_id,
                        'company_id' => $data->company_id,
                        'customer_id' => $staying_customer_id
                    );
                    
                    $this->db->insert('booking_staying_customer_list', $link_data);
                }
            }
        }
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }       
    }
    */

    function create_booking($data)
    {
        $this->db->insert("booking", $data);
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
       
        $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
        $result = $query->result_array();
        if(isset($result[0]))
        {
          $new_booking_id = $result[0]['last_id'];
        }
            else
        {
          return null;
        }
        
        // Generate new invoice_hash for the new booking (for guest to check the invoice later)
        $this->load->helper("guid");        
        
        $guid = generate_guid();
        $count = 0;
        while($count < 5 || $this->get_booking_id_from_invoice_hash($guid)){
            $guid = generate_guid();
            $count++;
        }
        
        $this->db->query("UPDATE booking SET invoice_hash = '".$guid."' WHERE booking_id = '".$new_booking_id."'");
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        // echo $this->db->last_query();
        return $new_booking_id;
    }

    function insert_booking_batch ($all_booking_batch) {
        if (!($all_booking_batch && count($all_booking_batch) > 0)) {
            return array();
        }

        $bookings = array();

        // just to be safe, only insert in batch of 50, sometimes it doesn't insert more than 100 at once
        for ($i = 0, $total = count($all_booking_batch); $i < $total; $i = $i + 50)
        {
            $booking_batch = array_slice($all_booking_batch, $i, 50);

            $this->db->insert_batch("booking", $booking_batch);

            if ($this->db->_error_message())
            {
                show_error($this->db->_error_message());
            }
            // insert_id function won't work as it converts id(bigint) to int, results in incorrect value
            // $new_booking_id = $this->db->insert_id();

            $query = $this->db->query('select LAST_INSERT_ID( ) AS last_id');
            $result = $query->result_array();
            if(isset($result[0]))
            {
                $new_booking_id = $result[0]['last_id'];
            }
            else
            {
                return null;
            }
            $number_of_bookings = count($booking_batch);
            $last_id = $new_booking_id + ($number_of_bookings - 1);

            $update_batch = array();

            for($booking_id = $new_booking_id; $booking_id <= $last_id; $booking_id++) {
                $bookings[] = $booking_id;
                // Generate new invoice_hash for the new booking (for guest to check the invoice later)
                $this->load->helper("guid");

                $guid = generate_guid();
                $count = 0;
                while($count < 5 || $this->get_booking_id_from_invoice_hash($guid)){
                    $guid = generate_guid();
                    $count++;
                }

                $update_batch[] = array(
                    'invoice_hash' => $guid,
                    'booking_id' => $booking_id
                );
            }

            $this->db->update_batch('booking', $update_batch, 'booking_id');
        }
        return $bookings;
    }

    // called from invoice
    function get_state($booking_id)
    {
        $this->db->select('state');
        $this->db->where('booking_id', $booking_id);
        $query = $this->db->get('booking');
        $result = $query->result();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result[0]->state;
        }
        return null;
    }
        
    function get_rate($booking_id)
    {
        $this->db->select('rate');
        $this->db->where('booking_id', $booking_id);
        $query = $this->db->get('booking');
        $result = $query->result();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        
        if ($query->num_rows >= 1)
        {
            return $result[0]->rate;
        }
        return null;
    }
    
    // returns attribute of booking with corresponding booking_id
    // attributes can be customer_id, company_id, check_in_date, etc... 
    function get_attribute($attribute, $booking_id)
    {
        $this->db->select($attribute);
        $this->db->where('booking_id', $booking_id);
        $query = $this->db->get('booking');
        $q = $query->result();
        return $q[0]->$attribute;
    }   
    
    //Auto check-out guests that are suppose to check out on $selling_date
    function auto_check_out_guests($company_id, $selling_date) 
    {
        $query = "
            UPDATE booking as b
            LEFT JOIN (
                SELECT b2.booking_id, max(brh.check_out_date) as check_out_date
                FROM booking as b2, booking_block as brh
                WHERE b2.booking_id = brh.booking_id AND b2.state = ".INHOUSE." AND b2.company_id = '$company_id'
                GROUP by booking_id
            )brhMax on brhMax.booking_id = b.booking_id
            SET b.state = '".CHECKOUT."'
            WHERE b.state = '".INHOUSE."' AND DATE(brhMax.check_out_date) <='$selling_date' AND b.company_id= '$company_id'
            ";
                    
        $this->db->query($query);   
        
        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
    }
    
    // Get all bookings that are staying and paying
    // used by Night Audit process and also by In-house guest Report
    function get_todays_bookings($company_id) 
    {        

        $sql = "
            SELECT *
            FROM (`booking` as b, `room` as r, `company` as c, `booking_block` as brh)
            WHERE `b`.`company_id` =  '$company_id'
            AND `c`.`company_id` = b.company_id
            AND (
                    (
                        c.night_audit_ignore_check_out_date = '0' AND 
                        (
                            (DATE(brh.check_in_date) <= c.selling_date and DATE(brh.check_out_date) > c.selling_date) OR 
                            (DATE(brh.check_in_date) = c.selling_date and DATE(brh.check_out_date) = c.selling_date)
                        )
                    ) OR
                    (
                        c.night_audit_ignore_check_out_date = '1'
                    )
                )
                
            AND

                (
                    (
                        c.night_audit_charge_in_house_only = '1' AND b.state = '".INHOUSE."'
                    ) OR 
                    (
                        c.night_audit_charge_in_house_only = '0' AND 
                        (
                            b.state = '".INHOUSE."' OR 
                            (
                                b.state = '".RESERVATION."' AND 
                                (
                                    (DATE(brh.check_in_date) <= c.selling_date and DATE(brh.check_out_date) > c.selling_date) OR 
                                    (DATE(brh.check_in_date) = c.selling_date and DATE(brh.check_out_date) = c.selling_date)
                                )
                            )
                        ) 
                    )
                )
            AND `b`.`is_deleted` =  '0'
            AND `b`.`booking_id` = brh.booking_id
            AND `r`.`room_id` = brh.room_id
            GROUP BY `b`.`booking_id`
            ORDER BY `r`.`room_name`
        ";
        
        $q = $this->db->query($sql);    

        //echo $this->db->last_query();
        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        
        return $q->result_array();      
    }
        
        // get all bookings that are not checked in
        function get_reservations($company_id)
        {
            $sql = "
            SELECT *
            FROM (`booking` as b, `room` as r, `company` as c, `booking_block` as brh)
            WHERE `b`.`company_id` =  '$company_id'
            AND `c`.`company_id` = b.company_id
            AND b.state = '".RESERVATION."' 
                        AND DATE(brh.check_in_date) = c.selling_date AND DATE(brh.check_out_date) > c.selling_date
            AND `b`.`is_deleted` =  '0'
            AND `b`.`booking_id` = brh.booking_id
            AND `r`.`room_id` = brh.room_id
            GROUP BY `b`.`booking_id`
            ORDER BY `r`.`room_name`
        ";
        
        $q = $this->db->query($sql);    

        //echo $this->db->last_query();
        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        
        return $q->result_array();
        }

    // Get all bookings that are staying and paying
    function get_bookings_by_check_out_date($company_id, $date) 
    {
        $this->db->where('booking.company_id', $company_id);
        $this->db->where('state', CHECKOUT);
        $this->db->where('DATE(booking_block.check_out_date)', $date);
        $this->db->where('booking.booking_id = booking_block.booking_id');
        $this->db->from('booking');
        $this->db->from('booking_block');

        $q = $this->db->get();
        
        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        
        return $q->result();        
    }
    
    // return $booking_id's balance based on it's charges and its taxes
    function get_balance($booking_id) 
    {   
        $sql = "
            SELECT 
                (IFNULL(c.charge_total,0) - IFNULL(SUM(p.amount), 0)) as balance 
            FROM 
            ( 
                SELECT  
                    ch.booking_id,  
                    ROUND(SUM((ch.amount * (1+IFNULL(percentage_total * 0.01, 0))) + IFNULL(flat_tax_total, 0)), 2) as charge_total 
                FROM 
                    charge as ch
                LEFT JOIN (  
                    SELECT ct.name, ct.id, sum(IF(tt.is_percentage = 1, tax_rate, 0)) as percentage_total, sum(IF(tt.is_percentage = 0, tax_rate, 0)) as flat_tax_total
                    FROM charge_type_tax_list AS cttl, charge_type as ct, tax_type AS tt 
                    WHERE  
                        ct.id = cttl.charge_type_id AND 
                        tt.tax_type_id = cttl.tax_type_id AND  
                        tt.is_deleted = '0' 
                    GROUP BY ct.id 
                )t ON ch.charge_type_id = t.id 
                WHERE 
                    ch.is_deleted = '0' and
                    ch.booking_id = '$booking_id'
                 GROUP BY ch.booking_id 
            )c
            LEFT JOIN payment as p ON p.booking_id = '$booking_id'  AND p.is_deleted = '0' 

            ";
        $query = $this->db->query($sql);    

        //echo $this->db->last_query();

        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        
        $q = $query->row_array(0);

        if (isset($q['balance']))
        {
            return $q['balance'];
        }

        return null;
        
    }
    
    // Confirm Unconfirmed Reservations by changing its state to RESERVATION(0) from UNCONFIRMED_RESERVATION(7)
    function confirm_reservation($booking_id)
    {
        $data = array( 'state' => RESERVATION );
        $this->db->where('booking_id', $booking_id);
        $this->db->update('booking', $data);
    }
    
    // @param hash = 32 char hash that's stored in booking table
    // @return booking_id
    function get_booking_id_from_invoice_hash($hash)
    {
        $this->db->select('booking_id');
        $this->db->where('invoice_hash', $hash);
        $query = $this->db->get('booking');
        $q = $query->result();  
        if (isset($q[0]))
        {
            return $q[0]->booking_id;
        }
        return null;
    }
    
    function get_group_id_from_invoice_hash($hash)
    {
        $this->db->select('bxblg.booking_group_id as group_id');
        $this->db->from('booking as b');
        $this->db->join('booking_x_booking_linked_group as bxblg', 'b.booking_id = bxblg.booking_id');
        $this->db->where('b.invoice_hash', $hash);
        $query = $this->db->get();
        $q = $query->result();  
        if (isset($q[0]))
        {
            return $q[0]->group_id;
        }
        return null;
    }

    // @param hash = 32 char hash that's stored in booking table
    // @return booking_id
    function get_invoice_hash_from_booking_id($booking_id)
    {
        $this->db->select('invoice_hash');
        $this->db->where('booking_id', $booking_id);
        $query = $this->db->get('booking');
        $q = $query->result();  
        if (isset($q[0]))
        {
            return $q[0]->invoice_hash;
        }
        return null;
    }
    
    function get_unassigned_booking_count($company_id) 
    {
        $sql= "SELECT COUNT(booking_id) AS total
        FROM booking as b
        WHERE (b.booking_customer_id = '' OR b.booking_customer_id IS NULL) AND b.company_id = '$company_id'";      
        $q = $this->db->query($sql);
        
        if ($this->db->_error_message()) 
        {
            show_error($this->db->_error_message());
        }
        
        $result = $q->row_array(0);
        
        return $result['total'];
    }
    
	function _get_where_conditions($filters, $company_id, $include_room_no_assigned = false, $booking_fields_where_condition = "", $customer_fields_where_condition = "")
    {
        //Generate booking type portion of the SQL statement
        if (isset($filters['search_query']) && !isset($filters['state']) && !isset($filters['start_date']) && !isset($filters['end_date']))
        {
            $search_query = $filters['search_query'];
            $where_conditions = "
                c.customer_name like '%$search_query%' OR 
                sg.customer_name like '%$search_query%' OR 
                b.booking_notes like '%$search_query%' OR 
                b.booking_id like '%$search_query%' OR 
                c.phone like '%$search_query%' OR
                c.email like '%$search_query%' 
                $booking_fields_where_condition
                $customer_fields_where_condition
                ";
            return $where_conditions;
        }
        
        $state_sql = "";
        if (isset($filters['state'])) 
        {
            // SKIP THE CONDITION if filter applied for cancellation state
            if ($filters['state'] == 'active')  // Show all reservation, inhouse, and checkout guests. Called from calendar
            {
                if (!(isset($filters['reservation_type'])
                    && ($filters['reservation_type'] == CANCELLED || $filters['reservation_type'] == -1))) {
                    $state_sql = "AND (b.state < 4 OR b.state = '" . UNCONFIRMED_RESERVATION . "' OR b.state = '" . NO_SHOW . "')";
                }
            } 
            elseif ($filters['state'] == 'all' || $filters['state'] == '') 
            {
                $state_sql = "";
            } 
            else 
            {
                $state_sql = "AND b.state = '".$filters['state']."'";
            }
        }
        
        
                
        //Generate time portion of the SQL statement
        $time_sql = "";
        if(isset($filters['show_only_checked_in_and_checked_out']) && $filters['show_only_checked_in_and_checked_out']){
            if($filters['state'] == INHOUSE)
            {
                $time_sql = "AND (DATE(check_in_date) >= '".$filters['start_date']."' AND DATE(check_in_date) <= '".$filters['end_date']."')";
            }
            elseif ($filters['state'] == CHECKOUT)
            {
                $time_sql = "AND (DATE(check_out_date) >= '".$filters['start_date']."' AND DATE(check_out_date) <= '".$filters['end_date']."')";
            }
        }
        else
        {
            if(isset($filters['filter_booking_list']))
            {
                $start_date = isset($filters['filter_booking_list']['start_date']) ? $filters['filter_booking_list']['start_date'] : "";
                $end_date = isset($filters['filter_booking_list']['end_date']) ? $filters['filter_booking_list']['end_date'] : "";
                $date_overlap = isset($filters['filter_booking_list']['date_overlap']) ? $filters['filter_booking_list']['date_overlap'] : "";
                
                $time_sql = " AND ((DATE(check_in_date) = '".$start_date."' AND ".RESERVATION." = b.state) OR "
                                . " (DATE(check_out_date) = '".$end_date."' AND ".CHECKOUT." = b.state) OR "
                                . " (".UNCONFIRMED_RESERVATION." = b.state) OR "
                                . " (".INHOUSE." = b.state) OR "
                                . " (DATE(check_out_date) > '".$date_overlap."' AND DATE(check_in_date) <= '".$date_overlap."' AND ".OUT_OF_ORDER." = b.state))";
            }
            else
            {
                // if both start_date and end_date are set, then return occupancies within range
                if (isset($filters['start_date']) && isset($filters['end_date']))
                { 
                    if ($filters['start_date'] != "" && $filters['end_date'] != "" && isset($filters['unassigned_bookings']) && $filters['unassigned_bookings'])
                    {
                        $time_sql = "AND (DATE(check_out_date) > '".$filters['start_date']."' AND '".$filters['end_date']."' > DATE(check_in_date))";
                    }
                    else if ($filters['start_date'] != "" && $filters['end_date'] != "") 
                    {
                        if(isset($filters['booking_status']) && $filters['booking_status'] == 'checking_in')
                        {
                            $time_sql = "AND (DATE(check_in_date) >= '".$filters['start_date']."' AND '".$filters['end_date']."' >= DATE(check_in_date))";
                        }
                        else if(isset($filters['booking_status']) && $filters['booking_status'] == 'checking_out')
                        {
                            $time_sql = "AND (DATE(check_out_date) >= '".$filters['start_date']."' AND '".$filters['end_date']."' >= DATE(check_out_date))";
                        }
                        else
                        {
                            $time_sql = "AND (DATE(check_out_date) >= '".$filters['start_date']."' AND '".$filters['end_date']."' >= DATE(check_in_date))";
                        }
                    }
                // if only start_date is set, then return occupancies that starts on start_date
                }
                elseif (isset($filters['start_date']) && !isset($filters['end_date'])) 
                { 
                    $time_sql = "AND DATE(check_in_date) = '".$filters['start_date']."'";
                // if only end_date is set, then return occupancies that ends on end_date
                } 
                elseif (!isset($filters['start_date']) && isset($filters['end_date'])) 
                { 
                    $time_sql = "AND DATE(check_out_date) = '".$filters['end_date']."'";
                }
            }
        }
        
        $date_overlap_sql = "";
        if (isset($filters['date_overlap']))
        { 
            $date_overlap_sql = "AND (DATE(check_in_date) <= '".$filters['date_overlap']."' AND DATE(check_out_date) > '".$filters['date_overlap']."')";
        }

        // matching customer_id
        $booking_customer_sql = "";
        if (isset($filters['booking_customer_id']))
        {
            $booking_customer_sql = "AND b.booking_customer_id = '".$filters['booking_customer_id']."'";
        }
        
        // matching customer_id
        $staying_customer_sql = $staying_customer_name = "";
        if (isset($filters['staying_customer_id']))
        {
            $staying_customer_sql = "AND 
                                        b.booking_id = bscl.booking_id AND
                                        bscl.customer_id = '".$filters['staying_customer_id']."'";
        }
        if(isset($filters['staying_guest_name']) && $filters['staying_guest_name'])
        {
            $staying_customer_name = "AND
                                       sg.customer_name like '%".$filters['staying_guest_name']."%' ";
        }
        // set search query
        $search_sql = "";
        if (isset($filters['search_query'])) 
        {
            $search_query = $filters['search_query'];
            $search_sql = "AND (
                c.customer_name like '%$search_query%' OR 
                sg.customer_name like '%$search_query%' OR 
                b.booking_notes like '%$search_query%' OR 
                b.booking_id like '%$search_query%' OR 
                c.phone like '%$search_query%' OR
                c.email like '%$search_query%' 
                $booking_fields_where_condition
                $customer_fields_where_condition
                )";     
        }
        
        $paid_sql = "";
        $where_conditions = "
            b.company_id = '$company_id' AND
            b.is_deleted != '1' AND 
            ".($include_room_no_assigned ? "IF(brh.room_id, r.is_deleted, '0')" : "r.is_deleted")." != '1'
            $time_sql
            $state_sql 
            $booking_customer_sql
            $staying_customer_sql
            $staying_customer_name
            $search_sql
            $paid_sql
            $date_overlap_sql
        ";
        
        if (isset($filters['location_id']) && $filters['location_id']) 
        {
            $where_conditions .= ' AND r.location_id="'.$filters['location_id'].'"';
        }
        if (isset($filters['floor_id']) && $filters['floor_id']) 
        {
            $where_conditions .= ' AND r.floor_id="'.$filters['floor_id'].'"';
        }  
        if (isset($filters['room_type_id']) && $filters['room_type_id']) 
        {
            $where_conditions .= ' AND r.room_type_id="'.$filters['room_type_id'].'"';
        }
        if (isset($filters['group_id']) && $filters['group_id']) 
        {
            $where_conditions .= ' AND r.group_id="'.$filters['group_id'].'"';
//            $where_conditions .= ' AND bxblg.booking_group_id="'.$filters['group_id'].'"';
        }    
        if (isset($filters['reservation_type']) && $filters['reservation_type'] != '' && $filters['reservation_type'] != -1)
        {
            $where_conditions .= ' AND b.state="'.$filters['reservation_type'].'"';
        }         
        if (isset($filters['booking_source']) && $filters['booking_source'] != '') 
        {
            $where_conditions .= ' AND b.source="'.$filters['booking_source'].'"';
        } 
        
        if(isset($filters['group']) && $filters['group']){
            if($filters['group'] == 'all'){
                $where_conditions .= " ";
            }elseif($filters['group'] == 'unassigned'){
                $where_conditions .= " AND (bxs.group_id IS NULL OR bxs.group_id = 0) ";
            }
            else {
                $where_conditions .= " AND bxs.group_id = '".$filters['group']."' ";
            }
            
        }
        if (isset($filters['statement_date']) && $filters['statement_date'] != '') 
        {
            $where_conditions .= ' AND DATE(check_in_date) <= "'.$filters['statement_date'].'"';
        }
        if (isset($filters['group_ids']) && $filters['group_ids'])
        {
            $group_ids = trim(trim($filters['group_ids'], ","), " ");
            $where_conditions .= " AND blg.id IN ($group_ids) ";
        }
        return $where_conditions;
    }
    
        function _get_group_where_conditions($filters, $company_id)
    {
        //Generate booking type portion of the SQL statement
        if (isset($filters['search_query']) && !isset($filters['state']) && !isset($filters['start_date']) && !isset($filters['end_date']))
        {
            $search_query = $filters['search_query'];
            $where_conditions = "
                c.customer_name like '%$search_query%' OR 
                sg.customer_name like '%$search_query%' OR 
                b.booking_notes like '%$search_query%' OR 
                b.booking_id like '%$search_query%'
                ";
            return $where_conditions;
        }
        
        $state_sql = "";
        if (isset($filters['state'])) 
        {
            if ($filters['state'] == 'active')  // Show all reservation, inhouse, and checkout guests. Called from calendar     
            {
                $state_sql = "AND (b.state < 4 OR b.state = '".UNCONFIRMED_RESERVATION."' OR b.state = '".NO_SHOW."')";
            } 
            elseif ($filters['state'] == 'all' || $filters['state'] == '') 
            {
                $state_sql = "";
            } 
            else 
            {
                $state_sql = "AND b.state = '".$filters['state']."'";
            }
        }
        
        
        
        //Generate time portion of the SQL statement
        $time_sql = "";
        if(isset($filters['show_only_checked_in_and_checked_out']) && $filters['show_only_checked_in_and_checked_out']){
            if($filters['state'] == INHOUSE)
            {
                $time_sql = "AND (DATE(check_in_date) >= '".$filters['start_date']."' AND DATE(check_in_date) <= '".$filters['end_date']."')";
            }
            elseif ($filters['state'] == CHECKOUT)
            {
                $time_sql = "AND (DATE(check_out_date) >= '".$filters['start_date']."' AND DATE(check_out_date) <= '".$filters['end_date']."')";
            }
        }
        else
        {
            // if both start_date and end_date are set, then return occupancies within range    
            if (isset($filters['start_date']) && isset($filters['end_date']))
            { 
                if ($filters['start_date'] != "" && $filters['end_date'] != "") 
                {
                    $time_sql = "AND (DATE(check_out_date) >= '".$filters['start_date']."' AND '".$filters['end_date']."' >= DATE(check_in_date))";
                }
            // if only start_date is set, then return occupancies that starts on start_date
            }
            elseif (isset($filters['start_date']) && !isset($filters['end_date'])) 
            { 
                $time_sql = "AND DATE(check_in_date) = '".$filters['start_date']."'";
            // if only end_date is set, then return occupancies that ends on end_date
            } 
            elseif (!isset($filters['start_date']) && isset($filters['end_date'])) 
            { 
                $time_sql = "AND DATE(check_out_date) = '".$filters['end_date']."'";
            }
        }
        
        $date_overlap_sql = "";
        if (isset($filters['date_overlap']))
        { 
            $date_overlap_sql = "AND (DATE(check_in_date) <= '".$filters['date_overlap']."' AND DATE(check_out_date) > '".$filters['date_overlap']."')";
        }

        // matching customer_id
        $booking_customer_sql = "";
        if (isset($filters['booking_customer_id']))
        {
            $booking_customer_sql = "AND b.booking_customer_id = '".$filters['booking_customer_id']."'";
        }
        
        // matching customer_id
        $staying_customer_sql = $staying_customer_name = "";
        if (isset($filters['staying_customer_id']))
        {
            $staying_customer_sql = "AND 
                                        b.booking_id = bscl.booking_id AND
                                        bscl.customer_id = '".$filters['staying_customer_id']."'";
        }
        if(isset($filters['staying_guest_name']) && $filters['staying_guest_name'])
        {
            $staying_customer_name = "AND
                                       sg.customer_name like '%".$filters['staying_guest_name']."%' ";
        }
        // set search query
        $search_sql = "";
        if (isset($filters['search_query'])) 
        {
            $search_query = $filters['search_query'];
            $search_sql = "AND (
                c.customer_name like '%$search_query%' OR 
                sg.customer_name like '%$search_query%' OR 
                b.booking_notes like '%$search_query%' OR 
                b.booking_id like '%$search_query%'
                )";     
        }
        
        $paid_sql = "";
        
        $where_conditions = "
            b.company_id = '$company_id' AND
            b.is_deleted != '1'
            $time_sql
            $state_sql 
            $booking_customer_sql
            $staying_customer_sql
            $staying_customer_name
            $search_sql
            $paid_sql
            $date_overlap_sql
        ";
        
        if (isset($filters['location_id']) && $filters['location_id']) 
        {
            $where_conditions .= ' AND r.location_id="'.$filters['location_id'].'"';
        }
        if (isset($filters['floor_id']) && $filters['floor_id']) 
        {
            $where_conditions .= ' AND r.floor_id="'.$filters['floor_id'].'"';
        }  
        if (isset($filters['room_type_id']) && $filters['room_type_id']) 
        {
            $where_conditions .= ' AND r.room_type_id="'.$filters['room_type_id'].'"';
        }
        if (isset($filters['group_id']) && $filters['group_id']) 
        {
//            $where_conditions .= ' AND r.group_id="'.$filters['group_id'].'"';
            $where_conditions .= ' AND (bxblg.booking_group_id="'.$filters['group_id'].'" OR blg.name LIKE "%'.trim($filters['group_id']).'%")';
        }    
        if (isset($filters['reservation_type']) && $filters['reservation_type'] != '') 
        {
            $where_conditions .= ' AND b.state="'.$filters['reservation_type'].'"';
        } 
        if(isset($filters['group']) && $filters['group']){
            if($filters['group'] == 'all'){
                $where_conditions .= " ";
            }elseif($filters['group'] == 'unassigned'){
                $where_conditions .= " AND (bxs.group_id IS NULL OR bxs.group_id = 0) ";
            }
            else {
                $where_conditions .= " AND bxs.group_id = '".$filters['group']."' ";
            }
            
        }
        if (isset($filters['statement_date']) && $filters['statement_date'] != '') 
        {
            $where_conditions .= ' AND DATE(check_in_date) <= "'.$filters['statement_date'].'"';
        }
        if (isset($filters['group_ids']) && $filters['group_ids'])
        {
            $group_ids = trim(trim($filters['group_ids'], ","), " ");
            $where_conditions .= " AND blg.id IN ($group_ids) ";
        }
        return $where_conditions;
    }
    
    // return csv values between date - 1 month and date + 1 month
    function get_csv($company_id, $booking_types, $date_start = null, $date_end = null)
    {
        $this->db->select('b.booking_id');
        $this->db->select("IF(b.state = '".RESERVATION."', 'reservation', IF(b.state = '".INHOUSE."', 'in_house', IF(b.state = '".CHECKOUT."', 'checked_out', IF(b.state = '".UNCONFIRMED_RESERVATION."', 'unconfirmed', IF(b.state = '".CANCELLED."', 'cancelled', IF(b.state = '".OUT_OF_ORDER."', 'out of order', '')))))) as state", false); // $this->db->select() accepts an optional second parameter. If you set it to FALSE, CodeIgniter will not try to protect your field or table names with backticks. This is useful if you need a compound select statement.
        $this->db->select('brh.check_in_date');
        $this->db->select('brh.check_out_date');
        $this->db->select('r.room_name as room_name');
        $this->db->select('c.customer_name');
        $this->db->select('c.phone');
        $this->db->from('booking as b, customer as c, booking_block as brh, room as r');
        $this->db->where("b.company_id", $company_id);
        $this->db->where_in("b.state", $booking_types);
        $this->db->where("b.booking_customer_id = c.customer_id");
        $this->db->where("b.is_deleted = '0'");
        $this->db->where('brh.booking_id = b.booking_id');
        $this->db->where('brh.room_id = r.room_id');
        if (isset($date_end))
        {
            $this->db->where('DATE(brh.check_in_date) <=', $date_end);
        }

        if (isset($date_start))
        {
            $this->db->where('DATE(brh.check_out_date) >=', $date_start);
        }
        
        $query = $this->db->get();
        return $query;
        
    }
    
    // when user toggles whether to use rate plan or not
    function set_use_rate_plan($booking_id, $use_rate_plan)
    {
        $data = array (
            'use_rate_plan' => $use_rate_plan
        );
        $this->db->where('booking_id', $booking_id);
        $this->db->update("booking", $data);  
        if ($this->db->_error_message()) // error checking
                    show_error($this->db->_error_message());
    }
    
    function get_company_id($booking_id)
    {
        $this->db->select('company_id');
        $this->db->where('booking_id', $booking_id);
        $query = $this->db->get('booking');
        $q = $query->result();  
        if (isset($q[0]))
        {
            return $q[0]->company_id;
        }
        return null;
    }
    
    function get_daily_number_rooms($from_date, $to_date){
        $company_id = $this->session->userdata('current_company_id');
        
        $sql = "
                SELECT  
                (
                    SELECT count(distinct brh.booking_id)
                    FROM booking_block as brh, booking as b
                    WHERE 
                    b.booking_id = brh.booking_id AND
                    b.company_id = '$company_id' AND (b.state = '".INHOUSE."' OR b.state = '".CHECKOUT."') AND
                    (
                        DATE(brh.check_out_date) > charge_table.selling_date AND charge_table.selling_date >= DATE(brh.check_in_date)
                    )
                ) as booking_count 
                FROM (
                    SELECT c.selling_date
                    FROM charge as c
                    LEFT JOIN booking as cb ON cb.booking_id = c.booking_id
                    LEFT JOIN charge_type ct ON c.charge_type_id=ct.id 
                    WHERE 
                        c.is_deleted = '0' AND
                        c.selling_date >= '".$from_date."' AND 
                        c.selling_date < '".$to_date."' AND 
                        ct.is_deleted = '0' AND 
                        cb.state != '".DELETED."'
                    ) charge_table              
                ";
        $result = $this->db->query($sql);
        $result = $result->result();
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
        if(count($result) > 0){
            return $result[0]->booking_count;
        }else{
            return 0;
        }
    }
    
    function get_ledgers_detail($date){
        $company_id = $this->session->userdata('current_company_id');
        
        $sql = "SELECT
                  r.room_name,
                  rt.name,
                  c.customer_name,
                  (b.rate-IFNULL(pa.pay_amount,0)) ledger_amount,
                  brh.check_in_date,
                  brh.check_out_date
                FROM booking_block brh
                  INNER JOIN room r
                    ON brh.room_id = r.room_id
                      AND r.company_id = '".$company_id."'
                  LEFT JOIN booking b
                    ON brh.booking_id = b.booking_id
                  LEFT JOIN room_type rt
                    ON r.room_type_id = rt.id
                  LEFT JOIN customer c
                    ON b.booking_customer_id = c.customer_id
                  LEFT JOIN (SELECT
                               p.amount            AS pay_amount,
                               p.booking_id
                             FROM payment p
                               LEFT JOIN payment_type pt
                                 ON p.payment_type_id = pt.payment_type_id
                             WHERE pt.company_id = '".$company_id."'
                                 AND pt.is_deleted = '0'
                                 AND p.selling_date='".$date."') pa
                    ON pa.booking_id = b.booking_id
                WHERE rt.is_deleted = '0'
                    AND r.is_deleted = '0'
                    AND DATE(brh.check_in_date) <= '".$date."'
                    AND DATE(brh.check_out_date) > '".$date."'";
        
        $q = $this->db->query($sql);
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result = $q->result();     
        return $result;
    }

    function get_occupancys_detail($date){
        $company_id = $this->session->userdata('current_company_id');
        
        $sql = "SELECT
                  r.room_name,
                  brh.check_in_date,
                  brh.check_out_date
                FROM room r
                  LEFT JOIN booking_block brh
                    ON r.room_id = brh.room_id
                  LEFT JOIN room_type rt
                    ON r.room_type_id = rt.id
                WHERE r.is_deleted = '0'
                    AND rt.is_deleted = '0'
                    AND r.company_id = '".$company_id."'
                    AND DATE(brh.check_in_date) <= '".$date."'
                    AND DATE(brh.check_out_date) > '".$date."'";
        
        $q = $this->db->query($sql);
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
        $result = $q->result();     
        return $result;
    }

    function get_booking_count($start_date, $end_date, $type = 'date_wise', $customer_type_id = null)
    {
        $select = 'di.date, count(distinct brh.booking_id)  as booking_count';
        $group = "di.date";
        $join = $where = $get_nights = $where_state = $where_customer_type = $join_customer_type = '';
        $key = 'date';
        if($type == 'room_wise'){
            $key = 'room_id';
            $group = "brh.room_id";
            $select = "brh.room_id, count(distinct(IF(b.state != '".CANCELLED."', b.booking_id, NULL))) as booking_count,
                    SUM(IF(b.state != '".CANCELLED."', 1, 0)) as total_nights,
                    (count(distinct(IF(b.state = '".CANCELLED."', b.booking_id, NULL))) / count(distinct(b.booking_id)) * 100) as cancelled_booking_per";
            $where_state = " OR b.state = '".CANCELLED."'";
            $get_nights = true;
        }elseif($type == 'roomtype_wise'){
            $key = 'room_type_id';
            $group = 'r.room_type_id';
            $select = "r.room_type_id, count(distinct(IF(b.state != '".CANCELLED."', b.booking_id, NULL))) as booking_count,
                    SUM(IF(b.state != '".CANCELLED."', 1, 0)) as total_nights,
                    (count(distinct(IF(b.state = '".CANCELLED."', b.booking_id, NULL))) / count(distinct(b.booking_id)) * 100) as cancelled_booking_per";
            $join = 'room as r,';
            $where = 'brh.room_id = r.room_id AND';
            $where_state = " OR b.state = '".CANCELLED."'";
            $get_nights = true;
        }
        if($customer_type_id)
        {
            $join_customer_type = ', customer as c';
            $where_customer_type = "b.booking_customer_id = c.customer_id AND c.customer_type_id = '$customer_type_id' AND";
        }
        $company_id = $this->session->userdata('current_company_id');
        $data = null;        
        $sql = "
                SELECT 
                    $select
                FROM 
                    date_interval as di, 
                    booking_block as brh, 
                    $join
                    booking as b
                    $join_customer_type
                WHERE 
                    b.booking_id = brh.booking_id AND
                    b.company_id = '$company_id' AND 
                    $where
                    $where_customer_type
                    b.is_deleted != '1' AND
                    (
                        (
                            b.state = '".RESERVATION."' AND
                            '".$this->selling_date."' <= DATE(brh.check_in_date)
                        ) OR
                        b.state = '".INHOUSE."' OR b.state = '".CHECKOUT."' $where_state
                    ) AND brh.room_id != 0 AND
                    di.date >= '$start_date' AND 
                    di.date <= '$end_date' AND
                    (
                        (
                            DATE(brh.check_out_date) > di.date AND 
                            di.date >= DATE(brh.check_in_date)
                        )
                        OR
                        (
                            DATE(brh.check_out_date) = DATE(brh.check_in_date) AND 
                            DATE(brh.check_in_date) = di.date
                        )
                    )
                GROUP BY $group;
                ";
        $query = $this->db->query($sql);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        $result = $get_nights ? array('booking_count' => array(), 'cancelled_booking_per' => array(), 'total_nights' => array()) : array();
        if ($query->num_rows >= 1) 
        {   
            if($get_nights){
                foreach ($query->result_array() as $row)
                {
                    $result['booking_count'][$row[$key]] = $row['booking_count'];
                    $result['cancelled_booking_per'][$row[$key]] = $row['cancelled_booking_per'];
                    $result['total_nights'][$row[$key]] = $row['total_nights'];
                }
            }else{
                foreach ($query->result_array() as $row)
                {
                    $result[$row[$key]] = $row['booking_count'];
                }
            }
        }   
        return $result; 

    }

    function get_charges_booking_count($start_date, $end_date, $type = 'date_wise', $customer_type_id = null)
    {
        $select = 'di.date, count(distinct brh.booking_id)  as booking_count';
        $group = "di.date";
        $join = $where = $get_nights = $where_state = $where_customer_type = $join_customer_type = '';
        $key = 'date';

        if($customer_type_id)
        {
            $join_customer_type = ', customer as c';
            $where_customer_type = "b.booking_customer_id = c.customer_id AND c.customer_type_id = '$customer_type_id' AND";
        }
        $company_id = $this->session->userdata('current_company_id');
        $data = null; 

            
        $sql = "
            SELECT
                ch.selling_date as date,
                count(DISTINCT ch.booking_id) as booking_count
            FROM
                charge AS ch,
                booking AS b
                $join_customer_type
            WHERE
                $where_customer_type ch.is_deleted = '0' AND b.company_id = '$company_id' AND b.booking_id = ch.booking_id AND b.is_deleted = '0' AND b.state < '3' AND ch.selling_date BETWEEN '$start_date' AND '$end_date'
            GROUP BY
                ch.selling_date";
        
        $query = $this->db->query($sql);

        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());

        $result = $get_nights ? array('booking_count' => array(), 'cancelled_booking_per' => array(), 'total_nights' => array()) : array();
        if ($query->num_rows >= 1) 
        {   
            if($get_nights){
                foreach ($query->result_array() as $row)
                {
                    $result['booking_count'][$row[$key]] = $row['booking_count'];
                    $result['cancelled_booking_per'][$row[$key]] = $row['cancelled_booking_per'];
                    $result['total_nights'][$row[$key]] = $row['total_nights'];
                }
            }else{
                foreach ($query->result_array() as $row)
                {
                    $result[$row[$key]] = $row['booking_count'];
                }
            }
        }   
        return $result; 

    }

    function set_guest_review($booking_id, $star_rating)
    {

        $data = array (
            'guest_review' => $star_rating
        );
        $this->db->where('booking_id', $booking_id);
        $this->db->update("booking", $data);  
        if ($this->db->_error_message()) // error checking
                    show_error($this->db->_error_message());

    }

    function set_booking_review($data)
    {
        $this->db->insert("booking_review", $data);  
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
    }

    function update_booking_review($data)
    {
        $this->db->where('booking_id', $data['booking_id']);
        $this->db->update("booking_review", $data);  
        if ($this->db->_error_message()) // error checking
            show_error($this->db->_error_message());
    }


    /*Return bookings for reservation report*/
    function get_bookings_for_report($filters, $company_id = 0, $group_by1 = NULL, $include_customer_all_details = false)
    {
        if ($company_id == 0)
        {
            $company_id = $this->session->userdata('current_company_id');
        }
        
        $sql = $this->_build_get_bookings_sql_query_based_on_filters_report($filters, $company_id, $group_by1, $include_customer_all_details);
        
        $q = $this->db->query($sql);
        //echo $this->db->last_query();
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

        $result = $q->result_array();
        
        return $result;
    }

    /*
     * Generate and return query for booking based on filters
     * */
    function _build_get_bookings_sql_query_based_on_filters_report($filters, $company_id, $group_by1, $include_customer_all_details = false)
    {

        $where_conditions = $this->_get_inhouse_where_conditions($filters, $company_id);
        
        // set order by
        $order_by = "";
        if (isset($filters['order_by'])) {
            // if both $star and $end are set, then return occupancies within range
            if ($filters['order_by'] != "") {
                $order_by = "ORDER BY ".$filters['order_by'];
            }
            else
            {
                $order_by = "ORDER BY bl.date_time";
            }
        }

        // set order
        $order = "";
        if (isset($filters['order'])) {
            if ($filters['order'] != "") {
                if ($filters['order'] == 'DESC') {
                    $order = "DESC";
                }
                if ($filters['order'] == 'ASC') {
                    $order = "ASC";
                }
            }
            else
            {
                $order = "DESC";
            }
        }
        
        $booking_log_join = '';
        if(isset($filters['state']))
        {
            if($filters['state'] == '1') // checkin
            {
                $booking_log_join = "LEFT JOIN booking_log bl_checkin ON bl_checkin.booking_id = b.booking_id AND bl_checkin.log_type = '5' AND bl_checkin.log = '1'";
            }
            else if($filters['state'] == '2') // checkout
            {
                $booking_log_join = "LEFT JOIN booking_log bl_checkout ON bl_checkout.booking_id = b.booking_id AND bl_checkout.log_type = '5' AND bl_checkout.log = '2'";
            }
            else if($filters['state'] == '3') // out of order
            {
                $booking_log_join = "LEFT JOIN booking_log bl_out_of_order ON bl_out_of_order.booking_id = b.booking_id AND bl_out_of_order.log_type = '5' AND bl_out_of_order.log = '3'";
            }
            else if($filters['state'] == '4') // cancelled
            {
                $booking_log_join = "LEFT JOIN booking_log bl_cancelled ON bl_cancelled.booking_id = b.booking_id AND 
                (
                    (
                        bl_cancelled.log_type = '5' OR bl_cancelled.log = '4'
                    ) OR 
                    (
                        bl_cancelled.log_type = '0' AND bl_cancelled.log = 'OTA Booking cancelled'
                    )
                ) ";
            }
            else if($filters['state'] == '5') // no show
            {
                $booking_log_join = "LEFT JOIN booking_log bl_noshow ON bl_noshow.booking_id = b.booking_id AND bl_noshow.log_type = '5' AND bl_noshow.log = '5'";
            }
            else if($filters['state'] == '7') // unconfirmed
            {
                $booking_log_join = "LEFT JOIN booking_log bl_unconfirmed ON bl_unconfirmed.booking_id = b.booking_id AND bl_unconfirmed.log_type = '5' AND bl_unconfirmed.log = '7'";
            }
            else if($filters['state'] == '6') // Deleted
            {
                $booking_log_join = "LEFT JOIN booking_log bl_deleted ON bl_deleted.booking_id = b.booking_id AND bl_deleted.log_type = '1' AND bl_deleted.log = 'Booking deleted'";
            }
            else if($filters['state'] == '10') // Depatures
            {
                $booking_log_join = "LEFT JOIN booking_log bl_departure ON bl_departure.booking_id = b.booking_id AND bl_departure.log_type = '5' AND (bl_departure.log = '2')";
            }
            else if($filters['state'] == '11') // Arrivals
            {
                $booking_log_join = "LEFT JOIN booking_log bl_arrivals ON bl_arrivals.booking_id = b.booking_id AND ((bl_arrivals.log_type = '5' AND bl_arrivals.log = '1'))";
            }
        }

        $select_customer_type = $join_customer_type = "";
        if (isset($filters['include_customer_type']) && $filters['include_customer_type']) 
        {
            
            $select_customer_type = "ct.name as customer_type_name, 
                                    b.adult_count,
                                    b.children_count,
                                    cxcf.value,
                                    GROUP_CONCAT(DISTINCT cxcf2.value, ' ') as custom_field_value,
                                    GROUP_CONCAT(DISTINCT c.customer_id, ' ') as staying_customer_id,";
            
            $join_customer_type = "LEFT JOIN customer_type as ct ON ct.id = c.customer_type_id
                    LEFT JOIN customer_x_customer_field as cxcf ON cxcf.customer_id = b.booking_customer_id AND b.booking_customer_id != '0'
                    LEFT JOIN customer_field as cf ON cxcf.customer_field_id = cf.id
                    LEFT JOIN customer_x_customer_field as cxcf2 ON cxcf2.customer_field_id = cf.id AND c.customer_id = c.customer_id AND cxcf2.customer_id = c.customer_id
                    ";
        }

        $select_booking_fields = $join_booking_fields = "";
        if (isset($filters['include_booking_fields']) && $filters['include_booking_fields']) 
        {
            
            $select_booking_fields = "bxbf.value,
                                    GROUP_CONCAT(IF(bxbf2.value != '', bxbf2.value, ''), ' ') as booking_custom_field_value,
                                    GROUP_CONCAT(DISTINCT bxbf2.booking_field_id, ' ') as booking_field_id,";
            
            $join_booking_fields = "LEFT JOIN booking_x_booking_field as bxbf ON bxbf.booking_id = b.booking_id AND b.booking_id != '0'
                    LEFT JOIN booking_field as bf ON bxbf.booking_field_id = bf.id
                    LEFT JOIN booking_x_booking_field as bxbf2 ON bxbf2.booking_field_id = bf.id AND bxbf2.booking_id = b.booking_id";
        }

        $select_customer_details = "";
        if($include_customer_all_details)
        {
            $select_customer_details = "c.phone2,
                                        c.fax,
                                        c.address,
                                        c.address2,
                                        c.city,
                                        c.region,
                                        c.country,
                                        c.postal_code,
                                        c.customer_notes,";
        }
        
        $sql = "SELECT 
                    b.*, 
                    bs.commission_rate,
                    c.customer_name,
                    c.phone,
                    r.*,
                    brh.check_in_date, 
                    brh.check_out_date, 
                    rp.rate_plan_name, 
                    cm.name as company_name, 
                    up.first_name, up.last_name, 
                    bl.date_time as created_date,
                    blg.name AS group_name,
                    count(DISTINCT sg.customer_id) as guest_count,
                    GROUP_CONCAT(DISTINCT sg.customer_name, ' ') as staying_customers,
                    (   SELECT c2.customer_name
                            FROM customer as c2, booking_staying_customer_list as bscl2
                            WHERE 
                                    c2.customer_id = bscl2.customer_id AND 
                                    bscl2.booking_id = b.booking_id
                            LIMIT 0,1
                    ) as guest_name,
                    $select_booking_fields
                    $select_customer_type
                    $select_customer_details
                    IF(b.source > 20, (SELECT bs.name FROM booking_source as bs WHERE bs.id = b.source LIMIT 1), b.source) as booking_source,
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
                    ), 0) as payment_total
                FROM 
                    `booking` b             
                LEFT JOIN customer c 
                    ON c.customer_id = b.booking_customer_id
                LEFT JOIN booking_log bl 
                    ON bl.booking_id = b.booking_id AND (bl.log = 'Booking created' OR bl.log = 'OTA Booking created' OR bl.log = 'OTA Booking modified' OR bl.log = 'Online reservation submitted')
                $booking_log_join
                LEFT JOIN user_profiles up 
                    ON up.user_id = bl.user_id
                LEFT JOIN booking_block as brh 
                    ON brh.booking_id = b.booking_id
                LEFT JOIN room as r
                    ON brh.room_id = r.room_id
                LEFT JOIN rate_plan as rp 
                    ON rp.rate_plan_id = b.rate_plan_id
                LEFT JOIN company as cm 
                    ON cm.company_id = b.company_id
                LEFT JOIN booking_source bs
                    ON bs.id = b.source
                LEFT JOIN booking_staying_customer_list as bscl ON bscl.booking_id = b.booking_id
                LEFT JOIN customer as sg ON bscl.customer_id = sg.customer_id
                LEFT JOIN booking_x_group AS bxs ON bxs.booking_id = brh.booking_id 
                LEFT JOIN booking_x_booking_linked_group AS bxblg ON bxblg.booking_id = brh.booking_id 
                LEFT JOIN booking_linked_group AS blg ON blg.id = bxblg.booking_group_id
                $join_customer_type
                $join_booking_fields
                $where_conditions
                GROUP BY b.booking_id
                $order_by $order";
        
        return $sql;
    }

    /*
     * Generate and return where conditions for inhouse
     * */
    function _get_inhouse_where_conditions($filters, $company_id)
    {
        //Generate booking type portion of the SQL statement
        $state_sql = "";
        if (isset($filters['state']))
        {
            if ($filters['state'] == 'active')  // Show all reservation, inhouse, and checkout guests. Called from calendar
            {
                $state_sql = "AND (b.state <= 4 OR b.state = '".UNCONFIRMED_RESERVATION."')";
            }
            elseif ($filters['state'] == 'all' || $filters['state'] == '' || $filters['state'] == 6 )
            {
                $state_sql = "";
            }
            elseif($filters['state'] == 10 || $filters['state'] == 11) // arrival, departure 
            {
                $state_sql = "";
            }
            else{
                $state_sql = "AND b.state = '".$filters['state']."'";
            }            
        }
        
        //Generate time portion of the SQL statement
        $time_sql = "";

        $date_overlap_sql = "";
        
        
        
        if(isset($filters['is_channel_manager']) && $filters['is_channel_manager'] == 1)
        {
            if($filters['state'] == 'active' || $filters['state'] == '0')
            {
                $time_sql .= "AND (DATE(bl.date_time) <= '".$filters['end_date']."' AND DATE(bl.date_time) >= '".$filters['start_date']."')";
            }
            if(isset($filters['booking_source']) && $filters['booking_source'] != 'active')
            {
                $time_sql .= "AND b.source = ".$filters['booking_source'];
            }
        }
        else
        {
            if($filters['state'] == 'active' || $filters['state'] == '0')
            {
                $time_sql .= "AND (DATE(check_in_date) <= '".$filters['end_date']."' AND DATE(check_out_date) >= '".$filters['start_date']."')";
                //$time_sql = " AND DATE(bl.date_time) >= '".$filters['start_date']."' AND DATE(bl.date_time) <= '".$filters['end_date']."'";
            }
        }
        
        if($filters['state'] == '1') // check-in 
        {
            //$time_sql .= " AND DATE(bl_checkin.date_time) >= '".$filters['start_date']."' AND DATE(bl_checkin.date_time) <= '".$filters['end_date']."'";
        }
        else if($filters['state'] == '2')// check-out
        {
            $time_sql .= " AND DATE(bl.date_time) <= '".$filters['end_date']."' AND DATE(check_in_date) <= '".$filters['end_date']."'  AND DATE(bl_checkout.date_time) >= '".$filters['start_date']."' AND DATE(bl_checkout.date_time) <= '".$filters['end_date']."'";
        }
        else if($filters['state'] == '3')// out of order
        {
            $time_sql .= " AND DATE(bl.date_time) <= '".$filters['end_date']."' AND DATE(check_in_date) <= '".$filters['end_date']."' AND DATE(check_out_date) >= '".$filters['start_date']."' ";
        }
        else if($filters['state'] == '4')// cancelled
        {
            $time_sql .= " AND DATE(bl.date_time) <= '".$filters['end_date']."' AND DATE(bl_cancelled.date_time) >= '".$filters['start_date']."' AND DATE(bl_cancelled.date_time) <= '".$filters['end_date']."'";
        }
        else if($filters['state'] == '5')// no show
        {
            $time_sql .= " AND DATE(bl.date_time) <= '".$filters['end_date']."' AND DATE(bl_noshow.date_time) >= '".$filters['start_date']."' AND DATE(bl_noshow.date_time) <= '".$filters['end_date']."'";
        }
        else if($filters['state'] == '7')// unconfirmed
        {
            $time_sql .= " AND DATE(bl.date_time) <= '".$filters['end_date']."' AND DATE(bl_unconfirmed.date_time) >= '".$filters['start_date']."' AND DATE(bl_unconfirmed.date_time) <= '".$filters['end_date']."'";
        }
        else if($filters['state'] == '6')// deleted
        {
            $time_sql .= " AND DATE(bl.date_time) <= '".$filters['end_date']."' AND DATE(bl_deleted.date_time) >= '".$filters['start_date']."' AND DATE(bl_deleted.date_time) <= '".$filters['end_date']."'";
        }
        else if($filters['state'] == '10')// departure
        {
            //$time_sql .= " AND DATE(bl.date_time) <= '".$filters['end_date']."' AND ((DATE(bl_departure.date_time) >= '".$filters['start_date']."' AND DATE(bl_departure.date_time) <= '".$filters['end_date']."') OR (check_out_date <= '".$filters['end_date']."' AND check_out_date >= '".$filters['start_date']."') )";
            $time_sql .= " AND DATE(bl.date_time) <= '".$filters['end_date']."' AND DATE(check_out_date) <= '".$filters['end_date']."' AND DATE(check_out_date) >= '".$filters['start_date']."'";
        }
        else if($filters['state'] == '11')// Arrivals
        {
            //$time_sql .= " AND DATE(bl.date_time) <= '".$filters['end_date']."' AND ((DATE(bl_arrivals.date_time) >= '".$filters['start_date']."' AND DATE(bl_arrivals.date_time) <= '".$filters['end_date']."'))";
            $time_sql .= " AND DATE(bl.date_time) <= '".$filters['end_date']."' AND DATE(check_in_date) <= '".$filters['end_date']."' AND DATE(check_in_date) >= '".$filters['start_date']."'";
        }
        
        
        // set search query
        if($filters['state'] == 6){
            $is_deleted = '0';
        }
        else{
            $is_deleted = '1';
        }
        $where_conditions = " WHERE 
            b.company_id = '$company_id' AND
            b.is_deleted != $is_deleted
            $time_sql
            $state_sql 
            $date_overlap_sql
        ";
        return $where_conditions;
    }
    
    function get_group_ids($from_date, $to_date)
    {
        $this->db->select('brh.*, bxblg.*');
        $this->db->from('booking_block as brh');
        $this->db->join('booking_x_booking_linked_group as bxblg', 'brh.booking_id = bxblg.booking_id');
        $this->db->where('DATE(brh.check_in_date) >=', $from_date);
        $this->db->where('DATE(brh.check_out_date) <=', $to_date);
        $this->db->group_by('bxblg.booking_group_id');
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    function update_booking_email_status($booking_id)
    {
        $data = Array('is_invoice_auto_sent' => '1');

        $this->db->where('booking_id', $booking_id);
        $this->db->update("booking", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
    
    function check_overbooking($start_date, $end_date, $room_id = null, $booking_id = null){
        $room_sql = $booking_sql = '';
        if($room_id)
        {
            $room_sql = "AND `room_history`.`room_id` = $room_id";
        }
        if($booking_id)
        {
            $booking_sql = "AND `room_history`.`booking_id` != $booking_id";
        }
        $sql = "SELECT room_history.*
                    FROM `booking_block` as `room_history`
                    LEFT JOIN `booking` as `b` ON `room_history`.`booking_id` = b.`booking_id`
                    WHERE 
                    ('$start_date' < (room_history.check_out_date) AND (room_history.check_in_date) < '$end_date') AND 
                    b.`is_deleted` = '0' AND 
                    b.company_id = '$this->company_id' AND    
                    b.`state` != 4 
                    $room_sql 
                    $booking_sql";

        $query = $this->db->query($sql);
        if($query->num_rows() >= 1){
            return $query->result_array();
        }
        return NULL;
    }


    function create_booking_fields($booking_id,$booking_fields)
    {
        if ($booking_fields && count($booking_fields) > 0) {
            $data = Array();
            foreach ($booking_fields as $booking_field_id => $value) {
                $data[] = Array(
                    "booking_id" => $booking_id,
                    "booking_field_id" => $value['id'],
                    "value" => $value['value']
                );
            }
            $this->db->insert_batch('booking_x_booking_field', $data);
        }
    }

    function update_booking_fields($booking_id, $booking_fields)
    {
        if ($booking_fields && count($booking_fields) > 0) {
            $data = Array();
            foreach ($booking_fields as $booking_field_id => $value) {
                $this->db->where('booking_id', $booking_id);
                $this->db->where('booking_field_id', $value['id']);
                $q = $this->db->get('booking_x_booking_field');


                if ($q->num_rows() > 0) {
                    $this->db->where('booking_id', $booking_id);
                    $this->db->where('booking_field_id', $value['id']);
                    $this->db->update('booking_x_booking_field', array("value" => $value['value']));
                } else {
                    $data = Array(
                        "booking_id" => $booking_id,
                        "booking_field_id" => $value['id'],
                        "value" => $value['value']
                    );

                    $this->db->insert('booking_x_booking_field', $data);
                }
            }
        }
    }

    function get_booking_fields($booking_id)
    {
        $this->db->where('company_id', $this->company_id);
        $this->db->where('show_on_booking_form', 1);
        $this->db->where('is_deleted', 0);
        $this->db->from('booking_field as bf');
        $this->db->join('booking_x_booking_field as bxbf', "bxbf.booking_field_id = bf.id and bxbf.booking_id = '$booking_id'", 'left');
        $query = $this->db->get();
        $booking_fields_result = $query->result_array();

        $booking_fields = array();
        foreach($booking_fields_result as $field)
        {
            $booking_fields[$field['id']] = $field['value'];
        }
        return $booking_fields;
    }

    function get_ota_bookings($company_id){

        $where = "(JSON_TYPE(JSON_EXTRACT(customer_meta_data, '$.token')) != 'NULL')";

        $this->db->from('booking as b');
        $this->db->join('booking_block as bb', 'bb.booking_id = b.booking_id');
        $this->db->join('customer as c', 'c.customer_id = b.booking_customer_id');
        $this->db->join('customer_card_detail as ccd', 'ccd.customer_id = c.customer_id');
        $this->db->where('c.is_deleted','0');
        $this->db->where('b.is_deleted','0');
        // $this->db->where('b.is_ota_booking','1');
        $this->db->where($where);
        $this->db->where('b.company_id',$company_id);
        $this->db->where('UNIX_TIMESTAMP(bb.check_out_date) + (86400 * 7) < UNIX_TIMESTAMP(NOW())');

        $query = $this->db->get();

        if($query->num_rows() >= 1){
            return $query->result_array();
        }
        return NULL;
    }

    function delete_bookings($company_id)
    {
        $data = Array('is_deleted' => 1);

        $this->db->where('company_id', $company_id);
        $this->db->update("booking", $data);

        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }

    }

    function get_bookings_company($company_id)
    {
        $this->db->from('booking as b');
        $this->db->where('company_id', $company_id);

        $query = $this->db->get();

        if($query->num_rows() >= 1){
            return $query->result_array();
        }
        return NULL;

    }

    function create_booking_staying_customer($data){
        $this->db->insert('booking_staying_customer_list', $data);
    }

    function get_booking_staying_customer_by_id($customer_id,$company_id,$booking_id){

        $this->db->where('customer_id',$customer_id);
        $this->db->where('company_id',$company_id);
        $this->db->where('booking_id',$booking_id);

        $query = $this->db->get('booking_staying_customer_list');

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

}
