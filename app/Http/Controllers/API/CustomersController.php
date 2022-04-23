<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomersResource;
use App\Models\Customers;
use App\Models\TCustomersPackages;
use Illuminate\Http\Request;
use Validator;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $data = Customers::select('m_customers.*', 'cp.package_id')
        ->join('t_customers_packages as cp', function($join){
            $join->on('cp.customer_id', 'm_customers.id')
            ->where('cp.status', '1');
        })
        ->where('m_customers.status', '1')
        ->latest()->get();
        return response()->json(['success' => true, 'data' => CustomersResource::collection($data), 'msg' => 'Customers fetched']);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'phone' => 'unique:m_customers',
            'email' => 'unique:m_customers|string|max:255',
            'package_id' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $customer = Customers::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email
         ]);
         
         $customer_packages = TCustomersPackages::create([
             'customer_id' => $customer->id,
             'package_id' => $request->package_id
         ]);
         
        $data = Customers::join('t_customers_packages as cp', function($join) {
            $join->on('cp.customer_id', 'm_customers.id')
            ->where('cp.status', '1');
        })->where('m_customers.id', $customer->id)->get();
        
        return response()->json(['success' => true, 'msg' => 'Customer added successfully.', 'data' => CustomersResource::collection($data)]);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //$customer = Customers::find($id);
        $customer = Customers::join('t_customers_packages as cp', function($join) {
            $join->on('cp.customer_id', 'm_customers.id')
            ->where('cp.status', '1');
        })->where('m_customers.id', $id)->where('m_customers.status', '1')->first();
        // dd($customer);
        if (is_null($customer)) {
            return response()->json('Data not found', 404);
        }
        return response()->json(['success' => true, 'data' => new CustomersResource($customer)]);
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customers $customer)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'string|max:255',
            'package_id' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }
        
        $is_exist = TCustomersPackages::where('customer_id', $customer->id)->where('status', '1')->latest()->first();
        if($is_exist->package_id != $request->package_id) {
            $customer_package = TCustomersPackages::find($is_exist->id);
            $customer_package->status = 0;
            $unactive_package = $customer_package->save();
            
            if($unactive_package) {
                $new_cust_package = TCustomersPackages::create([
                    'customer_id' => $customer->id,
                    'package_id' => $request->package_id
                ]);
            }
        }

        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->email = $request->email;
        
        $customer->save();
        
        $data = Customers::join('t_customers_packages as cp', function($join) {
            $join->on('cp.customer_id', 'm_customers.id')
            ->where('cp.status', '1');
        })->where('m_customers.id', $customer->id)->first();
        
        return response()->json(['success' => true, 'msg' => 'Customer updated successfully.', 'data' => new CustomersResource($data)]);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customers $customer)
    {
        $customer->status = 0;
        $customer->save();

        return response()->json(['msg' => 'Customer deleted successfully', 'success' => true]);
    }
}
