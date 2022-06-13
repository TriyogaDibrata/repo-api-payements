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
        $data = TCustomersPackages::select('c.*', 'p.id as package_id', 'p.name as package_name')
        ->join('m_customers as c', function($join) {
            $join->on('t_customers_packages.customer_id', 'c.id')
            ->where('c.status', '1');
        })
        ->join('m_packages as p', function($join) {
            $join->on('t_customers_packages.package_id', 'p.id');
        })->where('t_customers_packages.status', '1')->latest()->get();

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
         
        $data = TCustomersPackages::select('c.*', 'p.id as package_id', 'p.name as package_name')
        ->join('m_customers as c', function($join) use($customer) {
            $join->on('t_customers_packages.customer_id', 'c.id')
            ->where('c.id', $customer->id);
        })
        ->join('m_packages as p', function($join) {
            $join->on('t_customers_packages.package_id', 'p.id');
        })->where('t_customers_packages.status', '1')->latest()->get();
        
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
        $data = TCustomersPackages::select('c.*', 'p.id as package_id', 'p.name as package_name')
        ->join('m_customers as c', function($join) use($id) {
            $join->on('t_customers_packages.customer_id', 'c.id')
            ->where('c.id', $id);
        })
        ->join('m_packages as p', function($join) {
            $join->on('t_customers_packages.package_id', 'p.id');
        })->where('t_customers_packages.status', '1')->latest()->get();
        // dd($customer);
        if (is_null($data)) {
            return response()->json('Data not found', 404);
        }
        return response()->json(['success' => true, 'data' => CustomersResource::collection($data)]);
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
        
        $data = TCustomersPackages::select('c.*', 'p.id as package_id', 'p.name as package_name')
        ->join('m_customers as c', function($join) use($customer) {
            $join->on('t_customers_packages.customer_id', 'c.id')
            ->where('c.id', $customer->id);
        })
        ->join('m_packages as p', function($join) {
            $join->on('t_customers_packages.package_id', 'p.id');
        })->where('t_customers_packages.status', '1')->latest()->get();
        return response()->json(['success' => true, 'msg' => 'Customer updated successfully.', 'data' => CustomersResource::collection($data)]);
    }
    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customers $customer)
    {
        $custp = TCustomersPackages::where('customer_id', $customer->id)->where('status', 1)->first();
        $customer->status = 0;
        $saved = $customer->save();
        
        if($saved) {
            $custp->status = 0;
            $custp->save();
        }

        return response()->json(['msg' => 'Customer deleted successfully', 'success' => true]);
    }
}
