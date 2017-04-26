<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Customer extends MY_Controller
{

	public $page_limit;

	function __construct()
	{
		parent::__construct();

		$this->load->model('customer_model');
		$this->load->model('address_model');
		$this->page_limit = 25;
	}
	
	public function index()
	{
		$this->smarty->assign('data', $this->data);
		$this->smarty->display('customer/index.tpl');
	}
	
	public function detail($customer_id = null)
	{
		if($customer_id == null)
			redirect('/');
		
		$get = $this->input->get(NULL, TRUE);
		if(is_array($get))
			$get = @array_map('trim', $get);
		
		$slot = isset($get['slot']) && !empty($get['slot']) ? $get['slot'] : 'summary';
		$detail_slots = array('summary', 'info');
		
		if(!in_array($slot, $detail_slots))
			redirect('/customer/detail/' . $customer_id . '?slot=summary');
		
		$this->data['slot'] = $slot;
		
		$this->data['customer'] = $this->customer_model->get_information(array('customer_id' => $customer_id));
	    $this->data['detail'] = $this->customer_model->get_detail_information($this->data['customer'], $this->session->userdata('id'));
        
        
		$this->smarty->assign('data', $this->data);
		$this->smarty->display('customer/detail_individual.tpl');
	}
	
    public function displayBill()
    {
        $get = $this->input->get(NULL, TRUE);
        if($bill_info = $this->customer_model->display_bill_detail($get['id']))
        {
        	if($bill_info['user_id'] != $this->session->userdata('id'))
        		redirect('/customer/individual');
        	
        	$upload_status = "";
			$post = $this->input->post(null, true);
			if ($post != null)
			{
				$customer_id = $post['customer_id'];
				$bill_id = $post['bill_id'];
				$file_type = $post['file_type'];
	
				if ($_FILES["file"]["error"] > 0)
				{
					//echo "Error: " . $_FILES["file"]["error"] . "<br>";
					$upload_status = "fail";
	
				} else
				{
					$realname = $_FILES["file"]["name"];
					$ext = pathinfo($realname, PATHINFO_EXTENSION);
					$ext_len = strlen($ext);
					
					$filename = substr($realname, 0, ($ext_len+1)*-1) . '-' . date('Y-m-d_H-i-s') . '.' . $ext;
					
					copy($_FILES["file"]["tmp_name"], "/var/vhosts/enigmaanaliz.com/public_html/paytrust/cdn/files/upload/" . $filename);
					
					if($_FILES["file"]["size"] > 0)
					{
						if($info = $this->db->get_where("file_type", array('id' => $file_type), 1, 0)->row_array())
						{
							$name = $info['name'];
						}
						else
						{
							$upload_status = "fail";
						}
					}
	
					if ($upload_status == "")
					{
						$upload_status = "ok";
						
						$this->db->insert("files", array(
							'user_id' => $this->session->userdata('id'),
							'content_id' => $bill_id,
							'name' => $name,
							'file_name' => $filename,
							'type' => $file_type,
							'created_on' => date('Y-m-d H:i:s')
						));
					}
					else
					{
						$upload_status = "fail";
						@unlink(getcwd() . "/cdn/files/upload/" . $infod['file_name']);
					}
				}
			}
			$this->data['upload_status'] = $upload_status;
        	
        	
        	$this->data['product'] = $this->customer_model->get_product_by_bill($get['id']);
        	
	        $this->data['customer'] = $this->customer_model->get_information(array('customer_id' => $bill_info['customer_id']));
	        $this->data['bill'] = $bill_info;
	        
	        $this->data['payments'] = $this->customer_model->display_bill_payments($get['id']);
	        $this->data['notes'] = $this->customer_model->display_notes($get['id']);
	        
	        $this->data['files'] = $this->customer_model->display_files($get['id']);
	        
	        $this->data['bill_id'] = $get['id'];
	        
	        $this->smarty->assign('data', $this->data);
			$this->smarty->display('customer/display_bill.tpl');
        }
        else
        	redirect('/customer/individual');
    }
    
    public function add_note()
    {
        $post = $this->input->post(NULL, TRUE);
        unset($post['_wysihtml5_mode']);
        trim($post['title'],$post['content_id']);
        trim($post['wysiwyg']);
        $post['created_on'] = date("Y-m-d H:i:s");
        $post['user_id'] = $this->session->userdata('id');
        $this->customer_model->add_note($post);
        redirect('/customer/displayBill?id='.$post['content_id'], 'location');
    }
    	
	/* bu action altında modelleme yapmadan veritabanına direk bağlanıyorum, yeterli zaman yok development için */
	public function request()
	{
		$error = false;
		$error_messages = array();
		$error_items = array();
		
		$this->data['step'] = 1;
		$post = $this->input->post(null, true);
		
		$customer_id = intval($this->session->userdata('customer_id'));
		$group_id = intval($this->session->userdata('group_id'));
		if($customer_id > 0 && ($group_id != 1 && $group_id != 10) && !$customer_info = $this->db->get_where("enigma_v2.vi_customer_corporation", array('customer_id' => $customer_id, 'balance >' => '26'), 1, 0)->row_array())
		{
			$this->data['error'] = array(
						'type' => 'danger',
						'title' => '',
						'message' => '<strong>Sorgulama yapabilmek için yeterli kontörünüz bulunmuyor.</strong> Lütfen kontör satın aldıktan sonra tekrar deneyin.');
						
			$this->data['step'] = 9;
		}

		$query = $this->db->get_where("restricted_user", array('customer_id' => $customer_id, 'type' => 'request', 'status' => 'active'));
		if($query->num_rows() > 0)
		{
			$info = $query->row_array();

			$this->data['error'] = array(
						'type' => 'danger',
						'title' => '',
						'message' => '<strong>'.(!empty($info['message']) ? $info['message'] : 'Sistem şu anda rapor talebi yapmanıza izin vermemektedir. Bunun bir hata olduğunu düşünüyorsanız lütfen bizimle iletişime geçerek durumu bildirin.').'</strong>');
						
			$this->data['step'] = 9;
		}
	
		$get = $this->input->get(null, true);
		$report_id = $get['report_id'];

		if(isset($report_id) && !empty($report_id)) {
			
			if($report_data = $this->db->select('report_id, customer_id_report, identity_no')->get_where("enigma_v2.vi_report_request_paytrust_simple", array('report_id' => $report_id, 'pt_report_type' => 'pre'), 1, 0)->row_array()) {
				
				
				$this->db->select('name, middle_name, surname, birthdate');
				$customer_data = $this->db->get_where("enigma_v2.customer_individual", array('customer_id' => $report_data['customer_id_report']), 1, 0)->row_array();
				
				
				$this->db->order_by('id', 'desc');
				$this->db->select('bank_id');
				$bank_data = $this->db->get_where("enigma_v2.customer_bank", array('customer_id' => $report_data['customer_id_report']), 1, 0)->row_array();
				
				
				$this->db->order_by('id', 'desc');
				$this->db->select('phone');
				$phone_data = $this->db->get_where("enigma_v2.customer_phones", array('customer_id' => $report_data['customer_id_report']), 1, 0)->row_array();
				
				$post['report_id'] = $report_data['report_id'];
				$post['customer']['identity_no'] = $report_data['identity_no'];
				$post['customer']['birthdate']['year'] = date('Y', strtotime($customer_data['birthdate']));
				$post['customer']['birthdate']['month'] = date('m', strtotime($customer_data['birthdate']));
				$post['customer']['birthdate']['day'] = date('d', strtotime($customer_data['birthdate']));
				
				$post['customer']['name'] = $customer_data['name'];
				$post['customer']['middle_name'] = $customer_data['middle_name'];
				$post['customer']['surname'] = $customer_data['surname'];
				
				$post['customer']['mobile'] = $phone_data['phone'];
				$post['customer']['bank'] = $bank_data['bank_id'];
				
			} else {
				redirect('/customer/request?type=invalid_report_id');
			}
		} else {
			$report_id = 0;
		}
		
		$pre_report_id = $report_id;

		if ($post != null)
		{
			$this->data['postdata'] = $post;

			// customer identity no validations
			if (empty($post['customer']['identity_no']))
			{
				$error = true;
				$error_messages['customer_identity_no'] = 'Lütfen T.C. kimlik numarasını girin.';
				$error_items['customer_identity_no'] = 'customer_identity_no';
			} elseif (mb_strlen($post['customer']['identity_no']) != 11)
			{
				$error = true;
				$error_messages['customer_identity_no'] = 'Geçersiz bir T.C. kimlik numarası girdiniz.';
				$error_items['customer_identity_no'] = 'customer_identity_no';
			} elseif ($this->validate_tckn($post['customer']['identity_no']) != true)
			{
				$error = true;
				$error_messages['customer_identity_no'] = 'Geçersiz bir T.C. kimlik numarası girdiniz.';
				$error_items['customer_identity_no'] = 'customer_identity_no';
			}

			// Customer birthdate validations
			if (empty($post['customer']['birthdate']['year']))
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen müşterinin doğum tarihini girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			} elseif (intval($post['customer']['birthdate']['year']) < 1900 || intval($post['customer']['birthdate']['year']) > date('Y'))
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen geçerli bir doğum tarihi girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			}

			if (empty($post['customer']['birthdate']['month']))
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen müşterinin doğum tarihini girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			} elseif (intval($post['customer']['birthdate']['month']) < 1 || intval($post['customer']['birthdate']['month']) > 12)
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen geçerli bir doğum tarihi girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			}
			if (empty($post['customer']['birthdate']['day']))
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen müşterinin doğum tarihini girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			} elseif (intval($post['customer']['birthdate']['day']) < 1 || intval($post['customer']['birthdate']['day']) > 31)
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen geçerli bir doğum tarihi girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			}
            
            
			$age = date_create($post['customer']['birthdate']['year'] . '-' . $post['customer']['birthdate']['month'] . '-' . $post['customer']['birthdate']['day']);
            $date= date_create(date('Y-m-d'));
            
            $diff = date_diff($age, $date);

			if ($diff->y >= $this->get_param('max_yas')-2)
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'En fazla ' . $this->get_param('max_yas') . ' yaşındaki müşteriler sigorta kapsamındadır.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			}
			elseif ($diff->y < $this->get_param('min_yas'))
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'En az ' . $this->get_param('min_yas') . ' yaşındaki müşteriler sigorta kapsamındadır.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			}

			// Customer name validations
			if (empty($post['customer']['name']))
			{
				$error = true;
				$error_messages['customer_name'] = 'Lütfen müşteri adını girin.';
				$error_items['customer_name'] = 'customer_name';
			} elseif (mb_strlen($post['customer']['name']) < 2)
			{
				$error = true;
				$error_messages['customer_name'] = 'Müşteri adı en az 2 harften oluşmalıdır.';
				$error_items['customer_name'] = 'customer_name';
			} elseif (preg_match('/[^A-Za-züğışöçÜĞİŞÖÇ\s]/i', $post['customer']['name']))
			{
				$error = true;
				$error_messages['customer_name'] =
					'Lütfen müşteri adını kontrol ederek tekrar yazın.';
				$error_items['customer_name'] = 'customer_name';
			}

			// Customer middle name validations
			if (!empty($post['customer']['middle_name']) && mb_strlen($post['customer']['middle_name']) < 2)
			{
				$error = true;
				$error_messages['customer_middle_name'] = 'İkinci ad en az 2 harften oluşmalıdır.';
				$error_items['customer_middle_name'] = 'customer_middle_name';
			} elseif (!empty($post['customer']['middle_name']) && preg_match('/[^A-Za-züğışöçÜĞİŞÖÇ\s]/i', $post['customer']['middle_name']))
			{
				$error = true;
				$error_messages['customer_middle_name'] =
					'Lütfen ikinci adı kontrol ederek tekrar yazın.';
				$error_items['customer_middle_name'] = 'customer_middle_name';
			}

			// Customer surname validations
			if (empty($post['customer']['surname']))
			{
				$error = true;
				$error_messages['customer_surname'] = 'Lütfen soyadını girin.';
				$error_items['customer_surname'] = 'customer_surname';
			} elseif (mb_strlen($post['customer']['surname']) < 2)
			{
				$error = true;
				$error_messages['customer_surname'] = 'Soyadı en az 2 harften oluşmalıdır.';
				$error_items['customer_surname'] = 'customer_surname';
			} elseif (preg_match('/[^A-Za-züğışöçÜĞİŞÖÇ\s]/i', $post['customer']['surname']))
			{
				$error = true;
				$error_messages['customer_surname'] =
					'Lütfen soyadını kontrol ederek tekrar yazın.';
				$error_items['customer_surname'] = 'customer_surname';
			}

			// Customer email validations
			if (empty($post['customer']['email']))
			{
				$error = true;
				$error_messages['customer_email'] = 'Lütfen müşteri e-posta adresini girin.';
				$error_items['customer_email'] = 'customer_email';
			} elseif (!filter_var($post['customer']['email'], FILTER_VALIDATE_EMAIL))
			{
				$error = true;
				$error_messages['customer_email'] = 'Lütfen geçerli bir e-posta adresi girin.';
				$error_items['customer_email'] = 'customer_email';
			}
			
			// mobile validations
			if (empty($post['customer']['mobile']))
			{
				$error = true;
				$error_messages['customer_mobile'] = 'Lütfen müşterinin cep telefonunu girin.';
				$error_items['customer_mobile'] = 'customer_mobile';
			}
			
			// Customer address city validations
			if (empty($post['customer']['address']))
			{
				$error = true;
				$error_messages['customer_address'] = 'Lütfen müşterinin adresini girin.';
				$error_items['customer_address'] = 'customer_address';
			}
			
			// Customer address city validations
			if (empty($post['customer']['address_city_id']))
			{
				$error = true;
				$error_messages['customer_address_city_id'] = 'Lütfen il seçiniz.';
				$error_items['customer_address_city_id'] = 'customer_address_city_id';
			}
			
			// Customer address district validations
			if (empty($post['customer']['address_district_id']))
			{
				$error = true;
				$error_messages['customer_address_district_id'] = 'Lütfen ilçe seçiniz.';
				$error_items['customer_address_district_id'] = 'customer_address_district_id';
			}

			// banka kontrol
			if (empty($post['customer']['bank']))
			{
				$error = true;
				$error_messages['customer_bank'] = 'Müşterinin çalıştığı bankayı seçiniz.';
				$error_items['customer_bank'] = 'customer_bank';
			}

			if ($error === false)
			{
				if ($this->validate_tckn_api($post['customer']['identity_no'], $post['customer']['name'], $post['customer']['middle_name'], $post['customer']['surname'], $post['customer']['birthdate']['year']) === true)
				{
					$this->data['step'] = 2;
					
					if(isset($post['customer_request_step_2']) && $post['customer_request_step_2'] == "true")
					{
						$this->data['step'] = 3;
					}
					
					if(isset($post['customer_request_step_3']) && $post['customer_request_step_3'] == "true")
					{
						$this->data['step'] = 3;
						
						/*
						echo "<pre>";
						print_r($post);
						echo "</pre>";
						exit;
						*/

						$error = false;

						// nufüs seri kontrol
						if (empty($post['customer']['identity_serial']))
						{
							$error = true;
							$error_messages['customer_identity_serial'] = 'Nüfus seri boş olamaz.';
							$error_items['customer_identity_serial'] = 'customer_identity_serial';
						}
						if (strlen($post['customer']['identity_serial']) != 3)
						{
							$error = true;
							$error_messages['customer_identity_serial'] = 'Nüfus seri hatalı.';
							$error_items['customer_identity_serial'] = 'customer_identity_serial';
						}

						// nüfus no kontrol
						if (empty($post['customer']['identity_sequence']))
						{
							$error = true;
							$error_messages['customer_identity_sequence'] = 'Nüfus no boş olamaz.';
							$error_items['customer_identity_sequence'] = 'customer_identity_sequence';
						}
						if (strlen($post['customer']['identity_sequence']) != 6)
						{
							$error = true;
							$error_messages['customer_identity_sequence'] = 'Nüfus no hatalı.';
							$error_items['customer_identity_sequence'] = 'customer_identity_sequence';
						}

						// nüfusa kayıtlı il kontrolü
						if (empty($post['customer']['identity_city_id']))
						{
							$error = true;
							$error_messages['customer_identity_city_id'] = 'Nüfusa kayıtlı ili seçmediniz.';
							$error_items['customer_identity_city_id'] = 'customer_identity_city_id';
						}

						// cilt no kontrolü
						if (empty($post['customer']['identity_volume_no']))
						{
							$error = true;
							$error_messages['customer_identity_volume_no'] = 'Cilt numarası boş olamaz.';
							$error_items['customer_identity_volume_no'] = 'customer_identity_volume_no';
						}

						// anne kızlık soyad 1. harf
						if (empty($post['customer']['mother_maiden_name_1']))
						{
							$error = true;
							$error_messages['customer_mother_maiden_name_1'] = 'Boş olamaz.';
							$error_items['customer_mother_maiden_name_1'] = 'customer_mother_maiden_name_1';
						}

						// anne kızlık soyad 2. harf
						if (empty($post['customer']['mother_maiden_name_2']))
						{
							$error = true;
							$error_messages['customer_mother_maiden_name_2'] = 'Boş olamaz.';
							$error_items['customer_mother_maiden_name_2'] = 'customer_mother_maiden_name_2';
						}

						if ($error === false)
						{

						
							// tüm veritabanı işlemleri burada başlıyor
							$this->db->trans_begin();
							
							
							$customer_post = $post['customer'];
							// müşteri bizde kayıtlı mı*
							if($info = $this->db->get_where("enigma_v2.customer_individual", array('identity_no' => $customer_post['identity_no']), 1, 0)->row_array())
							{
								// müşteri var sadece bilgi güncelliyoruz
								$this->db->update("enigma_v2.customer_individual", array(
									'email' => $customer_post['email']
								), array('customer_id' => $info['customer_id']));
								
								// müşteri detay tablosunu güncelleyelim
								$this->db->update("enigma_v2.customer_individual_detail", array(
									'driving_license_id' => $customer_post['driving_license_id'],
									'driving_license_office' => $customer_post['driving_license_office'],
									'identity_city_id' => $customer_post['identity_city_id'],
									'identity_volume_no' => str_pad($customer_post['identity_volume_no'], 4, "0", STR_PAD_LEFT),
									'identity_serial' => $customer_post['identity_serial'],
									'identity_sequence' => $customer_post['identity_sequence'],
									'mother_maiden_name' => $customer_post['mother_maiden_name_1'].$customer_post['mother_maiden_name_2']
								), array('customer_id' => $info['customer_id']));
								
								// müşteri detay temp tablosuna bir kayıt
								$this->db->insert("enigma_v2.customer_individual_detail_temp", array(
									'customer_id' => $info['customer_id'],
									'driving_license_id' => $customer_post['driving_license_id'],
									'driving_license_office' => $customer_post['driving_license_office'],
									'identity_city_id' => $customer_post['identity_city_id'],
									'identity_volume_no' => str_pad($customer_post['identity_volume_no'], 4, "0", STR_PAD_LEFT),
									'identity_serial' => $customer_post['identity_serial'],
									'identity_sequence' => $customer_post['identity_sequence'],
									'mother_maiden_name' => $customer_post['mother_maiden_name_1'].$customer_post['mother_maiden_name_2'],
									'created_on' => date('Y-m-d H:i:s')
								));
							}
							else
							{
								// müşteri yok
								
								$this->db->insert("enigma_v2.customer_individual", array(
									'identity_no' => $customer_post['identity_no'],
									'name' => $customer_post['name'],
									'middle_name' => $customer_post['middle_name'],
									'surname' => $customer_post['surname'],
									'email' => $customer_post['email'],
									'membership_type' => 'temp',
									'birthdate' => $customer_post['birthdate']['year'].'-'.str_pad($customer_post['birthdate']['month'], 2, "0", STR_PAD_LEFT).'-'.str_pad($customer_post['birthdate']['day'], 2, "0", STR_PAD_LEFT),
									'created_on' => date('Y-m-d H:i:s')
								));
								
								$info['customer_id'] = $this->db->insert_id();
								
								// müşteri detay tablosuna bir kayıt
								$this->db->insert("enigma_v2.customer_individual_detail", array(
									'customer_id' => $info['customer_id'],
									'driving_license_id' => $customer_post['driving_license_id'],
									'driving_license_office' => $customer_post['driving_license_office'],
									'identity_city_id' => $customer_post['identity_city_id'],
									'identity_volume_no' => str_pad($customer_post['identity_volume_no'], 4, "0", STR_PAD_LEFT),
									'identity_serial' => $customer_post['identity_serial'],
									'identity_sequence' => $customer_post['identity_sequence'],
									'mother_maiden_name' => $customer_post['mother_maiden_name_1'].$customer_post['mother_maiden_name_2']
								));
								
								// müşteri detay temp tablosuna bir kayıt
								$this->db->insert("enigma_v2.customer_individual_detail_temp", array(
									'customer_id' => $info['customer_id'],
									'driving_license_id' => $customer_post['driving_license_id'],
									'driving_license_office' => $customer_post['driving_license_office'],
									'identity_city_id' => $customer_post['identity_city_id'],
									'identity_volume_no' => str_pad($customer_post['identity_volume_no'], 4, "0", STR_PAD_LEFT),
									'identity_serial' => $customer_post['identity_serial'],
									'identity_sequence' => $customer_post['identity_sequence'],
									'mother_maiden_name' => $customer_post['mother_maiden_name_1'].$customer_post['mother_maiden_name_2'],
									'created_on' => date('Y-m-d H:i:s')
								));
							}
							
							
							
							// müşteriyi bizim taraftaki veritabanına da kaydediyoruz
							if(!$this->db->get_where("customer_individual", array('identity_no' => $customer_post['identity_no'], 'branch_id' => $this->session->userdata('id')), 1, 0)->num_rows() > 0)
							{
								$this->db->insert("customer_individual", array(
									'customer_id' => $info['customer_id'],
									'branch_id' => $this->session->userdata('id'),
									'identity_no' => $customer_post['identity_no'],
									'name' => $customer_post['name'],
									'middle_name' => !empty($customer_post['middle_name']) ? $customer_post['middle_name'] : null,
									'surname' => $customer_post['surname'],
									'created_on' => date('Y-m-d H:i:s'),
									'balance' => $this->get_param('max_fatura_tutari')
								));
							}
							
							
							
							// müşteri telefonunu kaydediyoruz
							if(isset($customer_post['mobile']) && !empty($customer_post['mobile']))
							{
								$this->db->insert("enigma_v2.customer_phones", array(
									'customer_id' => $info['customer_id'],
									'phone' => str_replace('-', ' ', $customer_post['mobile']),
									'phone_type' => 'mobile',
									'created_on' => date('Y-m-d H:i:s')							
								));
							}

							if(isset($customer_post['phone']) && !empty($customer_post['phone']))
							{
								$this->db->insert("enigma_v2.customer_phones", array(
									'customer_id' => $info['customer_id'],
									'phone' => str_replace('-', ' ', $customer_post['phone']),
									'phone_type' => 'normal',
									'created_on' => date('Y-m-d H:i:s')
								));
							}
							
							// müşteri adresini kaydediyoruz
							if(isset($customer_post['address']) && !empty($customer_post['address']))
							{
								$this->db->insert("enigma_v2.customer_address", array(
									'customer_id' => $info['customer_id'],
									'address' => $customer_post['address'],
									'type' => 'contact',
									'city_id' => $customer_post['address_city_id'],
									'district_id' => $customer_post['address_district_id']					
								));
							}
							
							// müşteri bankasını kaydediyoruz
							if(isset($customer_post['bank']) && !empty($customer_post['bank']))
							{
								$this->db->insert("enigma_v2.customer_bank", array(
									'customer_id' => $info['customer_id'],
									'bank_id' => $customer_post['bank']				
								));
							}
							
							
							if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
							    $ip = $_SERVER['HTTP_CLIENT_IP'];
							} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
							    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
							} else {
							    $ip = $_SERVER['REMOTE_ADDR'];
							}
							
							
							
							if($pre_report_id == 0)
							{
								// Müşteri eklendikten ve bilgileri girildikten sonra rapor talebini oluşturuyoruz
								$this->db->insert("enigma_v2.report_request", array(
									'customer_id_request' => $customer_id,
									'branch_id_request' => $this->session->userdata('office_id'),
									'customer_id_report' => $info['customer_id'],
									'report_type' => 'paytrust',
									'created_on' => date('Y-m-d H:i:s'),
									'kkb' => 'yes',
									'sgk' => 'yes',
									'uyap' => 'yes',
									'ehliyet_ceza' => 'no',
									'telefon' => 'no',
									'plaka' => 'no',
									'gib' => 'yes',
									'sbm' => 'no',
									'tedas' => 'no',
									'ito' => 'no',
									'bilanco' => 'no',
									'ihale' => 'no'
								));
								$report_id = $this->db->insert_id();
								
								
								// bizim veritabanındaki tabloya da bir kayıt düşüyoruz
								$this->db->insert("reports", array(
									'report_id' => $report_id,
									'pre_report_id' => $pre_report_id,
									'user_id' => intval($this->session->userdata('id')),
									'customer_id_request' => $customer_id,
									'customer_id_report' => $info['customer_id'],
									'created_on' => date('Y-m-d H:i:s'),
									'expired_on' => date('Y-m-d H:i:s', (time() + 60*30))
								));
								$peyton_report_id = $this->db->insert_id();
	
								// bu satışın kullanacağı bill_id yi boş olarak oluşturarak reporta set ediyoruz
								$this->db->insert("customer_bill", array(
									'report_id' => $report_id
								));
								$bill_id = $this->db->insert_id();
								$this->db->update('reports', array('bill_id' => $bill_id), array('id' => $peyton_report_id));
							
								// SMS gönderilmek üzere ilk kaydı oluşturuyoruz
								$this->db->insert("enigma_peyton.report_sms", array(
									'report_id' => $report_id,
									'bank_id' => $customer_post['bank'],
									'created_on' => date('Y-m-d H:i:s')
								));
							
								$rq_result = $this->paytrust_api->request('credit/rapor_kontor_isle', array('report_id' => $report_id, 'type' => 'bekleyen'));
								if(@$rq_result['result']['result'] != "done")
								{
									
								}
								
							}
							else
							{
								$report_id = $pre_report_id;
								
								$this->db->update("enigma_v2.report_request", array(
									'report_status' => '50'
								), array('id' => $report_id));
								
								
								$this->db->update("enigma_peyton.reports", array(
									'pre_report_id' => $report_id,
									'report_type' => 'normal',
									'report_status' => '50',
									//'grade' => null,
									//'score' => null,
									'expired_on' => date('Y-m-d H:i:s', (time() + 60*30)),
									'status' => 'waiting',
									'status_kontrol' => 'hayir'
								), array('report_id' => $report_id));
							}
							

							if ($this->db->trans_status() === FALSE)
							{
								// hata oluştu
								$this->db->trans_rollback();
								
								$this->data['error'] = array(
									'type' => 'danger',
									'title' => '',
									'message' => 'Teknik bir problem meydana geldi. Lütfen tekrar deneyin.');
							}
							else
							{
								// herşey yolunda merkez
								$this->db->trans_commit();
								
								// başvuru formunu temp klasöründen gerçeğe çekiyoruz
								$file = "../cdn/files/docs_pdf_temp/" . $customer_post['identity_no'] . '-' . date('Y-m-d') . '.pdf';
								if(file_exists($file)) {
									
									$file_key = $this->uniqe_file_key();
									$file_name = $report_id . '-' . 'basvuru_formu' .'-'.$file_key.'.pdf';
									
									$put = S3::putObject(
								        S3::inputFile($file),
								        'paytrust-ir',
								        'storage/basvuru_formu/' . $file_name,
								        S3::ACL_PUBLIC_READ,
								        array(),
								        array(
								            "Cache-Control" => "max-age=315360000",
								            "Expires" => gmdate("D, d M Y H:i:s T", strtotime("+5 years"))
								        )
								    );
								    
								    if($put === true) {
								    	
								    	// dosya başarıyla upload edildi, temp olarak tutulan dosyayı siliyoruz
								    	unlink($file);
								    	unlink($customer_post['identity_no'] . '-' . date('Y-m-d') . '.pdf');
								    	
								    	$created_on = date('Y-m-d H:i:s');
										$expired_on = date('Y-m-d H:i:s', mktime( date('H'), date('i'), date('s'), date('m'), date('d') + 1, date('Y') ) );
										$this->db->insert('enigma_peyton.report_files', array(
											'report_id' => $report_id,
											'type' => 'basvuru_formu',
											'code' => $file_key,
											'created_on' => $created_on,
											'expired_on' => $expired_on,
											'file' => '' . $file_name,
											'status' => 'active',
											'transfer' => 'completed',
											'doc_code' => null,
											'postdata' => null
										));
								    	
								    } else {
								    	
								    	// sorun var bilgi ver!
								    	$this->report_log($report_id, 'eksik_basvuru_formu', 'müşteri sorgulamada eksik evrak', 'panel');
								    	
								    }
								    
								} else {
									
									$this->report_log($report_id, 'eksik_basvuru_formu', 'müşteri sorgulamada eksik evrak', 'panel');
									
								}
								
								// burada istek işleme aracını çalıştırıp sonra rapor sonuç sayfasına yölendiriyoruz
								
								$url = $this->get_param('ws_url_paytrust_rapor_isleme');
								$get_result_s = file_get_contents($url . "?report_id=" . $report_id);
								$get_result = json_decode($get_result_s, true);
	
								if($get_result['status'] == "done")
								{
									redirect('/report/paytrust');
								}
								else
								{
									$this->report_log($report_id, 'rapor_isleme_hatasi', 'rapor işlenirken problem meydana geldi. - ' . $get_result_s, 'panel');
									redirect('/report/paytrust');
								}
							}

						}
					}

				} else
				{
					$this->data['error'] = array(
						'type' => 'danger',
						'title' => '',
						'message' => 'Lütfen müşteriye ait kimlik bilgileri kontrol ederek tekrar deneyin.');
				}
			}
		}
		
		$this->data['months'] = array('', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık');
		
		$this->data['error_items'] = $error_items;
		$this->data['error_messages'] = $error_messages;
		
		$this->data['bank'] = $this->address_model->bank();
		
		$this->data['city'] = $this->address_model->city();
		$this->data['driving_office'] = $this->address_model->driving_license_office();
		
		if (!empty($post['customer']['address_city_id']))
		{
			$this->data['district'] = $this->address_model->district($post['customer']['address_city_id']);
		}

		for($i = (date('Y')-17); $i >= (date('Y')-80); $i--)
			$this->data['year_list'][] = $i;
		
		$this->smarty->assign('data', $this->data);
		$this->smarty->display('customer/request.tpl');
	}
	
    /* Yeni ön sorgu */
	public function pre_request()
	{
		if($this->get_param('on_sorgulama_izni') != "evet") {
			redirect('customer/request');
			exit;
		}

		$error = false;
		$error_messages = array();
		$error_items = array();

		$this->data['step'] = 1;
		$post = $this->input->post(null, true);

		$customer_id = intval($this->session->userdata('customer_id'));
		$group_id = intval($this->session->userdata('group_id'));
		if($customer_id > 0 && ($group_id != 1 && $group_id != 10) && !$customer_info = $this->db->get_where("enigma_v2.vi_customer_corporation", array('customer_id' => $customer_id, 'balance >' => '26'), 1, 0)->row_array())
		{
			$this->data['error'] = array(
						'type' => 'danger',
						'title' => '',
						'message' => '<strong>Sorgulama yapabilmek için yeterli kontörünüz bulunmuyor.</strong> Lütfen kontör satın aldıktan sonra tekrar deneyin.');

			$this->data['step'] = 9;
		}

		$query = $this->db->get_where("restricted_user", array('customer_id' => $customer_id, 'type' => 'request', 'status' => 'active'));
		if($query->num_rows() > 0)
		{
			$info = $query->row_array();

			$this->data['error'] = array(
						'type' => 'danger',
						'title' => '',
						'message' => '<strong>'.(!empty($info['message']) ? $info['message'] : 'Sistem şu anda rapor talebi yapmanıza izin vermemektedir. Bunun bir hata olduğunu düşünüyorsanız lütfen bizimle iletişime geçerek durumu bildirin.').'</strong>');

			$this->data['step'] = 9;
		}

		if ($post != null)
		{
			$this->data['postdata'] = $post;

			// customer identity no validations
			if (empty($post['customer']['identity_no']))
			{
				$error = true;
				$error_messages['customer_identity_no'] = 'Lütfen T.C. kimlik numarasını girin.';
				$error_items['customer_identity_no'] = 'customer_identity_no';
			} elseif (mb_strlen($post['customer']['identity_no']) != 11)
			{
				$error = true;
				$error_messages['customer_identity_no'] = 'Geçersiz bir T.C. kimlik numarası girdiniz.';
				$error_items['customer_identity_no'] = 'customer_identity_no';
			} elseif ($this->validate_tckn($post['customer']['identity_no']) != true)
			{
				$error = true;
				$error_messages['customer_identity_no'] = 'Geçersiz bir T.C. kimlik numarası girdiniz.';
				$error_items['customer_identity_no'] = 'customer_identity_no';
			}

			// Customer birthdate validations
			if (empty($post['customer']['birthdate']['year']))
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen müşterinin doğum tarihini girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			} elseif (intval($post['customer']['birthdate']['year']) < 1900 || intval($post['customer']['birthdate']['year']) > date('Y'))
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen geçerli bir doğum tarihi girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			}

			if (empty($post['customer']['birthdate']['month']))
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen müşterinin doğum tarihini girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			} elseif (intval($post['customer']['birthdate']['month']) < 1 || intval($post['customer']['birthdate']['month']) > 12)
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen geçerli bir doğum tarihi girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			}
			if (empty($post['customer']['birthdate']['day']))
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen müşterinin doğum tarihini girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			} elseif (intval($post['customer']['birthdate']['day']) < 1 || intval($post['customer']['birthdate']['day']) > 31)
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'Lütfen geçerli bir doğum tarihi girin.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			}

			$age = date_create($post['customer']['birthdate']['year'] . '-' . $post['customer']['birthdate']['month'] . '-' . $post['customer']['birthdate']['day']);
            $date= date_create(date('Y-m-d'));
            
            $diff = date_diff($age, $date);
                       
            
			if ($diff->y >= $this->get_param('max_yas')-2)
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'En fazla ' . $this->get_param('max_yas') . ' yaşındaki müşteriler sigorta kapsamındadır.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			}
			elseif ($diff->y < $this->get_param('min_yas'))
			{
				$error = true;
				$error_messages['customer_birthdate'] = 'En az ' . $this->get_param('min_yas') . ' yaşındaki müşteriler sigorta kapsamındadır.';
				$error_items['customer_birthdate'] = 'customer_birthdate';
			}

			// Customer name validations
			if (empty($post['customer']['name']))
			{
				$error = true;
				$error_messages['customer_name'] = 'Lütfen müşteri adını girin.';
				$error_items['customer_name'] = 'customer_name';
			} elseif (mb_strlen($post['customer']['name']) < 2)
			{
				$error = true;
				$error_messages['customer_name'] = 'Müşteri adı en az 2 harften oluşmalıdır.';
				$error_items['customer_name'] = 'customer_name';
			} elseif (preg_match('/[^A-Za-züğışöçÜĞİŞÖÇ\s]/i', $post['customer']['name']))
			{
				$error = true;
				$error_messages['customer_name'] =
					'Lütfen müşteri adını kontrol ederek tekrar yazın.';
				$error_items['customer_name'] = 'customer_name';
			}

			// Customer middle name validations
			if (!empty($post['customer']['middle_name']) && mb_strlen($post['customer']['middle_name']) < 2)
			{
				$error = true;
				$error_messages['customer_middle_name'] = 'İkinci ad en az 2 harften oluşmalıdır.';
				$error_items['customer_middle_name'] = 'customer_middle_name';
			} elseif (!empty($post['customer']['middle_name']) && preg_match('/[^A-Za-züğışöçÜĞİŞÖÇ\s]/i', $post['customer']['middle_name']))
			{
				$error = true;
				$error_messages['customer_middle_name'] =
					'Lütfen ikinci adı kontrol ederek tekrar yazın.';
				$error_items['customer_middle_name'] = 'customer_middle_name';
			}

			// Customer surname validations
			if (empty($post['customer']['surname']))
			{
				$error = true;
				$error_messages['customer_surname'] = 'Lütfen soyadını girin.';
				$error_items['customer_surname'] = 'customer_surname';
			} elseif (mb_strlen($post['customer']['surname']) < 2)
			{
				$error = true;
				$error_messages['customer_surname'] = 'Soyadı en az 2 harften oluşmalıdır.';
				$error_items['customer_surname'] = 'customer_surname';
			} elseif (preg_match('/[^A-Za-züğışöçÜĞİŞÖÇ\s]/i', $post['customer']['surname']))
			{
				$error = true;
				$error_messages['customer_surname'] =
					'Lütfen soyadını kontrol ederek tekrar yazın.';
				$error_items['customer_surname'] = 'customer_surname';
			}

			// mobile validations
			if (empty($post['customer']['mobile']))
			{
				$error = true;
				$error_messages['customer_mobile'] = 'Lütfen müşterinin cep telefonunu girin.';
				$error_items['customer_mobile'] = 'customer_mobile';
			}


			// banka kontrol
			if (empty($post['customer']['bank']))
			{
				$error = true;
				$error_messages['customer_bank'] = 'Müşterinin çalıştığı bankayı seçiniz.';
				$error_items['customer_bank'] = 'customer_bank';
			}

			if ($error === false)
			{
				$error_sorgulama_limit = false;
				if ($this->validate_tckn_api($post['customer']['identity_no'], $post['customer']['name'], $post['customer']['middle_name'], $post['customer']['surname'], $post['customer']['birthdate']['year']) === true)
				{

					// tüm veritabanı işlemleri burada başlıyor
					$this->db->trans_begin();


					$customer_post = $post['customer'];
					// müşteri bizde kayıtlı mı*

					$this->db->select('customer_id');
					if($info = $this->db->get_where("enigma_v2.customer_individual", array('identity_no' => $customer_post['identity_no']), 1, 0)->row_array())
					{
						//Müşteriye ait bekleyen rapor talebi varsa bunun önüne geçiyoruz
						if ($this->db->get_where("enigma_peyton.vi_reports",array('customer_id_report' => $info['customer_id'], 'report_status =' => '50', 'demo =' => 'no'), 1, 0)->num_rows() > 0)
						{
							$error_sorgulama_limit = true;

							$this->data['error'] = array(
								'type' => 'danger',
								'title' => '',
								'message' => 'Şu an bu müşteriye ait bekleyen bir rapor talebi olduğu için tekrar sorgulama yapılamamaktadır.');
						}

						//Müşteriye ait en fazla bu kadar sorgulama yapılabilir
						elseif($this->db->get_where("enigma_peyton.vi_reports", array('customer_id_report' => $info['customer_id'], 'demo' => 'no', 'created_on >' => date('Y-m-d H:i:s', (time() - $this->get_param('sorgulama_limit_zaman')*60))))->num_rows() > $this->get_param('sorgulama_limit_sayi'))
						{
							$error_sorgulama_limit = true;

							$this->data['error'] = array(
								'type' => 'danger',
								'title' => '',
								'message' => 'Şu an bu müşteriye ait daha fazla sorgulama yapamazsınız.');
						}
						else
						{
							// müşteri var sadece bilgi güncelliyoruz

							// EK - ön sorgulama durumunda müşterinin bilgilerini güncellemiyoruz

							$customer_post['mother_maiden_name'] = null;
							$customer_post['identity_sequence'] = null;
							$customer_post['identity_serial'] = null;
							$customer_post['identity_volume_no'] = null;
							$customer_post['identity_city_id'] = null;

							$customer_post['driving_license_id'] = null;
							$customer_post['driving_license_office'] = null;

							$customer_post['mother_maiden_name_1'] = null;
							$customer_post['mother_maiden_name_2'] = null;


							// müşteri detay tablosunu güncelleyelim
							$this->db->update("enigma_v2.customer_individual_detail", array(
								'driving_license_id' => $customer_post['driving_license_id'],
								'driving_license_office' => $customer_post['driving_license_office'],
								'identity_city_id' => $customer_post['identity_city_id'],
								'identity_volume_no' => str_pad($customer_post['identity_volume_no'], 4, "0", STR_PAD_LEFT),
								'identity_serial' => $customer_post['identity_serial'],
								'identity_sequence' => $customer_post['identity_sequence'],
								'mother_maiden_name' => $customer_post['mother_maiden_name_1'].$customer_post['mother_maiden_name_2']
							), array('customer_id' => $info['customer_id']));


							// müşteri detay temp tablosuna bir kayıt
							$this->db->insert("enigma_v2.customer_individual_detail_temp", array(
								'customer_id' => $info['customer_id'],
								'driving_license_id' => $customer_post['driving_license_id'],
								'driving_license_office' => $customer_post['driving_license_office'],
								'identity_city_id' => $customer_post['identity_city_id'],
								'identity_volume_no' => str_pad($customer_post['identity_volume_no'], 4, "0", STR_PAD_LEFT),
								'identity_serial' => $customer_post['identity_serial'],
								'identity_sequence' => $customer_post['identity_sequence'],
								'mother_maiden_name' => $customer_post['mother_maiden_name_1'].$customer_post['mother_maiden_name_2'],
								'created_on' => date('Y-m-d H:i:s')
							));
						}
					}
					else
					{
						// müşteri yok

						$this->db->insert("enigma_v2.customer_individual", array(
							'identity_no' => $customer_post['identity_no'],
							'name' => $customer_post['name'],
							'middle_name' => !empty($customer_post['middle_name']) ? $customer_post['middle_name'] : null,
							'surname' => $customer_post['surname'],
							//'email' => $customer_post['email'],
							'membership_type' => 'temp',
							'birthdate' => $customer_post['birthdate']['year'].'-'.str_pad($customer_post['birthdate']['month'], 2, "0", STR_PAD_LEFT).'-'.str_pad($customer_post['birthdate']['day'], 2, "0", STR_PAD_LEFT),
							'created_on' => date('Y-m-d H:i:s')
						));

						$info['customer_id'] = $this->db->insert_id();

						// müşteri detay tablosuna bir kayıt
						$this->db->insert("enigma_v2.customer_individual_detail", array(
							'customer_id' => $info['customer_id'],
							'driving_license_id' => $customer_post['driving_license_id'],
							'driving_license_office' => $customer_post['driving_license_office'],
							'identity_city_id' => $customer_post['identity_city_id'],
							'identity_volume_no' => str_pad($customer_post['identity_volume_no'], 4, "0", STR_PAD_LEFT),
							'identity_serial' => $customer_post['identity_serial'],
							'identity_sequence' => $customer_post['identity_sequence'],
							'mother_maiden_name' => $customer_post['mother_maiden_name_1'].$customer_post['mother_maiden_name_2']
						));

						// müşteri detay temp tablosuna bir kayıt
						$this->db->insert("enigma_v2.customer_individual_detail_temp", array(
							'customer_id' => $info['customer_id'],
							'driving_license_id' => $customer_post['driving_license_id'],
							'driving_license_office' => $customer_post['driving_license_office'],
							'identity_city_id' => $customer_post['identity_city_id'],
							'identity_volume_no' => str_pad($customer_post['identity_volume_no'], 4, "0", STR_PAD_LEFT),
							'identity_serial' => $customer_post['identity_serial'],
							'identity_sequence' => $customer_post['identity_sequence'],
							'mother_maiden_name' => $customer_post['mother_maiden_name_1'].$customer_post['mother_maiden_name_2'],
							'created_on' => date('Y-m-d H:i:s')
						));
					}


					if($error_sorgulama_limit == false)
					{

						// müşteriyi bizim taraftaki veritabanına da kaydediyoruz
						if(!$this->db->get_where("customer_individual", array('identity_no' => $customer_post['identity_no'], 'branch_id' => $this->session->userdata('id')), 1, 0)->num_rows() > 0)
						{
							$this->db->insert("customer_individual", array(
								'customer_id' => $info['customer_id'],
								'branch_id' => $this->session->userdata('id'),
								'identity_no' => $customer_post['identity_no'],
								'name' => $customer_post['name'],
								'middle_name' => !empty($customer_post['middle_name']) ? $customer_post['middle_name'] : null,
								'surname' => $customer_post['surname'],
								'created_on' => date('Y-m-d H:i:s'),
								'balance' => $this->get_param('max_fatura_tutari')
							));
						}


						// müşteri telefonunu kaydediyoruz
						if(isset($customer_post['mobile']) && !empty($customer_post['mobile']))
						{
							$this->db->insert("enigma_v2.customer_phones", array(
								'customer_id' => $info['customer_id'],
								'phone' => str_replace('-', ' ', $customer_post['mobile']),
								'phone_type' => 'mobile',
								'created_on' => date('Y-m-d H:i:s')
							));
						}


						// müşteri bankasını kaydediyoruz
						if(isset($customer_post['bank']) && !empty($customer_post['bank']))
						{
							$this->db->insert("enigma_v2.customer_bank", array(
								'customer_id' => $info['customer_id'],
								'bank_id' => $customer_post['bank']
							));
						}


						// Müşteri eklendikten ve bilgileri girildikten sonra rapor talebini oluşturuyoruz
						$this->db->insert("enigma_v2.report_request", array(
							'customer_id_request' => $customer_id,
							'branch_id_request' => $this->session->userdata('office_id'),
							'customer_id_report' => $info['customer_id'],
							'report_type' => 'paytrust',
							'created_on' => date('Y-m-d H:i:s'),
							'kkb' => 'yes',
							'sgk' => 'no',
							'uyap' => 'no',
							'ehliyet_ceza' => 'no',
							'telefon' => 'no',
							'plaka' => 'no',
							'gib' => 'yes',
							'sbm' => 'no',
							'tedas' => 'no',
							'ito' => 'no',
							'bilanco' => 'no',
							'ihale' => 'no'
						));
						$report_id = $this->db->insert_id();


						// bizim veritabanındaki tabloya da bir kayıt düşüyoruz
						$this->db->insert("reports", array(
							'report_id' => $report_id,
							'user_id' => intval($this->session->userdata('id')),
							'customer_id_request' => $customer_id,
							'customer_id_report' => $info['customer_id'],
							'report_type' => 'pre',
							'status' => 'pre_waiting',
							'created_on' => date('Y-m-d H:i:s'),
							'expired_on' => date('Y-m-d H:i:s', (time() + 60*30))
						));
						$peyton_report_id = $this->db->insert_id();

						// bu satışın kullanacağı bill_id yi boş olarak oluşturarak reporta set ediyoruz
						$this->db->insert("customer_bill", array(
							'report_id' => $report_id
						));
						$bill_id = $this->db->insert_id();
						$this->db->update('reports', array('bill_id' => $bill_id), array('id' => $peyton_report_id));


						// SMS gönderilmek üzere ilk kaydı oluşturuyoruz
						$this->db->insert("enigma_peyton.report_sms", array(
							'report_id' => $report_id,
							'bank_id' => $customer_post['bank'],
							'created_on' => date('Y-m-d H:i:s')
						));

						if ($this->db->trans_status() === FALSE)
						{
							// hata oluştu
							$this->db->trans_rollback();
								
							$this->data['error'] = array(
								'type' => 'danger',
								'title' => '',
								'message' => 'Teknik bir problem meydana geldi. Lütfen tekrar deneyin.');
						}
						else
						{
							// herşey yolunda merkez
							$this->db->trans_commit();
							
							$url = $this->get_param('ws_url_paytrust_rapor_isleme');
							$get_result_s = file_get_contents($url . "?report_id=" . $report_id);
							$get_result = json_decode($get_result_s, true);

							if($get_result['status'] == "done")
							{
								redirect('/report/paytrust');
							}
							else
							{
								$message = "report ID: " .$report_id . "<hr /><hr /><hr />" . $get_result_s;
								$done = $this->send_email("it@enigmaanaliz.com", "Report Request Error", $message);

								usleep(100);
								redirect('/report/paytrust');
							}
						}
					}

				}
				else
				{
					$this->data['error'] = array(
						'type' => 'danger',
						'title' => '',
						'message' => 'Müşteriye ait girilen bilgiler Nüfus ve Vatandaşlık İşleri Kimlik Doğrulama servisi tarafından doğrulanmadı. Lütfen girdiğiniz kimlik bilgilerini kontrol ederek tekrar deneyin.');
				}
			}
			/*
			 else {
						$this->data['error'] = array(
						'type' => 'danger',
						'title' => '',
						'message' => 'Müşteriye sorgulama için uygun bulunmamıştır.');
			}
			*/
		}

		$this->data['months'] = array('', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık');

		$this->data['error_items'] = $error_items;
		$this->data['error_messages'] = $error_messages;

		$this->data['bank'] = $this->address_model->bank();

		for($i = (date('Y')-17); $i >= (date('Y')-80); $i--)
			$this->data['year_list'][] = $i;

		$this->smarty->assign('data', $this->data);
		$this->smarty->display('customer/pre_request.tpl');
	}

  

	
	
	public function individual()
	{
		$this->customer(__FUNCTION__);
	}
	
	public function corporation()
	{
		$this->customer(__FUNCTION__);
	}
	
	private function customer($type)
	{
		$get = $this->input->get(NULL, TRUE);
		if(is_array($get))
			$get = array_map('trim', $get);
			
		$page = isset($get['page']) ? intval($get['page']) : 1;
		
		if($page < 1)
			redirect('/customer/' . $type);
		
		$get['branch_id'] = intval($this->session->userdata('id'));
		//$get['office_id'] = intval($this->session->userdata('office_id'));

		$customer_count = intval($this->customer_model->filter_customer($type, true, $get));
		$total_page = ceil($customer_count / $this->page_limit);
		$total_page = $total_page == 0 ? 1 : $total_page;
		
		if($page > $total_page)
			redirect('/customer/' . $type);
			
		$get['branch_id'] = intval($this->session->userdata('id'));
		//$get['office_id'] = intval($this->session->userdata('office_id'));
		
		$url_data = $get;
		if(isset($url_data['page']) && !empty($url_data['page']))
			unset($url_data['page']);
		
		$url_string = is_array($url_data) ? http_build_query($url_data) : '?hl=tr';
		
		$this->data['getdata'] = $get;
		$this->data['current_page'] = $page;
		$this->data['total_page'] = $total_page;
		$this->data['page_link'] = strpos($url_string, '?') !== false ? $url_string : '?'.$url_string;
		$this->data['page_limit'] = $this->page_limit;
		$this->data['customer_list'] = $this->customer_model->filter_customer($type, false, $get, $page, $this->page_limit);
		$this->data['customer_count'] = $customer_count;
		
		$this->smarty->assign('data', $this->data);
		$this->smarty->display('customer/'.$type.'.tpl');
	}
}
