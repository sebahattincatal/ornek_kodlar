<?php
namespace App\Models\Paytrust;

use Illuminate\Database\Eloquent\Model;

class CustomerReport extends Model
{
    protected $table = 'enigma_peyton.reports';
    protected $primaryKey = 'id';
    protected $appends = array('reportStatus');

    public $timestamps = false;

    public function bill()
    {
        return $this->hasOne('App\Models\Paytrust\CustomerBill', 'id', 'bill_id');
    }

    public function payments()
    {
        return $this->hasMany('App\Models\Paytrust\CustomerBillPayment', 'bill_id', 'bill_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\Paytrust\User','user_id','id');
    }
    
    public function userGroup()
    {
        return $this->belongsTo('App\Models\Paytrust\User','user_id','id')
                ->with('group')
                ;
    }

    public function customerIndividual()
    {
        return $this->belongsTo('App\Models\Individual\Customer','customer_id_report','customer_id');
    }
    
    public function customerPhone()
    {
        return $this->belongsTo('App\Models\Paytrust\CustomerPhone','customer_id_report','customer_id');
    }

    public function customerBank()
    {
        return $this->belongsTo('App\Models\Paytrust\CustomerBank','customer_id_report','customer_id');
    }
    
    public function customerAddress()
    {
        return $this->belongsTo('App\Models\Paytrust\CustomerAddress','customer_id_report','customer_id')
            ->with('city')
            ->with('district');
    }
        
    public function customerBillDoc()
    {
        return $this->belongsTo('App\Models\Paytrust\CustomerBillDoc','bill_id','bill_id');
    }
    
    public function customerBillDocNotes()
    {
        return $this->belongsTo('App\Models\Paytrust\CustomerBillDoc','bill_id','bill_id')->where('note','<>','');
    }

    public function reportStatus()
    {
        return $this->belongsTo('App\Models\Paytrust\ReportReason','reason_id','id');
    }
    
    public function getReportStatusDetailAttribute(){
        if ( $this->reason_id ){
            return array( 'status' =>'Reddedildi', 'detail' => $this->getRelation('reportStatus')->message );
        } else {
            switch ( $this->status ){
                case "cancel_waiting":
                    return array( 'status' => 'İptal Onayı Bekliyor' );
                break;
                case "pre_waiting":
                case "waiting":
                    return array( 'status' => 'Onaylandı' );
                break;
                case "cancel":
                    return array( 'status' => 'İptal' );
                break;
                default:
                    return array( 'status' => 'Satış Yapıldı' );
            }
            
        }
        return $this->status;
    }
}