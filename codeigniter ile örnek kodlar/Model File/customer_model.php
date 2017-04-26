<?php

class Customer_model extends MY_Model
{

	function __construct()
	{
		parent::__construct();
	}

	function get_information($where)
	{
		if (isset($where['office_id']) && !empty($where['office_id']) && $where['office_id'] != 52)
			$where['office_id'] = $where['office_id'];
		else
			unset($where['office_id']);
            
		if ($where['customer_id'] < 2000000 || (isset($where['identity_no']) && !empty($where['identity_no'])))
			$table = "enigma_v2.customer_individual";
			
        $array = $this->db->get_where($table, $where)->row_array();
        
        $temp = $this->db->get_where("enigma_v2.customer_phones",array('customer_id' => $where['customer_id']))->result_array();
        $array['telefon'] = $temp[0]['phone'];
        
        return $array;
	}

	function get_detail_information($array = array(), $user_id = null)
	{
		if (!empty($array['customer_id']) && $array['bills'] = $this->db->get_where("vi_customer_bill", array('customer_id' => $array['customer_id'], 'user_id' => $user_id, 'status' => 'valid', 'report_status' => 'done'))->result_array())
		{
            foreach($array['bills'] as $key=>$bill)
            {
                $temp = $this->db->get_where("sys_user", array('id' => $bill['vendor_id']))->row_array();
                $array['bills'][$key]['vendor_name'] = $temp['name'];
            }
            
            return $array;
            
		} else
			return false;
	}
    
    function display_bill_detail($id)
    {
        if (!empty($id))
		{
			return $this->db->get_where("customer_bill", array('id' => $id), 1, 0)->row_array();         
		}
		else
			return false;
        
    }
    
    function display_bill_payments($id)
    {
        if (!empty($id))
		{
			$this->db->order_by('payment_no', 'asc');
            $array = $this->db->get_where("customer_payment", array('bill_id' =>$id))->result_array();         
            return $array;
            
		} else
			return null;
        
    }
    
    function display_notes($id)
    {
        if (!empty($id))
		{
            $array = $this->db->get_where("vi_notes", array('content_id' => $id))->result_array();         
            return $array;
		} else
			return null;
        
    }
    
    function display_files($bill_id)
    {
    	return $this->db->get_where("vi_files", array('content_id' => $bill_id))->result_array();
    }
    
    function add_note($post)
    {
        if (!empty($post))
		{
            $this->db->insert('notes', $post);         
            
		} else
			return null;
        
    }

	function get_product_by_bill($bill_id)
	{
		return $this->db->get_where("vi_customer_bill_product", array('bill_id' => $bill_id))->result_array();
	}
    
	function filter_customer($type = "individual", $only_count = false, $filter = null,$page = 1, $limit = 25)
	{
         
        $where = array();
		$where['type'] = $type;
		if ($filter != null)
		{
			// Office kontrolü
			if (isset($filter['office_id']) && !empty($filter['office_id']) && $filter['office_id'] !=52)
				$where['branch_id'] = $filter['office_id'];
			if (isset($filter['branch_id']) && !empty($filter['branch_id']))
				$where['branch_id'] = $filter['branch_id'];

			if (isset($filter['identity_no']) && !empty($filter['identity_no']))
				$where['identity_no'] = $filter['identity_no'];

			if (isset($filter['customer_id']) && !empty($filter['customer_id']))
				$where['customer_id'] = $filter['customer_id'];


			if (isset($filter['order']) && !empty($filter['order']))
			{
				list($column, $order_type) = explode("|", $filter['order']);
				$this->db->order_by($column, $order_type);
			} else
			{
				$this->db->order_by('customer_id', 'desc');
			}

			if (isset($filter['title']) && !empty($filter['title']))
            {
                $this->db->like('name',trim($filter['title']));
            }
            
			if (isset($filter['created_on_start']) && !empty($filter['created_on_start']) &&
				isset($filter['created_on_end']) && !empty($filter['created_on_end']))
			{
				$where['created_on >='] = $this->date_to_sql($filter['created_on_start']) .
					" 00:00:00";
				$where['created_on <='] = $this->date_to_sql($filter['created_on_end']) .
					" 23:59:59";
			} else
			{
				if (isset($filter['created_on_start']) && !empty($filter['created_on_start']))
					$where['created_on >='] = $this->date_to_sql($filter['created_on_start']) .
						" 00:00:00";

				if (isset($filter['created_on_end']) && !empty($filter['created_on_end']))
					$where['created_on <='] = $this->date_to_sql($filter['created_on_end']) .
						" 23:59:59";
			}
		} else
		{
			$this->db->order_by('customer_id', 'desc');
		}
        unset($where['type']);
		if ($only_count === true)
		{
			$this->db->select("customer_id");
			$this->db->where($where);
			$result = $this->db->get("customer_individual");
			return $result->num_rows();
		} else
		{
			$this->db->select("*");
			$this->db->where($where);

			$this->db->limit($limit, ($page - 1) * $limit);
			$result = $this->db->get("customer_individual");
			return $result->result_array();
		}
	}

