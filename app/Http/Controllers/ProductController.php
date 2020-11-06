<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $title =$request->title;
        $price_to =$request->price_to;
        $price_from =$request->price_from;
        $date =$request->date;
        $products = Product::query();
        $products->with('productvarientprices')->whereHas('productvarientprices', function($q) use($price_to,$price_from){
          if (!empty($price_to) && !empty($price_from))
          {
            $q->whereBetween('price',[$price_to,$price_from]);
          }

        });
        if (!empty($title)) {
           $products->where('title', 'like', '%' . $title . '%');
        }
        if (!empty($date)) {
           $products->whereDate('created_at',$date);
        }

        $products =$products->paginate(10);
        $variant =Variant::all();      
        return view('products.index',compact('products','variant'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
     $this->validate($request,[
            'title'=>['required', 'string', 'max:255', 'unique:products'],
            'sku'=>['required', 'string', 'max:255', 'unique:products'],
        ]);
    if (count($request->product_variant_prices)>0) {
     $product =new Product;
     $product->title =$request->title;
     $product->sku =$request->sku;
     $product->description =$request->description;
     $product->save();
     for ($i=0; $i <count($request->product_variant) ; $i++) { 

         for ($t=0; $t <count($request->product_variant[$i]['tags']) ; $t++) { 
            $variant =new ProductVariant;
            $variant->variant=$request->product_variant[$i]['tags'][$t];
            $variant->variant_id=$request->product_variant[$i]['option'];
            $variant->product_id=$product->id;
            $variant->save();
         }
     }

     for ($j=0; $j <count($request->product_variant_prices)  ; $j++) { 

        $array_index=explode('/', $request->product_variant_prices[$j]['title'],-1);
        $check =ProductVariant::where('product_id',$product->id)->whereIn('variant',$array_index)->pluck('id');
        $price =new ProductVariantPrice;
        $price->product_variant_one =isset($check[0])?$check[0]:null;
        $price->product_variant_two =isset($check[1])?$check[1]:null;
        $price->product_variant_three =isset($check[2])?$check[2]:null;
        $price->price =$request->product_variant_prices[$j]['price'];
        $price->stock =$request->product_variant_prices[$j]['stock'];
        $price->product_id =$product->id;
        $price->save();
     }

     for ($im=0; $im <count($request->product_image)  ; $im++) { 
        
        $image =new ProductImage;
        $image->file_path=$request->product_image[$im];
        $image->product_id =$product->id;
        $image->save();
     }
     return response()->json(['success' => true, 'status' => 'success', 'message' => __('Information Created Successfull')]);
  }else{
    throw ValidationException::withMessages(['message' => __('Please Select atlest One Variant')]);
  }

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants','product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
    $this->validate($request,[
            'title' => ['required', 'string', 'max:255',
                    Rule::unique('products', 'title')->ignore($product->id)],
            'sku'=>['required', 'string', 'max:255',
                    Rule::unique('products', 'sku')->ignore($product->id)],
        ]);
    if (count($request->product_variant_prices)>0) {
     $product->title =$request->title;
     $product->sku =$request->sku;
     $product->description =$request->description;
     $product->save();
     //delete prevoius variant insert new..
     $product->productvarients->delete();
     $product->productvarientprices->delete();
     for ($i=0; $i <count($request->product_variant) ; $i++) { 

         for ($t=0; $t <count($request->product_variant[$i]['tags']) ; $t++) { 
            $variant =new ProductVariant;
            $variant->variant=$request->product_variant[$i]['tags'][$t];
            $variant->variant_id=$request->product_variant[$i]['option'];
            $variant->product_id=$product->id;
            $variant->save();
         }
     }

     for ($j=0; $j <count($request->product_variant_prices)  ; $j++) { 

        $array_index=explode('/', $request->product_variant_prices[$j]['title'],-1);
        $check =ProductVariant::where('product_id',$product->id)->whereIn('variant',$array_index)->pluck('id');
        $price =new ProductVariantPrice;
        $price->product_variant_one =isset($check[0])?$check[0]:null;
        $price->product_variant_two =isset($check[1])?$check[1]:null;
        $price->product_variant_three =isset($check[2])?$check[2]:null;
        $price->price =$request->product_variant_prices[$j]['price'];
        $price->stock =$request->product_variant_prices[$j]['stock'];
        $price->product_id =$product->id;
        $price->save();
     }

     for ($im=0; $im <count($request->product_image)  ; $im++) { 
        
        $image =new ProductImage;
        $image->file_path=$request->product_image[$im];
        $image->product_id =$product->id;
        $image->save();
     }
     return response()->json(['success' => true, 'status' => 'success', 'message' => __('Information Updated Successfull')]);
  }else{
    throw ValidationException::withMessages(['message' => __('Please Select atlest One Variant')]);
  }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
