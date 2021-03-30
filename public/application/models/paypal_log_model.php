<?php

/**
 * Ipn_log_model
 *
 * @package    codeigniter-paypal-ipn
 * @author     Alexander Dean, alex@keplarllp.com
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 *
 * This file is copyright (c) 2011 Alexander Dean, github@keplarllp.com
 *
 * This file is part of codeigniter-paypal-ipn
 *
 * codeigniter-paypal-ipn is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * codeigniter-paypal-ipn is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * codeigniter-paypal-ipn. If not, see <http://www.gnu.org/licenses/>.
 */

class Paypal_log_model extends CI_Model
{
    // Holds the query for the cache
    private $cacheQuery = array();

    // Table name is a constant
    const LOG_TABLE = 'ipn_log';

    // Standard constructor
    function __construct()
    {
        parent::__construct();

        // Define once how we access the cache record
        $this->cacheQuery = array('listener_name'    => 'IPN',
                                  'transaction_type' => 'cache');
    }

    // Retrieve the IPN cache row from the IPN log (if there is one)
    function getCache()
    {
        $query = $this->db->get_where(self::LOG_TABLE, $this->cacheQuery);
        return $query->row();
    }

    // Retrieve an IPN log record as identified by its IPN data hash
    function getOneByHash($hash)
    {
        $query = $this->db->get_where(self::LOG_TABLE, array('ipn_data_hash' => $hash));
        return $query->row();
    }

    // Store the IPN cache row in the IPN log (updating the existing cache record if there is one)
    function saveCache($ipnDataRaw)
    {
        // Define the cache record to populate
        $cacheRecord = $this->cacheQuery;
        $cacheRecord['detail'] = serialize($ipnDataRaw);

        // Now run the insert/update
        $this->_upsert($cacheRecord, $this->cacheQuery);
    }

    // Store a row in the IPN log
    function saveLog($logRecord, $logID)
    {
        // Define the query to determine whether to update or insert
        $logQuery = (is_null($logID)) ? null : array('id' => $logID);
        
        // Now run the insert/update, returning the ID
        return $this->_upsert($logRecord, $logQuery);
    }

    // Helper function to insert or update depending on whether the relevant row exists already
    function _upsert($record, $query = null)
    {
        // Check if the record exists already, using the supplied query
        $exists = FALSE;
        if (!is_null($query))
        {
            $existingRecord = $this->db->get_where(self::LOG_TABLE, $query, 1, 0);
            if ($existingRecord->num_rows() > 0)
            {
                $exists = TRUE;
            }
        }

        // Set the update/insert date
        $upsertTime = date('Y-m-d H:i:s');
        $record['updated_at'] = $upsertTime; // All inserts/updates set the updated_at time

        // If it exists, then update
        $retVal = null;
        if ($exists)
        {
            $this->db->update(self::LOG_TABLE, $record, $query);
        }
        // Otherwise let's insert
        else
        {
            $record['created_at'] = $upsertTime; // All inserts need a created_at time too
            $this->db->insert(self::LOG_TABLE, $record);
            $retVal = $this->db->insert_id();
        }

        return $retVal; // Return the ID for an insert or null for an update
    }
}