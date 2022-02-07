<?php
/* add a new room in room table. you can only add a new room if your number_of_rooms capacity not full 
  in company.
* Supported hooks:
* before_add_room: the filter executed before add room
* should_add_room: the filter executed to check add room.
* pre.add.room: the hook executed before add room. 
* post.add.room: the hook executed after added room.
* @param array $room (Required) includes following attributes:
* $data['room_name'] : the room_name (character) of specific room.
* $data['room_type_id'] : the room_type_id (integer) for specific room.
* $data['sort_order'] : the sort_order (integer) for specific room.
* $data['status'] : the status (character) for specific room.
* $data['company_id'] : the company_id (integer) for specific room.
* $data['group_id'] : the group_id (integer) for specific room.
* $data['floor_id'] : the floor_id (integer) for specific room.
* $data['location_id'] : the location_id (integer) for specific room.
* $data['score'] : the score (integer) for specific room.
* $data['instructions'] : the instructions (text)for specific room.
* $data['can_be_sold_online'] : the can_be_sold_online (integer) for specific room.
* and many more attributes for table room.
* @return $response: array value of the room data. A value of any type may be returned, If there  
   is no room in the database, boolean false is returned
* $response array includes following attributes:
* $response['key'] : the key of specific room
*
*/
function add_room($room)
{

    $CI = & get_instance();
    $CI->load->model('Room_model');
    $CI->load->model('Company_model');
    $CI->load->library('session');

    if(empty($room)){
        return null;
    }

    if (isset($room['company_id'])) {
       $company_id = $room['company_id'];
    }else{
       $company_id = $CI->session->userdata('current_company_id');
    }
    
    $company_info = $CI->Company_model->get_company($company_id);
    $number_of_rooms = $company_info['number_of_rooms'];
    $room_detail = $CI->Room_model->get_rooms($company_id);
    
    $actual_used_room = count($room_detail);
    
    if ($actual_used_room < $number_of_rooms) {
        $data = apply_filters( 'before_add_room', $room, $CI->input->post());
        $should_add_room = apply_filters( 'should_add_room', $room, $CI->input->post());
        if (!$should_add_room) {
            return;
        }

        $room_data = array(
                'room_name' => $room['room_name'],
                'company_id' => $company_id,
                'room_type_id' => $room['room_type_id'],

                'sort_order' => isset($room['sort_order']) ? $room['sort_order'] : 0 ,

                'can_be_sold_online' => isset($room['can_be_sold_online']) ? $room['can_be_sold_online'] : 1,

                'status' => isset($room['status']) ? $room['status'] : 'Clean' ,

                'notes' => isset($room['notes']) ? $room['notes'] : '' ,

                'group_id' => isset($room['group_id']) ? $room['group_id'] : 0,

                'floor_id' => isset($room['floor_id']) ? $room['floor_id'] : 0,

                'location_id' => isset($room['location_id']) ? $room['location_id'] : 0 ,

                'is_hidden' => isset($room['is_hidden']) ? $room['is_hidden'] : 0,

                'score' => isset($room['score']) ? $room['score'] : 0,

                'instructions' => isset($room['instructions']) ? $room['instructions'] : '' 

            );

        do_action('pre.add.room', $room_data, $CI->input->post());

        $room_id = $CI->Room_model->create_room_data($room_data);

        do_action('post.add.room', $room_id, $room_data, $CI->input->post());

        if(isset($room_id)){
            return $room_id;
        }

    }else{
        return null;
    }
    
}

/* Retrieves a room value based on a room_id.
* Supported hooks:
* before_get_room: the filter executed before get room
* should_get_room: the filter executed to check get room.
* pre.get.room: the hook executed before getting room. 
* post.get.room: the hook executed after getting room
* @param string $room_id (Required) The primary id for room table
*
* @return $response: array value of the room data. A value of any type may be returned, If there  
   is no room in the database, boolean false is returned
* $response array includes following attributes:
* $response['room_name'] : the room_name of specific room.
* $response['room_type_id'] : the room_type_id for specific room.
* $response['sort_order'] : the sort_order for specific room.
* $response['status'] : the status for specific room.
* $response['company_id'] : the company_id for specific room.
* $response['room_type_name'] : the room_type_name for specific room.
* $response['can_be_sold_online'] : the can_be_sold_online for specific room.
* and many more attributes for table room and join with room type table.
*/

function get_room(int $room_id = null )
{
    $get_room_data = null;
    $CI = & get_instance();
    $CI->load->model('Room_model');

    // filters
    $data = apply_filters( 'before_get_room', $room_id, $CI->input->post());
    $should_get_room = apply_filters( 'should_get_room', $room_id, $CI->input->post());

    if (!$should_get_room) {
        return;
    }

    if(isset($room_id) && $room_id == null){
        return null;
    }

    // before getting room 
    do_action('pre.get.room', $room_id, $CI->input->post());

    $get_room_data = $CI->Room_model->get_room($room_id);

    // after getting room
    do_action('post.get.room', $room_id, $CI->input->post());
     
    return $get_room_data;

}

/* Retrieves a room value based on a filter.
* Supported hooks:
* before_get_rooms: the filter executed before get room
* should_get_rooms: the filter executed to check get room.
* pre.get.rooms: the hook executed before getting room. 
* post.get.rooms: the hook executed after getting room
* @param array $filter (Required) The data for room table
*
* @return $response: array value of the room data. A value of any type may be returned, If there  
   is no room in the database, boolean false is returned
* $response array includes following attributes:
* $response['room_name'] : the room_name of specific room.
* $response['room_type_id'] : the room_type_id for specific room.
* $response['sort_order'] : the sort_order for specific room.
* $response['status'] : the status for specific room.
* $response['company_id'] : the company_id for specific room.
* $response['room_type_name'] : the room_type_name for specific room.
* $response['can_be_sold_online'] : the can_be_sold_online for specific room.
* and many more attributes for table room and join with room type table.
*/

