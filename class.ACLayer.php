<?php

// Version 1.0.0

require_once('Zend/Db/Adapter/Abstract.php');
require_once('class.genericAPI.php');

class ACLayer{
    protected $config = array();
    protected $db = null;
    protected $dataQuery = null;
    protected $vars = array();
    protected $sanity = array();

    protected final function sanityCheck($what, $check, $vars){
        switch($check){
            case 'set':
                if(isset($vars[$what])){return(1);}else{return(0);}
                break;
            case 'empty':
                if(empty($vars[$what])){return(1);}else{return(0);}
                break;
        }
    }

    // Build class with base config and database connection
    public function __construct(Zend_Db_Adapter_Abstract $db, $config = array()){
        $this->db = $db;
        if(!empty($config)){$this->config = $config;}
    }

    // Function to build the acl query, or to select extra data
    protected function _buildQuery($selectType = null, $vars = array()){
        $this->dataQuery = $this->db->select();
        $dataStatement = array();

        switch($selectType){
            case 'group_exists_idcheck':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('gidEq', $vars);
                break;
            case 'group_exists_namecheck':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('gnameEq', $vars);
                break;
            case 'group_getid_name':
                $dataStatement[] = $this->config['groupidcol'];
                $this->_bq_attachWhere('gnameEq', $vars);
                break;
            case 'group_member':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('residGroupEq', $vars);
                $this->_bq_attachWhere('restypeGroup', $vars);
                $this->_bq_attachWhere('supplicantuidEq', $vars);
                break;
            case 'group_memberships':
                $dataStatement['groupid'] = $this->config['residcol'];
                $this->_bq_attachWhere('restypeGroup', $vars);
                $this->_bq_attachWhere('supplicantuidEq', $vars);
                break;
            case 'permission_exists':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('permkeyEq', $vars);
                break;
            case 'permission_gaccess_allow':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('gidIn', $vars);
                $this->_bq_attachWhere('permkeyIn', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                break;
            case 'permission_gaccess_deny':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('gidIn', $vars);
                $this->_bq_attachWhere('permkeyIn', $vars);
                $this->_bq_attachWhere('permvalDeny', $vars);
                break;
            case 'permission_glist_allow':
                $dataStatement[] = $this->config['groupidcol'];
                $this->_bq_attachWhere('permscopeGlobal', $vars);
                $this->_bq_attachWhere('permkeyEq', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('ridNull', $vars);
                $this->_bq_attachWhere('uidNull', $vars);
                $this->dataQuery->group($this->config['groupidcol']);
                break;
            case 'permission_glist_deny':
                $dataStatement[] = $this->config['groupidcol'];
                $this->_bq_attachWhere('permscopeGlobal', $vars);
                $this->_bq_attachWhere('permkeyEq', $vars);
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->_bq_attachWhere('ridNull', $vars);
                $this->_bq_attachWhere('uidNull', $vars);
                $this->dataQuery->group($this->config['groupidcol']);
                break;
            case 'permission_raccess_allow':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('permkeyIn', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('ridIn', $vars);
                break;
            case 'permission_raccess_deny':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('permkeyIn', $vars);
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->_bq_attachWhere('ridIn', $vars);
                break;
            case 'permission_rlist_allow':
                $dataStatement[] = $this->config['roleidcol'];
                $this->_bq_attachWhere('permkeyEq', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('gidNull', $vars);
                $this->_bq_attachWhere('uidNull', $vars);
                $this->dataQuery->group($this->config['roleidcol']);
                break;
            case 'permission_rlist_deny':
                $dataStatement[] = $this->config['roleidcol'];
                $this->_bq_attachWhere('permkeyEq', $vars);
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->_bq_attachWhere('gidNull', $vars);
                $this->_bq_attachWhere('uidNull', $vars);
                $this->dataQuery->group($this->config['roleidcol']);
                break;
            case 'permission_uaccess_allow':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('permkeyIn', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('uidEq', $vars);
                break;
            case 'permission_uaccess_deny':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('permkeyIn', $vars);
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->_bq_attachWhere('uidEq', $vars);
                break;
            case 'permission_ulist_allow':
                $dataStatement[] = $this->config['uidcol'];
                $this->_bq_attachWhere('permkeyEq', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('gidNull', $vars);
                $this->_bq_attachWhere('ridNull', $vars);
                $this->dataQuery->group($this->config['uidcol']);
                break;
            case 'permission_ulist_deny':
                $dataStatement[] = $this->config['uidcol'];
                $this->_bq_attachWhere('permkeyEq', $vars);
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->_bq_attachWhere('gidNull', $vars);
                $this->_bq_attachWhere('ridNull', $vars);
                $this->dataQuery->group($this->config['uidcol']);
                break;

            case 'resource_exists':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('residEq', $vars);
                break;
            case 'resource_gaccess':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('gidIn', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('residEq', $vars);
                break;
            case 'resource_gfind':
                $dataStatement[] = $this->config['residcol'];
                $this->_bq_attachWhere('gidIn', $vars);
                $this->_bq_attachWhere('restypeEq', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                break;
            case 'resource_glist_allow':
                $dataStatement[] = $this->config['groupidcol'];
                $this->_bq_attachWhere('residEq', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->dataQuery->group($this->config['groupidcol']);
                break;
            case 'resource_glist_deny':
                $dataStatement[] = $this->config['groupidcol'];
                $this->_bq_attachWhere('residEq', $vars);
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->dataQuery->group($this->config['groupidcol']);
                break;
            case 'resource_gperm_allow':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('gidIn', $vars);
                $this->_bq_attachWhere('permkeyIn', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('residEq', $vars);
                break;
            case 'resource_gperm_deny':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('gidIn', $vars);
                $this->_bq_attachWhere('permkeyIn', $vars);
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->_bq_attachWhere('residEq', $vars);
                break;
            case 'resource_info':
                $dataStatement[] = $this->config['resowneruidcol'];
                $dataStatement[] = $this->config['restypeidcol'];
                $this->_bq_attachWhere('residEq', $vars);
                break;
            case 'resource_ownerid':
                $dataStatement[] = $this->config['resowneruidcol'];
                $this->_bq_attachWhere('residEq', $vars);
                break;
            case 'resource_uaccess':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('residEq', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('uidEq', $vars);
                break;
            case 'resource_ufind':
                $dataStatement[] = $this->config['residcol'];
                $this->_bq_attachWhere('uidEq', $vars);
                $this->_bq_attachWhere('restypeEq', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                break;
            case 'resource_umembers':
                $dataStatement[] = $this->config['uidcol'];
                $this->_bq_attachWhere('residEq', $vars);
                break;
            case 'resource_ulist_allow':
                $dataStatement[] = $this->config['uidcol'];
                $this->_bq_attachWhere('residEq', $vars);
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->dataQuery->group($this->config['uidcol']);
                break;
            case 'resource_ulist_deny':
                $dataStatement[] = $this->config['uidcol'];
                $this->_bq_attachWhere('residEq', $vars);
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->dataQuery->group($this->config['uidcol']);
                break;
            case 'resource_uperm_allow':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('residEq', $vars);
                $this->_bq_attachWhere('permkeyIn', $vars);
                $this->_bq_attachWhere('uidEq', $vars);
                break;
            case 'resource_uperm_deny':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('residEq', $vars);
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->_bq_attachWhere('permkeyIn', $vars);
                $this->_bq_attachWhere('uidEq', $vars);
                break;

            case 'role_exists_idcheck':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('ridEq', $vars);
                break;
            case 'role_glist_allow':
                $dataStatement[] = $this->config['groupidcol'];
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('ridEq', $vars);
                $this->_bq_attachWhere('uidNull', $vars);
                $this->_bq_attachWhere('gidNotNull', $vars);
                break;
            case 'role_glist_deny':
                $dataStatement[] = $this->config['groupidcol'];
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->_bq_attachWhere('ridEq', $vars);
                $this->_bq_attachWhere('uidNull', $vars);
                $this->_bq_attachWhere('gidNotNull', $vars);
                break;
            case 'role_gmember':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('ridEq', $vars);
                $this->_bq_attachWhere('gidIn', $vars);
                break;
            case 'role_gmemberships':
                $dataStatement[] = $this->config['roleidcol'];
                $this->_bq_attachWhere('roleContext', $vars);
                $this->_bq_attachWhere('gidEq', $vars);
                break;
            case 'role_permlist_allow':
                $dataStatement[] = $this->config['permkeycol'];
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('ridNull', $vars);
                $this->_bq_attachWhere('uidNull', $vars);
                $this->dataQuery->group($this->config['permkeycol']);
                break;
            case 'role_permlist_deny':
                $dataStatement[] = $this->config['permkeycol'];
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->_bq_attachWhere('ridNull', $vars);
                $this->_bq_attachWhere('uidNull', $vars);
                $this->dataQuery->group($this->config['permkeycol']);
                break;
            case 'role_ulist_allow':
                $dataStatement[] = $this->config['uidcol'];
                $this->_bq_attachWhere('permvalAllow', $vars);
                $this->_bq_attachWhere('ridEq', $vars);
                $this->_bq_attachWhere('gidNull', $vars);
                $this->_bq_attachWhere('uidNotNull', $vars);
                break;
            case 'role_ulist_deny':
                $dataStatement[] = $this->config['uidcol'];
                $this->_bq_attachWhere('permvalDeny', $vars);
                $this->_bq_attachWhere('ridEq', $vars);
                $this->_bq_attachWhere('gidNull', $vars);
                $this->_bq_attachWhere('uidNotNull', $vars);
                break;
            case 'role_umember':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('ridEq', $vars);
                $this->_bq_attachWhere('uidEq', $vars);
                break;
            case 'role_umemberships':
                $dataStatement[] = $this->config['roleidcol'];
                $this->_bq_attachWhere('roleContext', $vars);
                $this->_bq_attachWhere('uidEq', $vars);
                break;

            case 'user_exists_idcheck':
                $dataStatement['tf'] = SQL_TF_COUNTGTZERO;
                $this->_bq_attachWhere('uidEq', $vars);
                break;

            default: throw new exception('Query Type Unrecognized: Default: ' . $selectType); break;
        }

        // Database table selection
        switch($selectType){
            case 'group_exists_idcheck':
            case 'group_getid_name':
                $databasetable = $this->config['table_groups'];
                break;
            case 'group_member':
            case 'group_memberships':
            case 'permission_gaccess_allow':
            case 'permission_gaccess_deny':
            case 'permission_glist_allow':
            case 'permission_glist_deny':
            case 'permission_raccess_allow':
            case 'permission_raccess_deny':
            case 'permission_rlist_allow':
            case 'permission_rlist_deny':
            case 'permission_uaccess_allow':
            case 'permission_uaccess_deny':
            case 'permission_ulist_allow':
            case 'permission_ulist_deny':
            case 'resource_gaccess':
            case 'resource_gfind':
            case 'resource_glist_allow':
            case 'resource_glist_deny':
            case 'resource_gperm_allow':
            case 'resource_gperm_deny':
            case 'resource_uaccess':
            case 'resource_ufind':
            case 'resource_ulist_allow':
            case 'resource_ulist_deny':
            case 'resource_umembers':
            case 'resource_uperm_allow':
            case 'resource_uperm_deny':
            case 'role_exists_idcheck':
            case 'role_glist_allow':
            case 'role_glist_deny':
            case 'role_gmember':
            case 'role_gmemberships':
            case 'role_permlist_allow':
            case 'role_permlist_deny':
            case 'role_ulist_allow':
            case 'role_ulist_deny':
            case 'role_umember':
            case 'role_umemberships':
                $databasetable = $this->config['table_acl_mappermissions'];
                break;
            case 'permission_exists':
                $databasetable = $this->config['table_acl_permissions'];
                break;
            case 'resource_exists':
            case 'resource_info':
            case 'resource_ownerid':
                $databasetable = $this->config['table_acl_resources'];
                break;
            case 'user_exists_idcheck':
                $databasetable = $this->config['table_users'];
                break;
            default: throw new exception('Query Table Unrecognized: Default: ' . $selectType); break;
        }

        $this->dataQuery->from($databasetable, $dataStatement);
    }

    protected function _bq_attachWhere($whereID = null, $vars = array()){
        switch($whereID){
            case 'gidEq':
                $this->_sanityRun_acl('groupid_noempty', $vars);
                $this->dataQuery->where($this->config['groupidcol'] . ' = ?', $vars['groupid']);
                break;

            case 'gidIn':
                $this->_sanityRun_acl('groupid_noempty', $vars);
                if(empty($vars['groupid'])){$vars['groupid'] = 'NULL';}
                $this->dataQuery->where($this->config['groupidcol'] . ' IN(?)', $vars['groupid']);
                break;

            case 'gidNotNull':
                $this->dataQuery->where($this->config['groupidcol'] . ' IS NOT NULL');
                break;

            case 'gidNull':
                $this->dataQuery->where($this->config['groupidcol'] . ' IS NULL');
                break;

            case 'gnameEq':
                $this->_sanityRun_acl('groupname_noempty', $vars);
                $this->dataQuery->where($this->config['groupnamecol'] . ' = ?', $vars['groupname']);
                break;

            case 'permkeyEq':
                $this->_sanityRun_acl('permkey_noempty', $vars);
                $this->dataQuery->where($this->config['permkeycol'] . ' = ?', $vars['permkey']);
                break;

            case 'permkeyIn':
                $this->_sanityRun_acl('permkey_noempty', $vars);
                if(empty($vars['permkey'])){$vars['permkey'] = 'NULL';}
                $this->dataQuery->where($this->config['permkeycol'] . ' IN(?)', $vars['permkey']);
                break;

            case 'permscopeGlobal':
                $this->dataQuery->where($this->config['permscopecol'] . ' = ' . ACL_PERMSCOPE_GLOBAL);
                break;

            case 'permvalAllow':
                $this->dataQuery->where($this->config['permvalcol'] . ' = 1');
                break;

            case 'permvalDeny':
                $this->dataQuery->where($this->config['permvalcol'] . ' = 0');
                break;

            case 'residEq':
                $this->_sanityRun_acl('resid_noempty', $vars);
                $this->dataQuery->where($this->config['residcol'] . ' = ?', $vars['resid']);
                break;

            case 'residGroupEq':
                $this->_sanityRun_acl('groupid_noempty', $vars);
                $this->dataQuery->where($this->config['residcol'] . ' = ?', $vars['groupid']);
                break;

            case 'residGroupIn':
                $this->_sanityRun_acl('groupid_noempty', $vars);
                $this->dataQuery->where($this->config['residcol'] . ' IN(?)', $vars['groupid']);
                break;

            case 'restypeGroup':
                $this->dataQuery->where($this->config['restypeidcol'] . ' = ?', ACL_RESTYPE_GROUP);
                break;

            case 'restypeEq':
                $this->dataQuery->where($this->config['restypeidcol'] . ' = ?', $vars['restypeid']);
                break;

            case 'roleContext':
                $this->_sanityRun_acl('rolecontext_noempty', $vars);
                $this->dataQuery->where($this->config['roleidcol'] . ' IS NOT NULL');

                switch($vars['rolecontext']){
                    case 'A': break;
                    case 'G':
                        $this->dataQuery->where($this->config['residcol'] . ' IS NULL');
                        break;
                    default:
                        throw new Exception(ERR_ACL_INVALIDROLECONTEXT);
                        break;
                }
                break;

            case 'ridEq':
                $this->_sanityRun_acl('roleid_noempty', $vars);
                $this->dataQuery->where($this->config['roleidcol'] . ' = ?', $vars['roleid']);
                break;

            case 'ridIn':
                $this->_sanityRun_acl('roleid_noempty', $vars);
                if(empty($vars['roleid'])){$vars['roleid'] = 'NULL';}
                $this->dataQuery->where($this->config['roleidcol'] . ' IN(?)', $vars['roleid']);
                break;

            case 'ridNull':
                $this->dataQuery->where($this->config['roleidcol'] . ' IS NULL');
                break;

            case 'operatoruidEq':
                $this->_sanityRun_acl('uid_noempty', $vars);
                $this->dataQuery->where($this->config['uidcol'] . ' = ?', $vars['operator_uid']);
                break;

            case 'supplicantuidEq':
                $this->_sanityRun_acl('uid_noempty', $vars);
                $this->dataQuery->where($this->config['uidcol'] . ' = ?', $vars['supplicant_uid']);
                break;

            case 'uidEq':
                $this->_sanityRun_acl('uid_noempty', $vars);
                $this->dataQuery->where($this->config['uidcol'] . ' = ?', $vars['uid']);
                break;

            case 'uidNotNull':
                $this->dataQuery->where($this->config['uidcol'] . ' IS NOT NULL');
                break;

            case 'uidNull':
                $this->dataQuery->where($this->config['uidcol'] . ' IS NULL');
                break;

            default:
                throw new Exception();
                break;
        }
    }

    private function _helper_setpermkey(&$vars){
        $vars['permkey'] = (isset($vars['permission_keys'])) ? $vars['permission_keys'] : $this->permission('tree', array('type' => 'a', 'permkey' => $vars['permkey']));
    }

    private function _helper_group_setquerytype($vars){
        if(isset($vars['groupid'])){
            $qt = 'id';
        }elseif(isset($vars['groupname'])){
            $qt = 'name';
        }else{
            throw new Exception(ERR_ACL_GROUP_NOIDENTSET);
        }

        return($qt);
    }

    private function _helper_gru_setquerytype($vars){
        if(isset($vars['groupid'])){
            $qt = 'g';
        }elseif(isset($vars['uid']) || isset($vars['supplicant_uid']) || isset($vars['operator_uid'])){
            $qt = 'u';
        }elseif(isset($vars['roleid'])){
            $qt = 'r';
        }else{
            throw new Exception(ERR_ACL_NO_GRU_IDSET);
        }

        return($qt);
    }

    private function _helper_gru_sanitygru_norole($qt, $vars){
        switch($qt){
            case 'g': $this->_sanityRun_acl('groupid_set', $vars); break;
            case 'u':
                if(isset($vars['uid'])){$this->_sanityRun_acl('uid_exists', $vars);}
                if(isset($vars['supplicant_uid'])){$this->_sanityRun_acl('supplicant_uid_exists', $vars);}
                if(isset($vars['operator_uid'])){$this->_sanityRun_acl('operator_uid_exists', $vars);}
                break;
            case 'r': throw new Exception(ERR_ACL_ROLENEVERHAS); break;
        }
    }

    private function _permission_tree($permission = null, $direction = 'd'){
        switch(strtolower($direction)){
            case 'd':
                $keys = $this->db->fetchAll($this->db->select()->from('acl_permissions', 'permkey')->where('parentpermkey = ?', $permission));
                if(count($keys) > 0){
                    $children = array();
                    for($x = 0, $c = count($keys); $x < $c; $x++){
                        $treeret = $this->_permission_tree($keys[$x]['permkey'], 'd');
                        if(is_array($treeret)){$children[$keys[$x]['permkey']] = $treeret;}else{$children[$keys[$x]['permkey']] = 1;}
                    }
                    return($children);
                }else{
                    return($permission);
                }
                break;
            case 'a':
                $key = $this->db->fetchOne($this->db->select()->from('acl_permissions', 'parentpermkey')->where('permkey = ?', $permission));
                if($key != NULL){
                    return($this->_permission_tree($key, 'a') . ',' . $permission);
                }else{
                    return($permission);
                }
                break;
        }
    }

    protected function _sanityRun_acl($sanitycheck, &$vars, $qt = null){
        switch($sanitycheck){
            case 'group_qt_exists':
                $this->_sanityRun_acl('group_qt_noempty', $vars, $qt);
                if(!$this->group('exists', $vars)){throw new Exception(ERR_ACL_GROUPIDENTNOEXISTS);}
                break;
            case 'group_qt_set':
                if(!$this->sanityCheck('group' . $qt, 'set', $vars)){throw new Exception(ERR_ACL_NOGROUPIDENT);}
                switch($qt){
                    case 'id': $vars['groupid'] = settype($vars['groupid'], 'integer');
                    case 'name': $vars['groupname'] = settype($vars['groupname'], 'string');
                }
                break;
            case 'group_qt_noempty':
                $this->_sanityRun_acl('group_qt_set', $vars, $qt);
                if($this->sanityCheck('group' . $qt, 'empty', $vars)){throw new Exception(ERR_ACL_NOGROUPIDENT);}
                break;
            case 'group_name_preexist':
                $this->_sanityRun_acl('group_qt_set', $vars, 'name');
                if($this->group('exists', $vars)){throw new Exception(ERR_ACL_GROUPNAMEINUSE);}
                break;
            case 'permkey_set':
                if(!$this->sanityCheck('permkey', 'set', $vars)){throw new Exception(ERR_ACL_NOPERMKEY);}
                $vars['permkey'] = settype($vars['permkey'], 'string');
                break;
            case 'permkey_noempty':
                if($this->sanityCheck('permkey', 'empty', $vars)){throw new Exception(ERR_ACL_NOPERMKEY);}
                break;
            case 'permscope_set':
                if(!$this->sanityCheck('permscope', 'set', $vars)){throw new Exception(ERR_ACL_NOPERMKEY);}
                $vars['permscope'] = settype($vars['permscope'], 'integer');
                break;
            case 'permscope_noempty':
                if($this->sanityCheck('permscope', 'empty', $vars)){throw new Exception(ERR_ACL_NOPERMKEY);}
                break;
            case 'resid_exists':
                $this->_sanityRun_acl('resid_noempty', $vars);
                if(!$this->resource('exists', $vars)){throw new Exception(ERR_ACL_RESIDNOEXISTS);}
                break;
            case 'resid_set':
                if(!$this->sanityCheck('resid', 'set', $vars)){throw new Exception(ERR_ACL_NORESID);}
                $vars['resid'] = settype($vars['resid'], 'integer');
                break;
            case 'resid_noempty':
                if($this->sanityCheck('resid', 'empty', $vars)){throw new Exception(ERR_ACL_NORESID);}
                break;
            case 'roleid_exists':
                $this->_sanityRun_acl('roleid_noempty', $vars);
                if(!$this->role('exists', $vars)){throw new Exception(ERR_ACL_ROLEIDNOEXISTS);}
                break;
            case 'roleid_set':
                if(!$this->sanityCheck('roleid', 'set', $vars)){throw new Exception(ERR_ACL_NOROLEID);}
                $vars['roleid'] = settype($vars['roleid'], 'integer');
                break;
            case 'roleid_noempty':
                if($this->sanityCheck('roleid', 'empty', $vars)){throw new Exception(ERR_ACL_NOROLEID);}
                break;
            case 'rolecontext_noempty':
                if($this->sanityCheck('rolecontext', 'empty', $vars)){throw new Exception(ERR_ACL_NOROLECONTEXT);}
                break;

            // -- Switching for type for common uid operations
            case 'uid_set':
            case 'uid_noempty':
            case 'uid_exists':
                $type = '';
                break;
            case 'operator_uid_set':
            case 'operator_uid_noempty':
            case 'operator_uid_exists':
                $type = 'operator_';
                break;
            case 'supplicant_uid_set':
            case 'supplicant_uid_noempty':
            case 'supplicant_uid_exists':
                $type = 'supplicant_';
                break;
        }
        switch($sanitycheck){
            // -- Switching for type for common uid operations
            case 'uid_set':
            case 'operator_uid_set':
            case 'supplicant_uid_set':
                if(!$this->sanityCheck($type . 'uid', 'set', $vars)){throw new Exception(ERR_ACL_NOUID);}
                $vars['uid'] = settype($vars[$type . 'uid'], 'integer');
                break;
            case 'uid_noempty':
            case 'operator_uid_noempty':
            case 'supplicant_uid_noempty':
                $this->_sanityRun_acl($type . 'uid_set', $vars);
                if($this->sanityCheck($type . 'uid', 'empty', $vars)){throw new Exception(ERR_ACL_NOUID);}
                break;
            case 'uid_exists':
            case 'operator_uid_exists':
            case 'supplicant_uid_exists':
                $this->_sanityRun_acl($type . 'uid_noempty', $vars);
                if(!$this->user('exists', $vars)){throw new Exception(ERR_ACL_USERNOEXISTS);}
                break;
        }
    }

    private function _helper_resowneroverride($qt, $vars){ // Set operator uid as uid
        if($qt == 'u'){
            if($vars['uid'] == $this->resource('ownerid', $vars)){return(1);}
        }
    }

    private function _helper_set_op_uasu($vars){ // Set operator uid as uid
        $uid = $vars['operator_uid'];
        unset($vars['operator_uid'], $vars['supplicant_uid']);
        $vars['uid'] = $uid;
        return($vars);
    }

    private function _helper_set_sup_uasu($vars){ // Set supplicant uid as uid
        $uid = $vars['operator_uid'];
        unset($vars['operator_uid'], $vars['supplicant_uid']);
        $vars['uid'] = $uid;
        return($vars);
    }

    public function check_permission_uaccess($permission = null, $uid = null){
        $permission_keys = $this->permission('tree', array('permkey' => $permission, 'type' => 'A'));

        $state = $this->permission('access', array('permkey' => $permission, 'uid' => $uid));

        if($state == 0){
            $groupid = $this->group('memberships', array('uid' => $uid));
            if(!empty($groupid)){
                $state = $this->permission('access', array('permkey' => $permission, 'permission_keys' => $permission_keys, 'groupid' => $groupid));
            }
        }

        if($state == 0){
            $roleid = $this->role('memberships', array('uid' => $uid, 'rolecontext' => 'A'));
            if(!empty($roleid)){
                $state = $this->permission('access', array('permkey' => $permission, 'permission_keys' => $permission_keys, 'roleid' => $roleid));
            }
        }

        $state = ($state) ? 1 : 0;

        return($state);
    }

    public function check_resource_uaccess($resid, $uid = null){
        $uaccess_permission = $this->resource('access', array('resid' => $resid, 'uid' => $uid));
        $user_gmemberships = $this->group('memberships', array('uid' => $uid));
        $gaccess_permission = $this->resource('access', array('groupid' => $user_gmemberships));

        $final_permission = ($uaccess_permission || $gaccess_permission) ? 1 : 0;

        return($final_permission);
    }

    public function check_resource_uperm($resid = null, $uid = null, $permkey = null){
        $global_perm = $this->permission('access', array('permkey' => $permkey, 'uid' => $uid));
        if($global_perm){return(1);}

        $permission_keys = $this->permission('tree', array('permkey' => $permkey, 'type' => 'A'));

        $state = $this->resource('perm', array('resid' => $resid, 'permkey' => $permkey, 'permission_keys' => $permission_keys, 'uid' => $uid));

        if($state == 0){
            $user_gmemberships = $this->group('umemberships', array('supplicant_uid' => $uid));
            $temp_state = $this->resource('gperm', array('resid' => $resid, 'permkey' => $permkey, 'permission_keys' => $permission_keys, 'uid' => $uid));
        }

        return($state);
    }

    private function group($action = null, $vars = array()){
        // Add: change user role
        // Add: Delete group
        switch($action){
            case 'create':
                $this->_sanityRun_acl('groupname_preexist', $vars);
                $this->check_permission_uaccess('group_create', $vars['uid']);

                try{
                    $this->db->beginTransaction();
                    $this->db->insert($this->config['table_groups'], array('creatoruid' => $vars['uid'], 'name' => $vars['groupname']));
                    $groupid = $this->db->lastInsertId();
                    $this->role('grant', array('context' => 'group', 'operator_uid' => $vars['uid'], 'supplicant_uid' => $vars['uid'], 'roleid' => ACL_ROLE_GROUP_OWNER, 'resid' => $groupid));
                    $this->db->commit();
                }catch(Exception $e){
                    $this->db->rollBack();
                    throw new Exception(ERR_ACL_GROUP_CREATE_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }

                return($groupid);
                break; // Create group
            case 'deactivate':
                $this->_sanityRun_acl('groupid_exists', $vars);
                $this->check_resource_uperm($vars['groupid'], $vars['uid'], 'group_deactivate');

                try{
                    $this->db->update($this->config['table_groups'], array('active' => 0), 'groupid = ' . $this->db->quote($vars['groupid']));
                }catch(Exception $e){
                    throw new Exception(ERR_ACL_GROUP_DEACTIVATE_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }
                break; // Deactivate group
            case 'exists':
                $qt = $this->_helper_group_setquerytype($vars);

                $this->_sanityRun_acl('group' . $qt . '_set', $vars);

                $this->_buildQuery('group_exists_' . $qt . 'check', $vars);
                $groupcheckresult = $this->db->fetchOne($this->dataQuery->__toString());

                return($groupcheckresult);
                break; // Group exists
            case 'getid':
                $this->_sanityRun_acl('groupname_set', $vars);

                $this->_buildQuery('group_getid_name', $vars);
                $groupid = $this->db->fetchOne($this->dataQuery->__toString());

                return($groupid);
                break; // Get group id from name
            case 'umember':
                $this->_sanityRun_acl('groupid_exists', $vars);
                $this->_sanityRun_acl('supplicant_uid_exists', $vars);

                $this->_buildQuery('group_member', $vars);
                $member = $this->db->fetchOne($this->dataQuery->__toString());

                return($member);
                break; // Is user member of
            case 'umemberships':
                $this->_sanityRun_acl('supplicant_uid_exists', $vars);

                $this->_buildQuery('group_memberships', $vars);
                $group_list = $this->db->fetchAll($this->dataQuery->__toString());

                $clean_return = array();
                foreach($group_list as $k => $v){
                    if(!empty($v[$this->config['groupidcol']])){$clean_return[] = $v[$this->config['groupidcol']];}
                }

                return($clean_return);
                break; // Groups that user is member of
            case 'uadd':
                $this->_sanityRun_acl('groupid_set', $vars);
                $this->_sanityRun_acl('operator_uid_exists', $vars);
                $this->_sanityRun_acl('supplicant_uid_exists', $vars);

                $this->check_resource_uperm($vars['groupid'], $vars['operator_uid'], 'group_useradd');

                try{
                    $this->db->insert($this->config['table_acl_mappermissions'], array('roleid' => ACL_ROLE_GROUP_MEMBER, 'uid' => $vars['supplicant_uid'], 'restypeid' => ACL_RESTYPE_GROUP, 'resid' => $vars['groupid'], 'permscope' => ACL_PERMSCOPE_GROUP));
                }catch(Exception $e){
                    throw new Exception(ERR_ACL_GROUP_USERADD_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }
                break; // Add a user to group
            case 'urem':
                $this->_sanityRun_acl('groupid_set', $vars);
                $this->_sanityRun_acl('operator_uid_noempty', $vars);
                $this->_sanityRun_acl('supplicant_uid_noempty', $vars);

                $this->check_resource_uperm($vars['groupid'], $vars['operator_uid'], 'group_userrem');

                try{ // Remove user from group list
                    $this->db->delete($this->config['table_acl_mappermissions'], array('uid = ' . $this->db->quote($vars['supplicant_uid']), 'restypeid = ' . ACL_RESTYPE_GROUP, 'resid = ' . $this->db->quote($vars['groupid']), 'permscope = ' . ACL_PERMSCOPE_GROUP));
                }catch(Exception $e){
                    throw new Exception(ERR_ACL_GROUP_USERREM_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }
                break; // Remove a user from group
            default: return(0); break;
        }
    }

    private function permission($action = null, $vars = array()){
        $this->_sanityRun_acl('permkey_noempty', $vars);

        switch($action){
            case 'access':
                $qt = $this->_helper_gru_setquerytype($vars);

                switch($qt){
                    case 'g': $this->_sanityRun_acl('groupid_set', $vars); break;
                    case 'r': $this->_sanityRun_acl('roleid_noempty', $vars); break;
                    case 'u': $this->_sanityRun_acl('uid_noempty', $vars); break;
                }

                $state = 0;
                $this->_helper_setpermkey($vars);

                $this->_buildQuery('permission_' . $qt . 'access_deny', $vars);
                $state = ($this->db->fetchOne($this->dataQuery->__toString())) ? 2 : 0;

                if($state != 2){
                    $this->_buildQuery('permission_' . $qt . 'access_allow', $vars);
                    $state = $this->db->fetchOne($this->dataQuery->__toString());
                }

                return($state);
                break; // Have permission globally
            case 'exists':
                $this->_buildQuery('permission_exists', $vars);
                $checkresult = $this->db->fetchOne($this->dataQuery->__toString());

                return($checkresult);
                break; // Check permkey exists
            case 'list':
                $qt = $this->_helper_gru_setquerytype($vars);
                switch($qt){
                    case 'g': $ct = 'group'; break;
                    case 'r': $ct = 'role'; break;
                    case 'u': $ct = 'u'; break;
                }

                $clean_return = array(0 => array(), 1 => array());

                $this->_buildQuery('permission_' . $qt . 'list_deny', $vars);
                $list = $this->db->fetchAll($this->dataQuery->__toString());

                foreach($list as $k => $v){
                    if(!empty($v[$this->config[$ct . 'idcol']])){$clean_return[0][] = $v[$this->config[$ct . 'idcol']];}
                }

                $this->_buildQuery('permission_' . $qt . 'list_allow', $vars);
                $list = $this->db->fetchAll($this->dataQuery->__toString());

                foreach($list as $k => $v){
                    if(!empty($v[$this->config[$ct . 'idcol']])){$clean_return[1][] = $v[$this->config[$ct . 'idcol']];}
                }

                return($clean_return);
                break; // List that have this permkey
            case 'tree':
                switch(strtolower($vars['type'])){
                    case 'd':
                        $type = 'd'; break;
                    case 'a':
                    default:
                        $type = 'a'; break;
                }

                $treedata = $this->_permission_tree($vars['permkey'], $type);

                switch($type){
                    case 'a': return(explode(',', $treedata)); break;
                    case 'd': return($treedata); break;
                }

                break; // Get tree
            default: return(0); break;
        }
    }

    private function resource($action = null, $vars = array()){
        $this->_sanityRun_acl('resid_noempty', $vars);
        // Add: chown // Change owner of resource

        switch($action){
            case 'access':
                $qt = $this->_helper_gru_setquerytype($vars);
                $this->_helper_gru_sanitygru_norole($qt, $vars);
                if($this->_helper_resowneroverride($qt, $vars)){return(1);}

                $this->_buildQuery('resource_' . $qt . 'access', $vars);
                $access_permission = $this->db->fetchOne($this->dataQuery->__toString());

                return($access_permission);
                break; // Have access
            case 'exists':
                $this->_buildQuery('resource_exists', $vars);
                $resourcecheckresult = $this->db->fetchOne($this->dataQuery->__toString());
                return($resourcecheckresult);
                break; // Resource exists
            case 'find':
                $qt = $this->_helper_gru_setquerytype($vars);
                $clean_return = array();

                $this->_buildQuery('resource_' . $qt . 'find', $vars);
                $list = $this->db->fetchAll($this->dataQuery->__toString());

                foreach($list as $k => $v){
                    if(!empty($v[$this->config['residcol']])){$clean_return[] = $v[$this->config['residcol']];}
                }

                return($clean_return);
                break; // Resources this user or group has access to
            case 'info':
                $this->_buildQuery('resource_info', $vars);
                $resourceinfo = $this->db->fetchAll($this->dataQuery->__toString());
                return($resourceinfo[0]);
                break; // Resource info
            case 'list':
                $qt = $this->_helper_gru_setquerytype($vars);
                switch($qt){
                    case 'g': $ct = 'group'; break;
                    case 'r': throw new Exception(ERR_ACL_ROLENEVERHAS); break;
                    case 'u': $ct = 'u'; break;
                }

                $clean_return = array(0 => array(), 1 => array());

                $this->_buildQuery('resource_' . $qt . 'list_deny', $vars);
                $list = $this->db->fetchAll($this->dataQuery->__toString());

                foreach($list as $k => $v){
                    if(!empty($v[$this->config[$ct . 'idcol']])){$clean_return[0][] = $v[$this->config[$ct . 'idcol']];}
                }

                $this->_buildQuery('resource_' . $qt . 'list_allow', $vars);
                $list = $this->db->fetchAll($this->dataQuery->__toString());

                foreach($list as $k => $v){
                    if(!empty($v[$this->config[$ct . 'idcol']])){$clean_return[1][] = $v[$this->config[$ct . 'idcol']];}
                }

                return($clean_return);
                break; // List that have access to this resource
            case 'ownerid':
                $this->_buildQuery('resource_ownerid', array('resid' => $vars['resid']));
                $ownerid = $this->db->fetchOne($this->dataQuery->__toString());
                return($ownerid);
                break; // Get owner id
            case 'perm':
                $qt = $this->_helper_gru_setquerytype($vars);
                if($this->_helper_resowneroverride($qt, $vars)){return(1);}
                $this->_sanityRun_acl('permkey_noempty', $vars);

                $state = 0;
                $this->_helper_setpermkey($vars);

                $this->_buildQuery('resource_' . $qt . 'perm_allow', $vars);
                $state = $this->db->fetchOne($this->dataQuery->__toString());

                if($state){
                    $this->_buildQuery('resource_' . $qt . 'perm_deny', $vars);
                    $state += $this->db->fetchOne($this->dataQuery->__toString());
                }

                return($state);
                break; // Does have specific permission on resource
            case 'permgrant':
                $qt = $this->_helper_gru_setquerytype($vars);
                $this->_helper_gru_sanitygru_norole($qt, $vars);

                $this->_sanityRun_acl('permkey_noempty', $vars);
                $this->_sanityRun_acl('resid_exist', $vars);

                $doesuidhaveperm = $this->check_resource_uperm($vars['resid'], $vars['operator_uid'], $vars['permkey']);
                if(!$doesuidhaveperm){throw new Exception(ERR_ACL_RESOURCE_GRANT_CANTWHATYOUDONTHAVE);}

                $resinfo = $this->resource('info', $vars);
                $insertdata = array('permval' => 1, 'permkey' => $vars['permkey'], 'resid' => $vars['resid'], 'restypeid' => $resinfo[$this->config['restypeidcol']], 'permscope' => ACL_PERMSCOPE_RESOURCE);

                switch($qt){
                    case 'g': $insertdata['groupid'] = $vars['groupid']; break;
                    case 'u': $insertdata['uid'] = $vars['supplicant_uid']; break;
                }

                try{
                    $this->db->insert($this->config['table_acl_mappermissions'], $insertdata);
                }catch(Exception $e){
                    throw new Exception(ERR_ACL_RESOURCE_PERMGRANT_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }
                break; // Grant permission for resource
            case 'permrevoke':
                $qt = $this->_helper_gru_setquerytype($vars);
                $this->_helper_gru_sanitygru_norole($qt, $vars);

                $this->_sanityRun_acl('permkey_noempty', $vars);
                $this->_sanityRun_acl('resid_exist', $vars);

                $doesuidhaveperm = $this->check_resource_uperm($vars['resid'], $vars['operator_uid'], $vars['permkey']);
                if(!$doesuidhaveperm){throw new Exception(ERR_ACL_RESOURCE_REVOKE_CANTWHATYOUDONTHAVE);}

                switch($qt){
                    case 'g': $doesidhaveperm = $this->resource('perm', $vars); break;
                    case 'u': $doesidhaveperm = $this->resource('perm', $this->_helper_set_op_uasu($vars)); break;
                }

                if(!$doesidhaveperm){throw new Exception(ERR_ACL_RESOURCE_REVOKE_IDNOTHAS);}

                $resinfo = $this->resource('info', $vars);
                $deletewhere = array('permkey = ' . $this->db->quote($vars['permkey']), 'resid = ' . $this->db->quote($vars['groupid']), 'permscope = ' . ACL_PERMSCOPE_RESOURCE);

                switch($qt){
                    case 'g': $deletewhere[] = 'groupid = ' . $this->db->quote($vars['groupid']); break;
                    case 'u': $deletewhere[] = 'uid = ' . $this->db->quote($vars['supplicant_uid']); break;
                }

                try{
                    $this->db->delete($this->config['table_acl_mappermissions'], $deletewhere);
                }catch(Exception $e){
                    throw new Exception(ERR_ACL_RESOURCE_REVOKE_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }
                break; // Revoke permission for resource
            default: return(0); break;
        }
    }

    private function role($action = null, $vars = array()){
        switch($action){
            case 'create':
                $this->_sanityRun_acl('rolename_preexist', $vars);
                $this->_sanityRun_acl('uid_exists', $vars);

                $this->check_permission_uaccess('admin_role_create', $vars['uid']);

                try{
                    $this->db->insert($this->config['table_roles'], array('name' => $vars['rolename']));
                    $roleid = $this->db->lastInsertId();
                }catch(Exception $e){
                    $this->db->rollBack();
                    throw new Exception(ERR_ACL_ROLE_CREATE_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }

                return($roleid);
                break; // Create a role
            case 'delete':
                $this->_sanityRun_acl('roleid_exists', $vars);
                $this->_sanityRun_acl('uid_exists', $vars);

                $this->check_permission_uaccess('admin_role_delete', $vars['uid']);

                try{
                    $this->db->delete($this->config['table_roles'], array('roleid = ' . $vars['roleid']));
                    // Don't forget the cleanup in here to remove all assigned to this role
                }catch(Exception $e){
                    $this->db->rollBack();
                    throw new Exception(ERR_ACL_ROLE_DELETE_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }
                break; // Delete a role
            case 'exists':
                $this->_sanityRun_acl('roleid_noempty', $vars);

                $this->_buildQuery('role_exists_idcheck', $vars);
                $checkresult = $this->db->fetchOne($this->dataQuery->__toString());
                return($checkresult);
                break; // Does role exist
            case 'grant':
                $this->_sanityRun_acl('roleid_exist', $vars);

                $qt = $this->_helper_gru_setquerytype($vars);
                $insertdata = array('roleid' => $vars['roleid'], 'permval' => 1);

                switch($qt){
                    case 'g':
                        $this->_sanityRun_acl('groupid_set', $vars);
                        $ct = 'group';
                        $insertdata['groupid'] = $vars['groupid'];
                        break;
                    case 'r': throw new Exception(ERR_ACL_ROLENEVERHAS); break;
                    case 'u':
                        $this->_sanityRun_acl('supplicant_uid_exists', $vars);
                        $this->_sanityRun_acl('operator_uid_exists', $vars);
                        $ct = 'u';
                        $insertdata['uid'] = $vars['supplicant_uid'];
                        break;
                }

                switch($vars['context']){
                    case 'global':
                        $allow = $this->check_permission_uaccess('admin_role_grant', $vars['operator_uid']);
                        $insertdata['permscope'] = ACL_PERMSCOPE_GLOBAL;
                        break;
                    case 'group':
                        $allow = 1;
                        // Does this allow check do what it needs to?
                        //$allow = $this->check_resource_uperm($vars['resid'], $vars['supplicant'], $vars['permkey']);
                        $insertdata = array_merge($insertdata, array('permscope' => ACL_PERMSCOPE_GROUP, 'restypeid' => ACL_RESTYPE_GROUP, 'resid' => $vars['resid']));
                        break;
                    case 'resource':
                        $allow = 1;
                        // Does this allow check do what it needs to?
                        //$allow = $this->check_resource_uperm($vars['resid'], $vars['supplicant'], $vars['permkey']);
                        $insertdata = array_merge($insertdata, array('permscope' => ACL_PERMSCOPE_RESOURCE, 'restypeid' => $vars['restypeid'], 'resid' => $vars['resid']));
                        break;
                    default: throw new exception();
                }

                if(!$allow){throw new Exception(ERR_ACL_ROLE_GRANT_NOTALLOWED);}

                // Do we need this check?
                //$doesuidtoactionhaverole = $this->role('member', $vars);

                try{
                    $this->db->insert($this->config['table_acl_mappermissions'], $insertdata);
                }catch(Exception $e){
                    throw new Exception(ERR_ACL_ROLE_GRANT_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }
                break; // Add to role
            case 'list':
                $qt = $this->_helper_gru_setquerytype($vars);
                switch($qt){
                    case 'g': $ct = 'group'; break;
                    case 'r': throw new Exception(ERR_ACL_ROLENEVERHAS); break;
                    case 'u': $ct = 'u'; break;
                }

                $clean_return = array(0 => array(), 1 => array());

                $this->_buildQuery('role_' . $qt . 'list_deny', $vars);
                $list = $this->db->fetchAll($this->dataQuery->__toString());

                foreach($list as $k => $v){
                    if(!empty($v[$this->config[$ct . 'idcol']])){$clean_return[0][] = $v[$this->config[$ct . 'idcol']];}
                }

                $this->_buildQuery('role_' . $qt . 'list_allow', $vars);
                $list = $this->db->fetchAll($this->dataQuery->__toString());

                foreach($list as $k => $v){
                    if(!empty($v[$this->config[$ct . 'idcol']])){$clean_return[1][] = $v[$this->config[$ct . 'idcol']];}
                }

                return($clean_return);
                break; // Show attached to role
            case 'member':
                $qt = $this->_helper_gru_setquerytype($vars);
                $this->_helper_gru_sanitygru_norole($qt, $vars);

                $this->_buildQuery('role_' . $qt . 'member', $vars);
                $member = $this->db->fetchOne($this->dataQuery->__toString());

                return($member);
                break; // Is member of
            case 'memberships':
                $qt = $this->_helper_gru_setquerytype($vars);
                $this->_helper_gru_sanitygru_norole($qt, $vars);

                $vars['rolecontext'] = 'A';

                $this->_buildQuery('role_' . $qt . 'memberships', $vars);
                $role_list = $this->db->fetchAll($this->dataQuery->__toString());

                $clean_return = array();
                foreach($role_list as $k => $v){
                    if(!empty($v[$this->config['roleidcol']])){$clean_return[] = $v[$this->config['roleidcol']];}
                }

                return($clean_return);
                break; // Roles that is member of
            case 'permadd':
                $this->_sanityRun_acl('permscope_set', $vars);
                $this->_sanityRun_acl('roleid_exist', $vars);
                $this->_sanityRun_acl('uid_exists', $vars);

                $this->check_permission_uaccess('admin_role_permadd', $vars['uid']);

                try{
                    $this->db->insert($this->config['table_acl_mappermissions'], array('roleid' => $vars['roleid'], 'permscope' => $vars['permscope'], 'permval' => 1));
                }catch(Exception $e){
                    $this->db->rollBack();
                    throw new Exception(ERR_ROLE_PERMADD_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }
                break; // Add permission
            case 'permlist':
                $clean_return = array(0 => array(), 1 => array());

                $this->_buildQuery('role_permlist_deny', $vars);
                $list = $this->db->fetchAll($this->dataQuery->__toString());

                foreach($list as $k => $v){
                    if(!empty($v[$this->config['permkeycol']])){$clean_return[0][] = $v[$this->config['permkeycol']];}
                }

                $this->_buildQuery('role_permlist_allow', $vars);
                $list = $this->db->fetchAll($this->dataQuery->__toString());

                foreach($list as $k => $v){
                    if(!empty($v[$this->config['permkeycol']])){$clean_return[1][] = $v[$this->config['permkeycol']];}
                }

                return($clean_return);
                break; // List permissions attached to role
            case 'permrem':
                $this->_sanityRun_acl('roleid_exist', $vars);
                $this->_sanityRun_acl('uid_exists', $vars);

                $this->check_permission_uaccess('admin_role_permrem', $vars['uid']);

                try{
                    $this->db->delete($this->config['table_acl_mappermissions'], array('roleid = ' . $this->db->quote($vars['roleid']), 'permscope = ' . $this->db->quote($vars['permscope']), 'permval = 1'));
                }catch(Exception $e){
                    $this->db->rollBack();
                    throw new Exception(ERR_ROLE_PERMREM_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }
                break; // Remove permission
            case 'revoke':
                $qt = $this->_helper_gru_setquerytype($vars);

                switch($qt){
                    case 'g':
                        $this->_sanityRun_acl('groupid_set', $vars);
                        break;
                    case 'r': throw new Exception(ERR_ACL_ROLENEVERHAS); break;
                    case 'u':
                        $this->_sanityRun_acl('supplicant_uid_exists', $vars);
                        $this->_sanityRun_acl('operator_uid_exists', $vars);
                        break;
                }

                //$this->_sanityRun_acl('permkey_noempty', $vars);
                $this->check_permission_uaccess('admin_role_revoke', $vars['operator_uid']);

                $deletedata = array('uid = ' . $this->db->quote($vars['supplicant_uid']), 'roleid = ' . $vars['roleid'], 'permval = 1');

                switch($vars['context']){
                    case 'global':
                        $allow = $this->check_permission_uaccess('role_revoke', $vars['operator_uid']);
                        $deletedata['permscope'] = ACL_PERMSCOPE_GLOBAL;
                        break;
                    case 'group':
                        $this->_sanityRun_acl('resid_exist', $vars);
                        $allow = $this->check_resource_uperm($vars['resid'], $vars['supplicant_uid'], $vars['permkey']);
                        $deletedata = array_merge($deletedata, array('permscope = ' . ACL_PERMSCOPE_GROUP, 'restypeid = ' . ACL_RESTYPE_GROUP, 'resid = ' . $vars['resid']));
                        break;
                    case 'resource':
                        $this->_sanityRun_acl('resid_exist', $vars);
                        $allow = $this->check_resource_uperm($vars['resid'], $vars['supplicant_uid'], $vars['permkey']);
                        $deletedata = array_merge($deletedata, array('permscope = ' . ACL_PERMSCOPE_RESOURCE, 'restypeid = ' . $vars['restypeid'], 'resid = ' . $vars['resid']));
                        break;
                    default: throw new exception();
                }

                $doesuidtoactionhaverole = $this->role('member', $this->_helper_set_sup_uasu($vars));
                if(!$doesuidtoactionhaverole){throw new Exception(ERR_ACL_ROLE_REVOKE_DOESNOTHAVE);}

                try{
                    $this->db->delete($this->config['table_acl_mappermissions'], $deletedata);
                }catch(Exception $e){
                    throw new Exception(ERR_ACL_RESOURCE_REVOKE_FAILED . ' ' . ERR_DBRETURN . ': ' . $e);
                }
                break; // Remove from role
            default: return(0); break;
        }
    }

    private function user($action = null, $vars = array()){
        switch($action){
            case 'exists':
                $this->_sanityRun_acl('uid_set', $vars);
                $this->_buildQuery('user_exists_idcheck', $vars);
                return($this->db->fetchOne($this->dataQuery->__toString()));
                break;
            default: return(0); break;
        }
    }

    // Public Interfaces
    public function group_create($groupname, $uid){return($this->group('create', array('groupname' => $groupname, 'uid' => $uid)));} // groupid // Create group
    public function group_deactivate($groupid, $uid){return($this->group('deactivate', array('groupid' => $groupid, 'uid' => $uid)));} // Deactivate group
    public function group_exists($groupid){return($this->group('exists', array('groupid' => $groupid)));} // Group exists
    public function group_getid($groupname){return($this->group('getid', array('groupname' => $groupname)));} // groupid // Get group id from name
    public function group_umember($groupid, $supplicant_uid){return($this->group('umember', array('groupid' => $groupid, 'supplicant_uid' => $supplicant_uid)));} // Is user member of
    public function group_umemberships($supplicant_uid){return($this->group('umemberships', array('supplicant_uid' => $supplicant_uid)));} // array groupids // Groups that user is member of
    public function group_uadd($groupid, $supplicant_uid, $operator_uid){return($this->group('uadd', array('groupid' => $groupid, 'supplicant_uid' => $supplicant_uid, 'operator_uid' => $operator_uid)));} // Add a user to group
    public function group_urem($groupid, $supplicant_uid, $operator_uid){return($this->group('urem', array('groupid' => $groupid, 'supplicant_uid' => $supplicant_uid, 'operator_uid' => $operator_uid)));} // Remove a user from group
    public function permission_exists($permkey){return($this->permission('exists', array('permkey' => $permkey)));} // bool checkresult // Check permkey exists
    public function permission_gaccess($permkey, $groupid){return($this->permission('access', array('permkey' => $permkey, 'groupid' => $groupid)));} // bool state // Have permission globally
    public function permission_glist($permkey){return($this->permission('list', array('permkey' => $permkey, 'groupid' => 1)));} // array allow/deny group // List that have this permkey
    public function permission_raccess($permkey, $roleid){return($this->permission('access', array('permkey' => $permkey, 'roleid' => $roleid)));} // bool state // Have permission globally
    public function permission_rlist($permkey){return($this->permission('list', array('permkey' => $permkey, 'roleid' => 1)));} // array allow/deny role // List that have this permkey
    public function permission_tree($permkey, $type = null){return($this->permission('tree', array('permkey' => $permkey, 'type' => $type)));} // array keys // Get tree
    public function permission_uaccess($permkey, $uid){return($this->permission('access', array('permkey' => $permkey, 'uid' => $uid)));} // bool state // Have permission globally
    public function permission_ulist($permkey){return($this->permission('list', array('permkey' => $permkey, 'uid' => 1)));} // array allow/deny user // List that have this permkey
    public function resource_exists($resid){return($this->resource('exists', array('resid' => $resid)));} // bool exists // Resource exists
    public function resource_gaccess($groupid, $resid){return($this->resource('access', array('resid' => $resid, 'groupid' => $groupid)));} // bool allowed // Have access
    public function resource_gfind($groupid, $restypeid){return($this->resource('find', array('resid' => 999, 'restypeid' => $restypeid, 'groupid' => $groupid)));} // array resid // List all resources group has access to of type
    public function resource_glist($resid){return($this->resource('list', array('resid' => $resid, 'groupid' => 1)));} // array allow/deny // List that have this permkey
    public function resource_gperm($resid, $groupid, $permkey){return($this->resource('perm', array('resid' => $resid, 'groupid' => $groupid, 'permkey' => $permkey)));} // bool state // Does have specific permission on resource
    public function resource_gpermgrant($resid, $groupid, $permkey, $operator_uid){return($this->resource('permgrant', array('resid' => $resid, 'groupid' => $groupid, 'permkey' => $permkey, 'operator_uid' => $operator_uid)));} // Grant permission for resource
    public function resource_gpermrevoke($resid, $groupid, $permkey, $operator_uid){return($this->resource('permrevoke', array('resid' => $resid, 'groupid' => $groupid, 'permkey' => $permkey, 'operator_uid' => $operator_uid)));} // bool state // Revoke permission for resource
    public function resource_info($resid){return($this->resource('info', array('resid' => $resid)));} // array resinfo // Resource info
    public function resource_ownerid($resid){return($this->resource('ownerid', array('resid' => $resid)));} // ownerid // Get ownerid
    public function resource_uaccess($resid, $uid){return($this->resource('access', array('resid' => $resid, 'supplicant' => $uid, 'uid' => $uid)));} // bool allowed // Have access
    public function resource_ufind($uid, $restypeid){return($this->resource('find', array('resid' => 999, 'restypeid' => $restypeid, 'uid' => $uid)));} // array resid // List all resources user has access to of type
    public function resource_ulist($resid){return($this->resource('list', array('resid' => $resid, 'uid' => 1)));} // array allow/deny // List that have this permkey
    public function resource_uperm($resid, $uid, $permkey){return($this->resource('perm', array('resid' => $resid, 'uid' => $uid, 'permkey' => $permkey)));} // bool state // Does have specific permission on resource
    public function resource_upermgrant($resid, $permkey, $operator_uid, $supplicant_uid){return($this->resource('permgrant', array('resid' => $resid, 'permkey' => $permkey, 'operator_uid' => $operator_uid, 'supplicant_uid' => $supplicant_uid)));} // Grant permission for resource
    public function resource_upermrevoke($resid, $permkey, $operator_uid, $supplicant_uid){return($this->resource('premrevoke', array('resid' => $resid, 'permkey' => $permkey, 'operator_uid' => $operator_uid, 'supplicant_uid' => $supplicant_uid)));} // bool state // Revoke permission for resource
    public function role_create($rolename, $uid){return($this->role('create', array('rolename' => $rolename, 'uid' => $uid)));} // roleid // Create a role
    public function role_delete($roleid, $uid){return($this->role('delete', array('roleid' => $roleid, 'uid' => $uid)));} // Delete a role
    public function role_exists($roleid){return($this->role('exists', array('roleid' => $roleid)));} // bool state // Does role exist
    public function role_ggrant($roleid, $groupid, $operator_uid, $supplicant_uid){return($this->role('grant', array('roleid' => $roleid, 'groupid' => $groupid, 'context' => 'global', 'operator_uid' => $operator_uid, 'supplicant_uid' => $supplicant_uid)));} // Add to role
    public function role_glist($roleid){return($this->role('list', array('roleid' => $roleid, 'groupid' => 1)));} // array allow/deny // Show attached to role
    public function role_gmember($roleid, $groupid){return($this->role('member', array('roleid' => $roleid, 'groupid' => $groupid, 'context' => 'CONTEXTCONTEXTCONTEXT')));} // bool member // Is member of
    public function role_gmemberships($groupid){return($this->role('memberships', array('groupid' => $groupid)));} // array roleids // Roles that is member of
    public function role_grevoke($roleid, $groupid, $operator_uid, $supplicant_uid){return($this->role('revoke', array('roleid' => $roleid, 'groupid' => $groupid, 'context' => 'global', 'operator_uid' => $operator_uid, 'supplicant_uid' => $supplicant_uid)));} // Remove from role
    public function role_permadd($roleid, $permkey, $permscope, $uid){return($this->role('permadd', array('permkey' => $permkey, 'permscope' => $permscope, 'roleid' => $roleid, 'uid' => $uid)));} // Add permission to role
    public function role_permlist($roleid){return($this->role('permlist', array('roleid' => $roleid)));} // array allow/deny // List permissions attached to role
    public function role_permrem($roleid, $permkey, $permscope, $uid){return($this->role('permrem', array('roleid' => $roleid, 'permkey' => $permkey, 'permscope' => $permscope, 'uid' => $uid)));} // Remove permission
    public function role_ulist($roleid){return($this->role('list', array('roleid' => $roleid, 'uid' => 1)));} // array allow/deny // Show attached to role
    public function role_umember($roleid, $uid){return($this->role('member', array('roleid' => $roleid, 'uid' => $uid)));} // bool member // Is member of
    public function role_umemberships($uid){return($this->role('memberships', array('uid' => $uid)));} // array roleids // Roles that is member of
    public function role_ugrant($roleid, $operator_uid, $supplicant_uid){return($this->role('grant', array('roleid' => $roleid, 'operator_uid' => $operator_uid, 'supplicant_uid' => $supplicant_uid, 'context' => 'global')));} // Add to role
    public function role_urevoke($roleid, $operator_uid, $supplicant_uid){return($this->role('revoke', array('roleid' => $roleid, 'operator_uid' => $operator_uid, 'supplicant_uid' => $supplicant_uid, 'context' => 'global')));} // Remove from role
    public function user_exists($uid){return($this->user('exists', array('uid' => $uid)));}
}
