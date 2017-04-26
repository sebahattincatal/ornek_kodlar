<?php namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;

use \App\Models\Individual\Customer;
use \App\Models\Paytrust\CustomerBill;
use Auth;
use URL;


class CustomerController extends Controller
{

    public function __construct()
    {

    }

    /**
    *   Orders(Siparişler)
    */
    public function orders()
    {
        $customer_id = Auth::user()->customer_id;

        $customer = new \App\Enigmatech\Base\Customer($customer_id);

        return view('individual.customer.order')->with('orders', $customer->getOrders());
    }

    /**
    *   Order Details(Sipariş Detayı)
    */
    public function order_detail($order_id)
    {
        $order = CustomerBill::with('payments')->find($order_id);
        return view('individual.customer.order_detail')->with('order', $order);
    }

    /**
    *   Payments(Ödemeler)
    */
    public function payments()
    {
        $payment_list = array(
            'all' => array(),
            'odendi' => array(),
            'odenecek' => array(),
            'geciken' => array(),
            'geciken_all' => array()
        );

        $customer_id = Auth::user()->customer_id;

        if ($orders = Customer::with('payments')->find($customer_id)) {
            foreach ($orders->payments as $item) {
                foreach ($item->payments as $payment) {
                    $payment_list['all'][date('Ym', strtotime($payment->payment_date_normal))][] = $payment;

                    if ($payment->status == "odendi")
                        $payment_list['odendi'][date('Ym', strtotime($payment->payment_date_normal))][] = $payment;

                    if (strtotime($payment->payment_date_normal) > time() && $payment->status == "odenecek")
                        $payment_list['odenecek'][date('Ym', strtotime($payment->payment_date_normal))][] = $payment;

                    if (strtotime($payment->payment_date_normal) < time() && $payment->status == "odenecek") {
                        $payment_list['geciken'][date('Ym', strtotime($payment->payment_date_normal))][] = $payment;
                        $payment_list['geciken_all'][] = $payment;
                    }
                }

            }
        }

        return view('individual.customer.payment')->with('payment_list', $payment_list);
    }

    /**
    *   Packages(Paketler)
    */
    public function packages()
    {
        $customer_id = Auth::user()->customer_id;

        $package_list = array();

        return view('individual.customer.package')->with('packages', $package_list);
    }
}