	function get_information_firma($where = array())
	{
		$customer_id = $where['customer_id'];
		$this->db->select('id');
		$this->db->order_by('id', 'desc');
		if ($firma = $this->db->get_where('enigma_bot.firma', array('customer_id' => $customer_id))->
			row_array())
		{
			$firma_id = $firma['id'];
		} else
		{
			$this->db->insert('enigma_bot.firma', array(
				'customer_id' => $customer_id,
				'status' => 'pending',
				'created_on' => date('Y-m-d H:i:s')));
			$firma_id = $this->db->insert_id();
		}

		$info = array();
		$info['firma_id'] = $firma_id;
		$info['firma_detay'] = $this->get_information(array('customer_id' => $customer_id));
		$info['bilgi'] = $this->db->get_where('enigma_bot.firma_bilgi', array('firma_id' =>
				$firma_id))->row_array();
		$info['detay'] = $this->db->get_where('enigma_bot.firma_detay', array('firma_id' =>
				$firma_id))->row_array();
		$info['eski_unvanlar'] = $this->db->get_where('enigma_bot.firma_eski_unvanlar',
			array('firma_id' => $firma_id))->result_array();
		$info['eski_yonetim_ortak'] = $this->db->get_where('enigma_bot.firma_eski_yonetim_ortak',
			array('firma_id' => $firma_id))->result_array();
		$info['faaliyet'] = $this->db->get_where('enigma_bot.firma_faaliyet', array('firma_id' =>
				$firma_id))->result_array();
		$info['gazete_bilgileri'] = $this->db->get_where('enigma_bot.firma_gazete_bilgileri',
			array('firma_id' => $firma_id))->result_array();
		$info['nace_kodu'] = $this->db->get_where('enigma_bot.firma_nace_kodu', array('firma_id' =>
				$firma_id))->result_array();
		$info['subeler'] = $this->db->get_where('enigma_bot.firma_subeler', array('firma_id' =>
				$firma_id))->result_array();
		$info['ulke'] = $this->db->get_where('enigma_bot.firma_ulke', array('firma_id' =>
				$firma_id))->result_array();
		$info['yetkililer'] = $this->db->get_where('enigma_bot.firma_yetkililer', array
			('firma_id' => $firma_id))->result_array();
		$info['yonetim_kurulu_ortaklar'] = $this->db->get_where('enigma_bot.firma_yonetim_kurulu_ortaklar',
			array('firma_id' => $firma_id))->result_array();

		return $info;
	}

	function get_information_ito($customer_id)
	{
		$this->db->select('id');
		$this->db->order_by('id', 'desc');
		if ($ito = $this->db->get_where("enigma_bot.ito", array('customer_id' => $customer_id,
				'note' => 'İto'))->row_array())
		{
			return $ito['id'];
		}
		return false;
	}

	function kampanya_ekle($customer_id, $kampanya)
	{
		if ($kampanya == "tim")
		{
			$this->db->insert("enigma_v2.customer_credit_income", array(
				'customer_id' => $customer_id,
				'credit' => '240',
				'balance' => '240',
				'process_date' => date('Y-m-d H:i:s'),
				'deadline' => '2025-12-31 23:59:59',
				'description' => 'TİM Üyesi'));

			$this->db->update('enigma_v2.customer_corporation', array('member_tim' => 'yes'),
				array('customer_id' => $customer_id));

			return true;

		} elseif ($kampanya == "und")
		{
			$this->db->insert("enigma_v2.customer_credit_income", array(
				'customer_id' => $customer_id,
				'credit' => '260',
				'balance' => '260',
				'process_date' => date('Y-m-d H:i:s'),
				'deadline' => '2025-12-31 23:59:59',
				'description' => 'UND Üyesi'));

			$this->db->update('enigma_v2.customer_corporation', array('member_und' => 'yes',
					'contract_status' => 'valid'), array('customer_id' => $customer_id));

			return true;

		} elseif ($kampanya == "iso")
		{
			$this->db->update('enigma_v2.customer_corporation', array('member_iso' => 'yes'),
				array('customer_id' => $customer_id));

			return true;

		} elseif ($kampanya == "enka")
		{
			$this->db->insert("enigma_v2.customer_credit_income", array(
				'customer_id' => $customer_id,
				'credit' => '20',
				'balance' => '20',
				'process_date' => date('Y-m-d H:i:s'),
				'deadline' => '2025-12-31 23:59:59',
				'description' => 'Enka Projesi'));

			$this->db->update('enigma_v2.customer_corporation', array('member_enka' => 'yes',
					'contract_status' => 'valid'), array('customer_id' => $customer_id));

			return true;

		} else
		{
			return false;
		}
	}

}

?>