function get_rooms(array $filter = null)
{
    $get_room_data = null;
    $CI = & get_instance();
    $CI->load->model('Room_model');

    // filters
    $data = apply_filters( 'before_get_rooms', $filter, $CI->input->post());
    $should_get_room = apply_filters( 'should_get_rooms', $filter, $CI->input->post());

    if (!$should_get_room) {
        return;
    }

    if(empty($filter)){
        return null;
    }

    // before getting room 
    do_action('pre.get.rooms', $filter, $CI->input->post());

    $get_room_data = $CI->Room_model->get_rooms_data($filter);

    // after getting room
    do_action('post.get.rooms',$filter, $CI->input->post());
     
    return $get_room_data;

}

/* update a room details in room table.
* Supported hooks:
* before_update_room: the filter executed before update room
* should_update_room: the filter executed to check update room.
* pre.update.room: the hook executed before update room. 
* post.update.room: the hook executed after update room.
* @param array $room (Required) includes following attributes:
* @param int $room_id The id of the room corresponds to the room data.
* $data['room_name'] : the room_name (character) of specific room.
* $data['room_type_id'] : the room_type_id (integer) for specific room.
* $data['sort_order'] : the sort_order (integer) for specific room.
* $data['status'] : the status (character) for specific room.
* $data['company_id'] : the company_id (integer) for specific room.
* $data['group_id'] : the group_id (integer) for specific room.
* $data['floor_id'] : the floor_id (integer) for specific room.
* $data['location_id'] : the location_id (integer) for specific room.
* $data['score'] : the score (integer) for specific room.
* $data['instructions'] : the instructions (text)for specific room.
* $data['can_be_sold_online'] : the can_be_sold_online (integer) for specific room.
* and many more attributes for table room.
* @return mixed Either true or null, if room data is updated then true else null.
*
*/

function update_room(array $room = null, int $room_id = null)
{
    $updated_flag = null;
    $CI = & get_instance();
    $CI->load->model('Room_model');

    //filters
    $data = apply_filters( 'before_update_room', $room_id, $CI->input->post());
    $should_update_room = apply_filters( 'should_update_room', $room_id, $CI->input->post());

    if (!$should_update_room) {
        return;
    }

    if(empty($room) && $room_id == null){
        return null;
    }

    $get_room_data = $CI->Room_model->get_room($room_id);

    $room_data = array(
                'room_name' => isset($room['room_name']) ?$room['room_name'] : $get_room_data['room_name'],
                'company_id' => $get_room_data['company_id'],
                'room_type_id' => isset($room['room_type_id']) ?  $room['room_type_id'] : $get_room_data['room_type_id'],

                'sort_order' => isset($room['sort_order']) ? $room['sort_order'] : $get_room_data['sort_order'] ,

                'can_be_sold_online' => isset($room['can_be_sold_online']) ? $room['can_be_sold_online'] : $get_room_data['can_be_sold_online'],

                'status' => isset($room['status']) ? $room['status'] : $get_room_data['status'],

                'notes' => isset($room['notes']) ? $room['notes'] : $get_room_data['notes'] ,

                'group_id' => isset($room['group_id']) ? $room['group_id'] :$get_room_data['group_id'],

                'floor_id' => isset($room['floor_id']) ? $room['floor_id'] : $get_room_data['floor_id'],

                'location_id' => isset($room['location_id']) ? $room['location_id'] : $get_room_data['location_id'],

                'is_hidden' => isset($room['is_hidden']) ? $room['is_hidden'] : $get_room_data['is_hidden'],

                'score' => isset($room['score']) ? $room['score'] : $get_room_data['score'],

                'instructions' => isset($room['instructions']) ? $room['instructions'] :$get_room_data['instructions'] 

            );
   
    // before updating room 
    do_action('pre.update.room', $room_id, $CI->input->post());
    
    $updated_flag = $CI->Room_model->update_room($room_id,$room_data);
    if(empty($updated_flag)){
        return null;
    }
    // before updating room 
    do_action('post.update.room', $room_id, $room_data, $CI->input->post());
     
    return true;
}

/**
 * delete a room data.
 * Supported hooks:
 * before_delete_room: the filter executed before delete room.
 * should_delete_room: the filter executed to check delete room.
 * pre.delete.room: the hook executed before delete room. 
 * post.delete.room: the hook executed after delete room.
 * @param int $room_id The id of the room corresponds to the room table.
 * @return mixed Either true or null, if room data is deleted then true else null.
 * 
 */
function delete_room(int $room_id = null)
{ 
    $CI = & get_instance();
    $CI->load->model('Room_model');

    // filters 
    $data = apply_filters( 'before_delete_room', $room_id, $CI->input->post());
    $should_delete_room = apply_filters( 'should_delete_room', $room_id, $CI->input->post());
    if (!$should_delete_room) {
        return;
    }

    if(isset($room_id) && $room_id == null){
        return null;
    }

    //for action befor deleting the room
    do_action('pre.delete.room', $room_id, $CI->input->post()); 
  
    $delete_flag = $CI->Room_model->delete_room_data($room_id);
    if(empty($delete_flag)){
        return null;
    }
    
    //for action after deleting the room
    do_action('post.delete.room', $room_id, $CI->input->post());
  
    return true;
}