<?php

if (!defined('BASEPATH'))

	exit('No direct script access allowed');

    

 class Aviva extends MY_Controller

 {

    function __construct()

    {
       parent::__construct();

        $this->load->model('aviva_model');
    }


    // Tarih format düzenleme

    function tarih_fix($str) 

    {

	   $timezone = new DateTimeZone('UTC');

	   $date = new DateTime($str, $timezone);

	   return $date->format('c');

    }

    
    //Poliçe Yaratma

    function policy($report_id)

    {      

        $report = $this->aviva_model->report($report_id);

        $customer_individual = $this->aviva_model->individual($report['customer_id_report']);

        $customer_address = $this->aviva_model->address($report['customer_id_report']);

        $customer_mobile = $this->aviva_model->phones($report['customer_id_report']);

        $installments = $this->aviva_model->installments($report['bill_id']);

        $user = $this->aviva_model->user($report['customer_id_report']);


        $customer = array(

            'identityNo' => $customer_individual['identity_no'],

            'gsm' => $customer_mobile['phone'],

            'email' => $customer_individual['email'],

            'city' => $customer_address['city_name'],

            'district' => $customer_address['district_name'],

            'name' => $customer_individual['name'],

            'surname' => $customer_individual['surname']
        );    

        $installment = array();

        $installment[] = array(

            'installmentDate' => tarih_fix($installments['payment_date_normal']), 

            'installmentAmount' => $installments['monthly_payment'], 

            'installmentStatus' => 'ODENECEK', 

            'installmentPayDate' => tarih_fix($installments['payment_date_normal']) 

        );

        $createPolicy = array(

            'contractNo' => $report['report_id'], 

            'customer' => $customer, 

            'paymentType' => 'CEK', 

            'taxIdNo' => $user['vkn'],   

            'salesDate' => $report['satis_tarihi'], 

            'project' => $report['police_turu'], 

            'installmentList' => $installment,

            'webServiceVersion' => 1
        );

        ini_set('soap.wsdl_cache_enabled',0);

        ini_set('soap.wsdl_cache_ttl',0);  

        $ctx_opts = array(

            'http' => array(

            'header' => 'Content-type: text/html; charset=utf-8'

            )

        );	

        $ctx = stream_context_create($ctx_opts);  

        $apiKey['apiKey'] = 'BIMEKS'; 

        $token = new SoapVar($apiKey, SOAP_ENC_OBJECT); 

        $header = new SoapHeader('http://service.unipay.integration.aviva.com/', 'token', $token);              

        $client = new SoapClient("http://premavi.avivasigorta.com.tr/AvivaBPM/services/enigmaWebService?wsdl", array("exceptions" => 0, "trace" => 1, 'Content-Type' => $ctx , 'soap_version' => SOAP_1_1));   

        $client->__setSoapHeaders(array($header));

        $response = $client->createPolicy(array('createPolicyInput' => $createPolicy));

        $response = json_decode(json_encode($response), true);

        echo "\n\n\n";

        echo $client->__getLastRequest()."\n\n\n";

        echo $client->__getLastResponse()."\n\n\n";    

        $this->db->insert("aviva_paytrust.policy",array(

            'appendage_no' => $response['return']['policyList'][0]['appendageNo'],  

            'finish_date' => $response['return']['policyList'][0]['finishDate'],      

            'policy_no' => $response['return']['policyList'][0]['policyNo'],          

            'premium' => $response['return']['policyList'][0]['premium'], 

            'return_premium' => $response['return']['policyList'][0]['returnPremium'], 

            'product_no' => $response['return']['policyList'][0]['productNo'],       

            'start_date' => $response['return']['policyList'][0]['startDate'],      

            'branch_code' => $response['return']['policyList'][0]['branchCode'],

            'branch_name' => $response['return']['policyList'][0]['branchName'],

            'bank_code' => $response['return']['policyList'][0]['bankCode'],

            'bank_name' => $response['return']['policyList'][0]['bankName'],

            'insured_tax_id' => $response['return']['policyList'][0]['insuredTaxIdNo'],

            'user_id' => $user['id'],

            'customer_id_request' => $report['customer_id_request'],

            'customer_id_report' => $report['customer_id_report'],

            'created_on' => $response['return']['policyList'][0]['createDate'],

            'report_id' => $report['report_id']

        ));

        
        $this->db->insert("aviva_paytrust.policy",array(

            'appendage_no' => $response['return']['policyList'][1]['appendageNo'],  

            'finish_date' => $response['return']['policyList'][1]['finishDate'],      

            'policy_no' => $response['return']['policyList'][1]['policyNo'],          

            'premium' => $response['return']['policyList'][1]['premium'], 

            'return_premium' => $response['return']['policyList'][1]['returnPremium'], 

            'product_no' => $response['return']['policyList'][1]['productNo'],       

            'start_date' => $response['return']['policyList'][1]['startDate'],      

            'branch_code' => $response['return']['policyList'][1]['branchCode'],

            'branch_name' => $response['return']['policyList'][1]['branchName'],

            'bank_code' => $response['return']['policyList'][1]['bankCode'],

            'bank_name' => $response['return']['policyList'][1]['bankName'],

            'insured_tax_id' => $response['return']['policyList'][1]['insuredTaxIdNo'],

            'user_id' => $user['id'],

            'customer_id_request' => $report['customer_id_request'],

            'customer_id_report' => $report['customer_id_report'],

            'created_on' => $response['return']['policyList'][1]['createDate'],

            'report_id' => $report['report_id']

        ));

    }

    //Poliçe PDF Görüntüleme

    function policy_pdf($report_id)

    {

        $report = $this->aviva_model->report($report_id);

        $policy = $this->aviva_model->policy($report['report_id']);

        $user = $this->aviva_model->user($report['customer_id_report']);

        $getPolicyReport = array(

            'contractNo' => $report['report_id'], 

            'endorsNo' => $policy['appendage_no'], 

            'project' => $report['police_turu'], 

            'webServiceVersion' => 1

        );

        ini_set('soap.wsdl_cache_enabled',0);

        ini_set('soap.wsdl_cache_ttl',0);

        

        $ctx_opts = array(

            'http' => array(

            'header' => 'Content-type: text/html; charset=utf-8'

            )

        );


        $ctx = stream_context_create($ctx_opts);

        $apiKey['apiKey'] = 'BIMEKS'; 

        $token = new SoapVar($apiKey, SOAP_ENC_OBJECT); 

        $header = new SoapHeader('http://service.unipay.integration.aviva.com/', 'token', $token);

                        

        $client = new SoapClient("http://premavi.avivasigorta.com.tr/AvivaBPM/services/enigmaWebService?wsdl", array("exceptions" => 0, "trace" => 1, 'Content-Type' => $ctx , 'soap_version' => SOAP_1_1));

            

        $client->__setSoapHeaders(array($header));

            

        $response = $client->getPolicyReport(array('getPolicyReportInput' => $getPolicyReport));

        $response = json_decode(json_encode($response), true);

    

        echo "\n\n\n";

        echo $client->__getLastRequest()."\n\n\n";

        echo $client->__getLastResponse()."\n\n\n";

        $this->db->insert("aviva_paytrust.policy_pdf",array(

            'policy_info1' => $response['return']['policyInfo1'], 

            'policy_info2' => $response['return']['policyInfo2'], 

            'policy_report1' => $response['return']['policyReport1'], 

            'policy_report2' => $response['return']['policyReport2'], 

            'policy_id' => $policy['policy_no'],

            'user_id' => $user['id'],

            'customer_id_request' => $report['customer_id_request'],

            'customer_id_report' => $report['customer_id_report'],

            'report_id' => $report['report_id'],

            'created_on' => date('Y-m-d')

        ));

    }


    //Teminat Bedeli Güncelleme

    function coverage_update($report_id)

    {

        $report = $this->aviva_model->report($report_id);

        $installments = $this->aviva_model->installments($report['bill_id']);

        $user = $this->aviva_model->user($report['customer_id_report']);

        $installment = array();

        $installment[] = array(

            'installmentDate' => $installments['payment_date_normal'], 

            'installmentAmount' => $installments['monthly_payment'],   

            'installmentStatus' => $installments['status'],            

            'installmentPayDate' => $installments['payment_date']       

        );

        $updateCoverageLimit = array(

            'contractNo' => $report['report_id'],

            'payments' => $installment,

            'project' => $report['police_turu'],

            'webServiceVersion' => 1

        );

        ini_set('soap.wsdl_cache_enabled',0);

        ini_set('soap.wsdl_cache_ttl',0);

        

        $ctx_opts = array(

            'http' => array(

            'header' => 'Content-type: text/html; charset=utf-8'

            )

        );
	

        $ctx = stream_context_create($ctx_opts);  

        $apiKey['apiKey'] = 'BIMEKS'; 

        $token = new SoapVar($apiKey, SOAP_ENC_OBJECT); 

        $header = new SoapHeader('http://service.unipay.integration.aviva.com/', 'token', $token);

                        

        $client = new SoapClient("http://premavi.avivasigorta.com.tr/AvivaBPM/services/enigmaWebService?wsdl", array("exceptions" => 0, "trace" => 1, 'Content-Type' => $ctx , 'soap_version' => SOAP_1_1)); 

        $client->__setSoapHeaders(array($header));

        $response = $client->updateCoverageLimit(array('updateCoverageLimitInput' => $updateCoverageLimit));

        $response = json_decode(json_encode($response), true);

        echo "\n\n\n";

        echo $client->__getLastRequest()."\n\n\n";

        echo $client->__getLastResponse()."\n\n\n";

        $this->db->insert("aviva_paytrust.policy_update",array(

            'contract_no' => $response['return']['contractNo'], 

            'endors_no' => $response['return']['endorsNo'], 

            'appendage_no' => $response['return']['policies'][0]['appendageNo'], 

            'finish_date' => $response['return']['policies'][0]['finishDate'], 

            'policy_no' => $response['return']['policies'][0]['policyNo'], 

            'premium' => $response['return']['policies'][0]['premium'], 

            'product_no' => $response['return']['policies'][0]['productNo'], 

            'return_premium' => $response['return']['policies'][0]['returnPremium'], 

            'start_date' => $response['return']['policies'][0]['startDate'],

            'branch_code' => $response['return']['policies'][0]['branchCode'],

            'branch_name' => $response['return']['policies'][0]['branchName'],

            'bank_code' => $response['return']['policies'][0]['bankCode'],

            'bank_name' => $response['return']['policies'][0]['bankName'],

            'insured_tax_id' => $response['return']['policies'][0]['insuredTaxIdNo'],  

            'created_on' => $response['return']['policies'][0]['createDate'],

            'user_id' => $user['id'],

            'customer_id_request' => $report['customer_id_request'],

            'customer_id_report' => $report['customer_id_report'],

            'report_id' => $report['report_id']

        ));

    }


    //Poliçe İptali / Erken Ödeme

    function cancel_policy($report_id)

    {

        $report = $this->aviva_model->report($report_id);

        $policy = $this->aviva_model->policy($report['report_id']);

        $user = $this->aviva_model->user($report['customer_id_report']);

        

        $date = police_iptal(date('Y-m-d'));

        

        if($date == true)

        {

            $cancelPolicy = array(

                'contractNo' => $report['report_id'],  

                'project' => $report['police_turu'], 

                'cancellationDate' => tarih_fix(date('Y-m-d')), 

                'cancellationReason' => 'eklenecek', /* unutma eklenecek sebo */

                'webServiceVersion' => 1

            );

            

            ini_set('soap.wsdl_cache_enabled',0);

            ini_set('soap.wsdl_cache_ttl',0);

        

            $ctx = stream_context_create($ctx_opts);

            

            $apiKey['apiKey'] = 'BIMEKS'; 

            $token = new SoapVar($apiKey, SOAP_ENC_OBJECT); 

            $header = new SoapHeader('http://service.unipay.integration.aviva.com/', 'token', $token);

                        

            $client = new SoapClient("http://premavi.avivasigorta.com.tr/AvivaBPM/services/enigmaWebService?wsdl", array("exceptions" => 0, "trace" => 1, 'Content-Type' => $ctx , 'soap_version' => SOAP_1_1));

                

            $client->__setSoapHeaders(array($header));

            

            $response = $client->cancelPolicy(array('cancelPolicyInput' => $cancelPolicy));

            $response = json_decode(json_encode($response), true);

    

            echo "\n\n\n";

            echo $client->__getLastRequest()."\n\n\n";

            echo $client->__getLastResponse()."\n\n\n";
   

            $returnPremium = $response['return']['returnPremium'];

        

            $this->db->insert('aviva_paytrust.policy_cancellation',array(

                'return_premium' => $returnPremium,

                'policy_id' => $policy['policy_no'],

                'report_id' => $report['report_id'],

                'user_id' => $user['id'],

                'customer_id_request' => $report['customer_id_request'],

                'customer_id_report' => $report['customer_id_report'],

                'created_on' => $policy['created_on'],

                'cancelled_on' => date('Y-m-d')

                ));

        }

    }


    // Taksit Ödeme Bilgisi Gönderme

    function send_paid_installment($report_id)

    {   

        $report = $this->aviva_model->report($report_id);

        $installments = $this->aviva_model->installments($report['bill_id']);

        $user = $this->aviva_model->user($report['customer_id_report']);

        

        $installment = array();

        $installment[] = array(

            'installmentDate' => $installments['payment_date_normal'], 

            'installmentAmount' => $installments['monthly_payment'], 

            'instalmentPaymentDate' => $installments['payment_date_normal'], 

            'installmentPaidAmount' => $installments ['monthly_payment']

        );

        

        if($installment['instalmentPaymentDate'] == $installment['installmentDate'] && $installment['installmentPaidAmount'] == $installment['installmentAmount'])

        {

            $sendPaidInstallment = array(

                'contractNo' => $report['report_id'], 

                'installmentPaymentDate' => tarih_fix($installment['payment_date']), 

                'installmentPaidAmount' => $installment['monthly_payment'], 

                'webServiceVersion' => 1

            );

        

            ini_set('soap.wsdl_cache_enabled',0);

            ini_set('soap.wsdl_cache_ttl',0);

        

            $ctx = stream_context_create($ctx_opts);

            

            $apiKey['apiKey'] = 'BIMEKS'; 

            $token = new SoapVar($apiKey, SOAP_ENC_OBJECT); 

            $header = new SoapHeader('http://service.unipay.integration.aviva.com/', 'token', $token);

                        

            $client = new SoapClient("http://premavi.avivasigorta.com.tr/AvivaBPM/services/enigmaWebService?wsdl", array("exceptions" => 0, "trace" => 1, 'Content-Type' => $ctx , 'soap_version' => SOAP_1_1));

                

            $client->__setSoapHeaders(array($header));

        

            $response = $client->sendPaidInstallment(array('sendPaidInstallmentInput' => $sendPaidInstallment));

            $response = json_decode(json_encode($response), true);

        

            echo "\n\n\n";

            echo $client->__getLastRequest()."\n\n\n";

            echo $client->__getLastResponse()."\n\n\n";


            if($response['return']['errorCode'] == '0')

            {

                $this->db->update('enigma_peyton.customer_payment',array(

                'payment_date' => date('Y-m-d'), 

                'status' => 'ODENDI',

                'user_id' => $user['id'],

                'report_id' => $report['report_id'],

                'bill_id' => $report['bill_id'],

                'customer_id' => $report['customer_id_report'],

                'monthly_payment' => 0

            ));

            }  

        }

    }


    // Poliçe Listesi Getirme

    function policy_list($report_id, $startDate, $endDate)

    {

        $report = $this->aviva_model->report($report_id);  

        $customer = $this->aviva_model->individual($report['bill_id']);

        $user = $this->aviva_model->user($report['customer_id_report']);

        

        $dateMax = police_liste($startDate, $endDate);

        

        if($dateMax == true)

        {

                $getPolicyList = array(

                    'taxIdentityNo' => $user['vkn'],  

                    'identityNo' => $customer['identity_no'], 

                    'contractNo' => $report['report_id'], 

                    'startDate' => tarih_fix($startDate), 

                    'endDate' => tarih_fix($endDate), 

                    'webServiceVersion' => 1

                );

            

            ini_set('soap.wsdl_cache_enabled',0);

            ini_set('soap.wsdl_cache_ttl',0);

        

            $ctx = stream_context_create($ctx_opts);

            

            $apiKey['apiKey'] = 'BIMEKS'; 

            $token = new SoapVar($apiKey, SOAP_ENC_OBJECT); 

            $header = new SoapHeader('http://service.unipay.integration.aviva.com/', 'token', $token);

                        

            $client = new SoapClient("http://premavi.avivasigorta.com.tr/AvivaBPM/services/enigmaWebService?wsdl", array("exceptions" => 0, "trace" => 1, 'Content-Type' => $ctx , 'soap_version' => SOAP_1_1));

                

            $client->__setSoapHeaders(array($header));

        

            $response = $client->getPolicyList(array('getPolicyListInput' => $getPolicyList));

            $response = json_decode(json_encode($response), true);

        

            echo "\n\n\n";

            echo $client->__getLastRequest()."\n\n\n";

            echo $client->__getLastResponse()."\n\n\n";

        }

    }


    //Hasar Dosyası Açma

    function create_claim_file($report_id)

    {

        $report = $this->aviva_model->report($report_id);

        $payment = $this->aviva_model->installments($report['bill_id']);

        $lossReasonCode = $this->aviva_model->lossReasonCode($report_id);

        $user = $this->aviva_model->user($report['customer_id_report']);

        

        $control = police_liste($payment['payment_no'],$payment['status']);

        

        if($control == true)

        {

            $createClaimFile = array(

                'contractNo' => $report['report_id'],  

                'lossDate' => tarih_fix(date('Y-m-d')), 

                'lossReasonCode' => 'eklenecek', 

                'explanation' => 'eklenecek', 

                'webServiceVersion' => 1

            );

            

            ini_set('soap.wsdl_cache_enabled',0);

            ini_set('soap.wsdl_cache_ttl',0);

        

            $ctx = stream_context_create($ctx_opts);

            

            $apiKey['apiKey'] = 'BIMEKS'; 

            $token = new SoapVar($apiKey, SOAP_ENC_OBJECT); 

            $header = new SoapHeader('http://service.unipay.integration.aviva.com/', 'token', $token);

                        

            $client = new SoapClient("http://premavi.avivasigorta.com.tr/AvivaBPM/services/enigmaWebService?wsdl", array("exceptions" => 0, "trace" => 1, 'Content-Type' => $ctx , 'soap_version' => SOAP_1_1));

                

            $client->__setSoapHeaders(array($header));

        

            $response = $client->createClaimFile(array('createClaimFileInput' => $createClaimFile));

            $response = json_decode(json_encode($response), true);

        

            echo "\n\n\n";

            echo $client->__getLastRequest()."\n\n\n";

            echo $client->__getLastResponse()."\n\n\n";

            $claimDocumentList = $response['return']['claimDocumentList'];

            $claimFileNo = $response['return']['claimFileNo'];

            

            $this->db->insert('aviva_paytrust.claim_create',array(

                'claim_no' => $claimFileNo, 

                'code' => $claimDocumentList['code'],    

                'name' => $claimDocumentList['name'], 

                'report_id' => $report['report_id'],

                'user_id' => $user['id'],

                'customer_id_request' => $report['customer_id_request'],

                'customer_id_report' => $report['customer_id_report'],

                'policy_no' => 'eklenecek',

                'created_on' => date('Y-m-d')

            ));

        }

    }
    

    // Hasar Dosyasının Durumunu Sorgulama

    function claim_file_no($report_id)

    {

        $report = $this->aviva_model->report($report_id);

        $claimFile = $this->aviva_model->claimFile($report_id);

        $user = $this->aviva_model->user($report['customer_id_report']);

        

        $getClaimFileByNo = array(

            'claimFileNo' => $claimFile['claimFileNo'],

            'project' => $report['police_turu'],

            'webServiceVersion' => 1

        );

        

        ini_set('soap.wsdl_cache_enabled',0);

        ini_set('soap.wsdl_cache_ttl',0);

    

        $ctx = stream_context_create($ctx_opts);

            

        $apiKey['apiKey'] = 'BIMEKS'; 

        $token = new SoapVar($apiKey, SOAP_ENC_OBJECT); 

        $header = new SoapHeader('http://service.unipay.integration.aviva.com/', 'token', $token);

                        

        $client = new SoapClient("http://premavi.avivasigorta.com.tr/AvivaBPM/services/enigmaWebService?wsdl", array("exceptions" => 0, "trace" => 1, 'Content-Type' => $ctx , 'soap_version' => SOAP_1_1));

                

        $client->__setSoapHeaders(array($header));

    

        $response = $client->getClaimFileByNo(array('getClaimFileByNoInput' => $getClaimFileByNo));

        $response = json_decode(json_encode($response), true);

    

        echo "\n\n\n";

        echo $client->__getLastRequest()."\n\n\n";

        echo $client->__getLastResponse()."\n\n\n";


        $agencyNo = $response['return']['agencyNo'];  

        $insuredName = $response['return']['insuredName']; 

        $lossDate = $response['return']['lossDate']; 

        $missingDocumentList = $response['return']['missingDocumentList']; 

        $noticeDate = $response['return']['noticeDate']; 

        $paymentAmount = $response['return']['paymentAmount']; 

        $paymentDate = $response['return']['paymentDate']; 

        $paymentNo = $response['return']['paymentNo']; 

        $paymentOwnerName = $response['return']['paymentOwnerName']; 

        $policyNo = $response['return']['policyNo']; 

        $status = $response['return']['status']; 

        

        $this->db->insert('aviva_paytrust.claim_get_file',array(

            'agency_no' => $agencyNo,

            'insured_name' => $insuredName,

            'loss_date' => $lossDate,

            'missing_document' => $missingDocumentList,

            'notice_date' => $noticeDate,

            'payment_amount' => $paymentAmount,

            'payment_date' => $paymentDate,

            'payment_no' => $paymentNo,

            'payment_owner' => $paymentOwnerName,

            'policy_no' => $policyNo,

            'status' => $status,

            'claim_no' => $claimFile['claimFileNo'],

            'user_id' => $user['id'],

            'customer_id_request' => $report['customer_id_request'],

            'customer_id_report' => $report['customer_id_report']

        ));


    }


    //Hasar Evraklarını Gönderme

    function send_claim_document($report_id)

    {

        $report = $this->aviva_model->report($report_id);

        $claimFile = $this->aviva_model->claimFile($report['report_id']);

        $user = $this->aviva_model->user($report['customer_id_report']);

        

        $sendClaimDocuments = array(

            'contractNo' => $report['report_id'], 

            'claimFileNo' => $claimFile['claim_no'], 

            'documentName' => $claimFile['name'], 

            'documentContent' => 'eklenecek', 

            'fileName' => 'eklenecek', 

            'mimeType' => 'eklenecek', 

            'webServiceVersion' => 1

        );

        

        ini_set('soap.wsdl_cache_enabled',0);

        ini_set('soap.wsdl_cache_ttl',0);

    

        $ctx = stream_context_create($ctx_opts);

            

        $apiKey['apiKey'] = 'BIMEKS'; 

        $token = new SoapVar($apiKey, SOAP_ENC_OBJECT); 

        $header = new SoapHeader('http://service.unipay.integration.aviva.com/', 'token', $token);

                        

        $client = new SoapClient("http://premavi.avivasigorta.com.tr/AvivaBPM/services/enigmaWebService?wsdl", array("exceptions" => 0, "trace" => 1, 'Content-Type' => $ctx , 'soap_version' => SOAP_1_1));

                

        $client->__setSoapHeaders(array($header));

    

        $response = $client->sendClaimDocuments(array('sendClaimDocumentsInput' => $sendClaimDocuments));

        $response = json_decode(json_encode($response), true);

    

        echo "\n\n\n";

        echo $client->__getLastRequest()."\n\n\n";

        echo $client->__getLastResponse()."\n\n\n"; 

        $documentId = $response['return']['documentId'];

        

        $this->db->insert('aviva_paytrust.claim_send_documents',array(

            'documentId' => $documentId, 

            'report_id' => $report['report_id'],

            'claim_no' => $claimFile['claim_no'],

            'created_on' => 'eklenecek',

            'user_id' => $user['id'],

            'customer_id_request' => $report['customer_id_request'],

            'customer_id_report' => $report['customer_id_report']

        ));

    }


    // Hasar Nedenleri

    function claim_reason($claim_no)

    {

        $reason = array(

            'webServiceVersion' => 1

        );

        

        ini_set('soap.wsdl_cache_enabled',0);

        ini_set('soap.wsdl_cache_ttl',0);

        

        $ctx = stream_context_create($ctx_opts);

            

        $apiKey['apiKey'] = 'BIMEKS'; 

        $token = new SoapVar($apiKey, SOAP_ENC_OBJECT); 

        $header = new SoapHeader('http://service.unipay.integration.aviva.com/', 'token', $token);

                        

        $client = new SoapClient("http://premavi.avivasigorta.com.tr/AvivaBPM/services/enigmaWebService?wsdl", array("exceptions" => 0, "trace" => 1, 'Content-Type' => $ctx , 'soap_version' => SOAP_1_1));

                

        $client->__setSoapHeaders(array($header));

        

        $response = $client->getClaimReasons(array('getClaimReasonsInput' => $reason));

        $response = json_decode(json_encode($response), true);

        

        echo "\n\n\n";

        echo $client->__getLastRequest()."\n\n\n";

        echo $client->__getLastResponse()."\n\n\n";


        $reason_model = $response['return']['getClaimReasonModel'];

        

        $this->db->insert('aviva_paytrust.claim_reason',array(

            'code' => $reason_model['code'], 

            'text' => $reason_model['text'], 

            'claim_no' => $claimFile['claim_no'],

            'report_id' => $report['report_id'],

            'customer_id_request' => $report['customer_id_request'],

            'customer_id_report' => $report['customer_id_report']

        ));

    }

    

 }

?>