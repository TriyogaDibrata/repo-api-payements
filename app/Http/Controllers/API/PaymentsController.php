<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\TCustomersPackages;
use App\Models\TPayments;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\Console\Input\Input;
use Validator;

class PaymentsController extends Controller
{
    public function createPayments(Request $request) {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'package_id' => 'required',
            'bulan' => 'required',
            'tahun' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $payment = TPayments::create([
            'customer_id' => $request->customer_id,
            'package_id' => $request->package_id,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun
        ]);

        return response()->json(['success' => true, 'msg' => 'Payment created', 'data' => $payment]);
    }
    
    public function deletePayments($id) {
        $payment = TPayments::find($id);
        $payment->delete();
        return response()->json(['msg' => 'Payment deleted successfully', 'success' => true]);
    }

    public function getCustomers() {
        $now = Carbon::now();
        $data = TCustomersPackages::select('c.*', 'p.id as package_id', 'p.name as package_name', 'tp.id as id_payments')
        ->join('m_customers as c', function($join) {
            $join->on('t_customers_packages.customer_id', 'c.id')
            ->where('c.status', '1');
        })
        ->join('m_packages as p', function($join) {
            $join->on('t_customers_packages.package_id', 'p.id');
        })
        ->leftJoin('t_payments as tp', function($join) use ($now) {
            $join->on('t_customers_packages.customer_id', 'tp.customer_id')
            ->where('tp.bulan', $now->month)
            ->where('tahun', $now->year);
        })
        ->where('t_customers_packages.status', '1')->latest()->get();

        return response()->json(['success' => true, 'msg' => 'Customers fetched', 'data' => $data]);
    }

    public function paymentsDetail(Request $request) {
        $now = Carbon::now();

        $id = $request->id;
        $year = $request->year;

        $customer = Customers::find($id);

        $package = TCustomersPackages::select('p.*')
        ->join('m_customers as c', function($join) use($id) {
            $join->on('t_customers_packages.customer_id', 'c.id')
            ->where('c.id', $id);
        })
        ->join('m_packages as p', function($join) {
            $join->on('t_customers_packages.package_id', 'p.id');
        })->where('t_customers_packages.status', '1')->first();

        $query = TPayments::select('t_payments.*', 'c.name as cust_name', 'p.name as package_name', 'p.price as price')
        ->join('m_customers as c', function($join) {
            $join->on('t_payments.customer_id', 'c.id');
        })
        ->join('m_packages as p', function($join) {
            $join->on('t_payments.package_id', 'p.id');
        })
        ->where('t_payments.customer_id', $id);

        if($year) {
            $query->where('tahun', $year);
        } else {
            $query->where('tahun', $now->year);
        }

        $payments = $query->orderBy('bulan')->get()->groupBy('bulan');

        return response()->json(['success' => true, 'msg' => 'Payments detail found', 'data' => ['customer' => $customer, 'package' => $package, 'payments' => $payments]]);
    }
}
