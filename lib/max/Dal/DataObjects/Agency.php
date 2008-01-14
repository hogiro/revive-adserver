<?php

/*
+---------------------------------------------------------------------------+
| Openads v${RELEASE_MAJOR_MINOR}                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

/**
 * Table Definition for agency
 */
require_once 'DB_DataObjectCommon.php';

class DataObjects_Agency extends DB_DataObjectCommon
{
    var $onDeleteCascade = true;
    var $refreshUpdatedFieldIfExists = true;

    /**
     * BC-compatible user details
     *
     * @todo Please remove later
     */
    var $username;
    var $password;
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'agency';                          // table name
    var $agencyid;                        // int(9)  not_null primary_key auto_increment
    var $name;                            // string(255)  not_null
    var $contact;                         // string(255)  
    var $email;                           // string(64)  not_null
    var $logout_url;                      // string(255)  
    var $active;                          // int(1)  
    var $updated;                         // datetime(19)  not_null binary
    var $account_id;                      // int(9)  multiple_key

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Agency',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    /**
     * Handle all necessary operations when new agency is created
     *
     * @see DB_DataObject::insert()
     */
    function insert()
    {
        // Create account first
        $result = $this->createAccount(OA_ACCOUNT_MANAGER, $this->name);
        if (!$result) {
            return $result;
        }

        // Store data to create a user
        if (!empty($this->username) && !empty($this->password)) {
            $aUser = array(
                'contact_name' => $this->contact,
                'email_address' => $this->email,
                'username' => $this->username,
                'password' => $this->password,
                'default_account_id' => $this->account_id
            );
        }

        $agencyid = parent::insert();
        if (!$agencyid) {
            return $agencyid;
        }

        // Create user if needed
        if (!empty($aUser)) {
            $this->createUser($aUser);
        }

        return $agencyid;
    }

    /**
     * Handle all necessary operations when an agency is updated
     *
     * @see DB_DataObject::update()
     */
    function update($dataObject = false)
    {
        // Store data to create a user
        if (!empty($this->username) && !empty($this->password)) {
            $aUser = array(
                'contact_name' => $this->contact,
                'email_address' => $this->email,
                'username' => $this->username,
                'password' => $this->password,
                'default_account_id' => $this->account_id
            );
        }

        $ret = parent::update($dataObject);
        if (!$ret) {
            return $ret;
        }

        // Create user if needed
        if (!empty($aUser)) {
            $this->createUser($aUser);
        }

        $this->updateAccountName($this->name);

        return $ret;
    }

    /**
     * Handle all necessary operations when an agency is deleted
     *
     * @see DB_DataObject::delete()
     */
    function delete($useWhere = false, $cascade = true, $parentid = null)
    {
        $result =  parent::delete($useWhere, $cascade, $parentid);
        if ($result) {
            $this->deleteAccount();
        }

        return $result;
    }

    /**
     * Returns agencyid.
     *
     * @return string
     */
    function getUserId()
    {
        return $this->agencyid;
    }

    function _auditEnabled()
    {
        return true;
    }

    function _getContextId()
    {
        return $this->agencyid;
    }

    function _getContext()
    {
        return 'Agency';
    }

    /**
     * build an agency specific audit array
     *
     * @param integer $actionid
     * @param array $aAuditFields
     */
    function _buildAuditArray($actionid, &$aAuditFields)
    {
        $aAuditFields['key_desc']     = $this->name;
        switch ($actionid)
        {
            case OA_AUDIT_ACTION_UPDATE:
                        break;
            case OA_AUDIT_ACTION_INSERT:
            case OA_AUDIT_ACTION_DELETE:
                        $aAuditFields['active']     = $this->_formatValue('active');
                        break;
        }
    }

    function _formatValue($field)
    {
        switch ($field)
        {
            case 'active':
                return $this->_boolToStr($this->$field);
            default:
                return $this->$field;
        }
    }

}

?>
