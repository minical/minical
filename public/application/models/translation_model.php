<?php

class Translation_model extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();		
    }
    
    // function for fetching all records from table
    function get_records($table)
    {
        $query = $this->db->get($table);
        return $query->result_array();
    }
    
    // function for fetching records from table by using where condition
    function get_record_where($table, $where)
    {
        $this->db->where($where);
        $query = $this->db->get($table);
        return $query->result_array();
    }
    
    // function for insert records in the table
    function insert_records($table, $data = array())
    {
        $query = $this->db->insert($table, $data);
        return $this->db->insert_id();
    }
    
    // function for update records in the table with conditions
    function update_records($table, $data, $where)
    {
        $this->db->where($where);
        $this->db->update($table, $data);
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
    
    // function for delete record from table with where conditions 
    function delete_records($table, $where)
    {
        $this->db->where($where);
        $this->db->delete($table); 
        
        if ($this->db->_error_message())
        {
            show_error($this->db->_error_message());
        }
    }
    
    // function to get translation data by language id 
    function get_translation_data($language_id = null, $current_language_id = null, $translation_key = null, $translation_key_id = null)
    {
        if($translation_key_id && $language_id)
        {
            $query = "SELECT * FROM `language_translation` where language_id = '".$language_id."' AND phrase_id = '".$translation_key_id."'";
            return $this->db->query($query)->row_array();
        }
        $translation_key = $this->db->escape($translation_key);
        if($current_language_id && $translation_key)
        {
            $this->db->select('p.*');
            $this->db->from('language_phrase as p');
            $this->db->join('language_translation as t', 't.phrase_id = p.id', 'inner');
            $this->db->where('t.language_id', $current_language_id);
            $this->db->where('t.phrase', $translation_key);
            $query = $this->db->get();
            
            if($query->num_rows() > 1)
            {
                return $query->result_array();
            }
            
            $sql = "SELECT * 
                      FROM `language_translation` 
                      WHERE language_id = '".$language_id."'
                            AND phrase_id = (SELECT phrase_id 
                                FROM `language_translation` 
                                WHERE language_id = '".$current_language_id."' AND phrase = ".$translation_key."
                            )";
            $query = $this->db->query($sql);
            if($query->num_rows() > 0)
            {
                return $query->row_array();
            }
            
            return $query->row_array();
        }
        
        
        $this->db->select('p.*,t.*,p.id as pid,t.id as tid');
        $this->db->from('language_phrase as p');
        $this->db->join('language_translation as t', 't.phrase_id = p.id', 'inner');
        $this->db->where(array('language_id'=>$language_id));
        $query = $this->db->get();
        
        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        return NULL;
    }

    function get_all_phrases ($language_id, $non_translated_phrase) {
        $join = "INNER";
        if($language_id){
            $join = "LEFT";
        }

        $sql = "SELECT 
                    `p`.*, `t`.*, 
                    `p`.`id` as pid, 
                    `t`.`id` as tid, 
                    IFNULL(te.phrase, `p`.phrase_keyword) as default_key 
                FROM (`language_phrase` as p) 
                $join JOIN `language_translation` as t 
                    ON `t`.`phrase_id` = `p`.`id` AND t.language_id = '$language_id'
                LEFT JOIN `language_translation` as te 
                    ON `te`.`phrase_id` = `p`.`id` AND te.language_id = 1 ";

            if($non_translated_phrase) {
                $sql .= " WHERE (t.phrase IS NULL OR t.phrase = '')";
            }
        
        $query = $this->db->query($sql);

        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        return NULL;
    }
    
    // function to check phrase keywords for same lanaguage 
    function check_phrase_keyword_with_language($phrase_keyword, $language_id, $phrase_only = null)
    {
        if($phrase_only)
        {
            $this->db->from('language_phrase');
            $this->db->where('phrase_keyword', $phrase_keyword);
            $query = $this->db->get();
            if($query->num_rows() > 0)
            {
                return $query->row_array();
            }
        }
        $this->db->select('p.id, p.phrase_keyword, t.phrase');
        $this->db->from('language_phrase as p');
        $this->db->join('language_translation as t', 't.phrase_id = p.id', 'inner');
        $this->db->where(array('language_id' => $language_id, 'phrase_keyword' => $phrase_keyword));
        $query = $this->db->get();
        
        if($query->num_rows() > 0)
        {
            return $query->row_array();
        }
        return NULL;
    }
    
    function get_existing_phrase_of_translation($translation) {
        
        $translation = $this->db->escape($translation);
        $sql = "SELECT phrase_id
                FROM `language_translation` 
                WHERE phrase = $translation";
        
        $query = $this->db->query($sql);
        if($query->num_rows() > 0)
        {
            return $query->row_array()['phrase_id'];
        }
        return null;
    }
    
    // function to insert translation 
    function insert_translation_records($table, $data = array())
    {
        $query = $this->db->insert_batch($table, $data);
        return $this->db->insert_id();
    }
    
    
    function insert_translation_records1($table, $data = array())
    {
        $query = $this->db->insert($table, $data);
        return $this->db->insert_id();
    }
    
    
    // function to get all phrases of language
    function get_all_phrases_by_language($language_id)
    {
        $this->db->select('p.id,p.phrase_keyword,t.phrase');
        $this->db->from('language_phrase as p');
        $this->db->join('language_translation as t', 't.phrase_id = p.id', 'inner');
        $this->db->where(array('language_id'=>$language_id));
        $query = $this->db->get();
        
        if($query->num_rows() > 0)
        {
            return $query->result_array();
        }
        return NULL;
    }
}