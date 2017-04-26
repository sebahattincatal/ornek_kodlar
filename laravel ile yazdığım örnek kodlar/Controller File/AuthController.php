<?php namespace App\Http\Controllers\Individual;

use App\Http\Controllers\Controller;
use App\models\Individual\Customer;
use App\Models\Paytrust\CustomerPhones;
use Hash;
use Request;
use Validator;
use Redirect;
use Auth;
use URL;
use Smsapi;
use Mail;

class AuthController extends Controller
{

    public function __construct()
    {
    }

    /**
    *   Login Method (Giriş Yapma Metodu)
    */
    public function login()
    {
        if (Auth::check())
            return Redirect::route('individual.home.index');

        if (Request::isMethod('post')) {
            $validator = Validator::make(Request::all(), array(
                'identity_no' => 'required|tckn',
                'password' => 'required|min:6',
            ));
            if ($validator->fails()) {

                return Redirect::route('individual.auth.login')->withErrors($validator)->withInput();

            } else {
                $identity_no = Request::get('identity_no');
                $password = Request::get('password');
                $remember = Request::has('remember') ? true : false;
                if (Auth::attempt(['identity_no' => $identity_no, 'password' => $password], $remember)) {

                    return redirect()->intended(URL::route('individual.home.index'));

                } else {
                    return Redirect::route('individual.auth.login')->with('message', 'T.C. Kimlik numarası veya parola hatalı.')->withInput();
                }
            }
        } else {
            return view('individual.auth.login');
        }
    }

    /**
    *   Reminder Method (Beni Hatırla Methodu)
    */
    public function reminder()
    {
        if (Auth::check())
            return Redirect::route('individual.home.index');

        if (Request::isMethod('post')) {
            $validator = Validator::make(Request::all(), array(
                'identity_no' => 'required|tckn',
                'mobile' => 'required',
            ));

            if ($validator->fails()) {
                return Redirect::route('individual.auth.reminder')->with('message', 'T.C. Kimlik numarası veya cep telefonu hatalı.')->withErrors($validator)->withInput();
            } else {
                $identity_no = Request::get('identity_no');
                $mobile = Request::get('mobile');

                $customer = new Customer;
                $customer = $customer->findByIdentityNo($identity_no);

                if ($customer->has_number($mobile)) {

                    $password = rand(1, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
                    $mobile = str_replace(array('(', ')', ' '), '', $mobile);
                    $send = Smsapi::send($mobile, 'Yeni parolanız: ' . $password);

                    Mail::send('individual.emails.auth.reminder', ['password' => $password], function ($m) use ($customer) {

                        $m->from('admin@enigmaanaliz.com', 'PayTrust');
                        $m->to($customer->email, $customer->get_name_surname())->subject('Parolanız Güncellendi!');
                    });

                    if ($send) {
                        $customer->password = Hash::make($password);
                        $customer->save();

                        return Redirect::route('individual.auth.login')->withInput()->with('message', 'Parolanız güncellendi ve cep telefonunuza SMS ile gönderildi');

                    } else {
                        return Redirect::route('individual.auth.reminder')->with('message', 'Parolanız güncellenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.')->withInput();
                    }

                } else {
                    return Redirect::route('individual.auth.reminder')->with('message', 'T.C. Kimlik numarası veya cep telefonu hatalı.')->withInput();
                }
            }
        } else {
            return view('individual.auth.reminder');
        }
    }


    /**
    *   Register Method (Kayıt Methodu)
    */
    public function register()
    {
        if (Auth::check())
            return Redirect::route('individual.home.index');

        $days = array('' => 'Gün');
        for ($i = 1; $i <= 31; $i++)
            $days[$i] = $i;

        $months = array('' => 'Ay');
        for ($i = 1; $i <= 12; $i++)
            $months[$i] = trans('timezone.months.' . $i);

        $years = array('' => 'Yıl');
        for ($i = (date('Y') - 18); $i > (date('Y') - 100); $i--)
            $years[$i] = $i;

        $vdata = array('list_date' => $days, 'list_month' => $months, 'list_year' => $years);

        if (Request::isMethod('post')) {

            $validator = Validator::make(Request::all(), array(
                'identity_no' => 'required|tckn|unique_identity_no',
                'email' => 'required|email',
                'password' => 'required',
                'password_confirm' => 'required|same:password',
                'name' => 'required',
                'surname' => 'required',
                'gender' => 'required|gender',
                'mobile' => 'required',
                'bday' => 'required',
                'bmonth' => 'required',
                'byear' => 'required',
            ));

            if ($validator->fails()) {

                return Redirect::route('individual.auth.register')
                    ->with('message', 'Lütfen kimlik bilgilerinizi kontrol ederek tekrar üye olmayı deneyin.')
                    ->with($vdata)
                    ->withErrors($validator)
                    ->withInput();

            } else {

                echo "@todo: buradan devam edecez";exit;


                $identity_no = Request::get('identity_no');
                $name = Request::get('name');
                $surname = Request::get('surname');
                $byear = Request::get('byear');


                $user = Customer::create(array(
                    'identity_no' => Request::get('identity_no'),
                    'password' => Hash::make(Request::get('password'))
                ));

                if ($user) {

                    return Redirect::intended('/');

                } else {

                    return Redirect::route('individual.auth.register')
                        ->with('message', 'Lütfen kimlik bilgilerinizi kontrol ederek tekrar üye olmayı deneyin.')
                        ->with($vdata)
                        ->withInput();
                }
            }
        } else {
            return view('individual.auth.register')
                ->with($vdata);
        }
    }


    /**
    *   Logout Method (Çıkış Yapma Methodu)
    */
    public function logout()
    {
        if (!Auth::check())
            return Redirect::route('individual.home.index');

        Auth::logout();
        return Redirect::route('individual.home.index');
    }
}
