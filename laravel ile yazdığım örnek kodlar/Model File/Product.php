<?php
namespace App\Models\Paytrust;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'enigma_peyton.product';
    
    protected $fillable = array('group_id','user_id','name','handle','category_id','brand_id');

    public $timestamps = false;

    static function getProductList( $group_id, $user_id, $category_id,$brand_id, $name = '' )
    {
        $where = 'group_id = "' . $group_id .  '" AND (user_id = "' . $user_id . '" OR user_id IS NOT NULL) AND category_id = "' . $category_id . '" AND brand_id = "' . $brand_id . '"';
        if ( $name ){
            $where .= ' AND name = "' . $name . '"';
        }
        if ( $list = self::
                whereRaw( $where )                
                ->get() ){
            $productProduct = [];
            foreach( $list as $key => $value ){
                $productProduct[$value->id] = $value->name;
            }
            return $productProduct;
        }
    }
    
    static function addProduct( $group_id,$user_id,$name,$category_id = '',$brand_id = '' ){
        if ( !$group_id || !$user_id || !$category_id || !$brand_id || !$name )
            return false;
        
        $array = array(
            'group_id' => $group_id,
            'user_id' => $user_id,
            'category_id' => $category_id,
            'brand_id' => $brand_id,
            'name' => $name,
        );
        
        $Product = self::firstOrNew( $array );
        
        $Product->group_id = $group_id;
        $Product->user_id = $user_id;
        $Product->name = $name;
        $Product->handle = \Helper::handle($name);
        $Product->category_id = $category_id;
        $Product->brand_id = $brand_id;
        
        $Product->save();
        return $Product->id;
    }
    
    static function getProductListDetail( array $productList ){        
        $list = array();
        
        if ( $tmpList = self::with('category')->with('brand')->whereIn( 'id', $productList )->get() ){
            foreach( $tmpList as $key => $value ){
                $list[] = array(
                    'product_id' => $value->id,
                    'name' => $value->name,
                    'category_id' => $value->category_id,
                    'category_name' => $value->category->name,
                    'brand_id' => $value->brand_id,
                    'brand_name' => $value->brand->name,                    
                );
            }
            return $list;
        }
        return ;
    }
    
    public function category()
    {
        return $this->belongsTo('App\Models\Paytrust\ProductCategory');
    }
    
    public function brand()
    {
        return $this->belongsTo('App\Models\Paytrust\ProductBrand');
    }
}