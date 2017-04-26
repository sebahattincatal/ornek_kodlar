<?php namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\Models\Common\City;
use App\Models\Common\Bank;
use Auth;
use URL;
use Request;
use Validator;
use Redirect;

class ReportController extends Controller {

    public function __construct()
    {

    }

    /**
    *   Request Method (İstek Methodu)
    */
    public function request()
    {
        if (Request::isMethod('post')) {

            $validator = Validator::make(Request::all(), array(
                'bank' => 'required|not_in:0',
                'mobile' => 'required',
                'city_id' => 'required|not_in:0',
                'district_id' => 'required|not_in:0',
                'address' => 'required',
                'identity_serial' => 'required',
                'identity_sequence' => 'required',
                'mother_maiden_name_1' => 'required|alpha',
                'mother_maiden_name_2' => 'required|alpha',
                'identity_volume_no' => 'required',
                'identity_city_id' => 'required|not_in:0'
            ));

            if ($validator->fails()) {
                return Redirect::route('individual.report.request')
                    ->withErrors($validator)
                    ->withInput();
            }

            $content = Request::get('content');

            return Redirect::route('individual.report.request')->with('success', true);
        }

        $data = [];
        if($cities = City::select(['id', 'name'])->get()) {
            $data['cities'][] = "Seçiniz";
            foreach($cities as $key => $val) {
                $data['cities'][$val['id']] = $val['name'];
            }
        }

        if($banks = Bank::select(['id', 'name'])->get()) {
            $data['banks'][] = "Seçiniz";
            foreach($banks as $key => $val) {
                $data['banks'][$val['id']] = $val['name'];
            }
        }

        $data['districts'][] = "Önce il seçin";

        return view('individual.report.request')->with('data', $data);
    }
}
