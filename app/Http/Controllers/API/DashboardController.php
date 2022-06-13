<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\Packages;
use App\Models\TCustomersPackages;
use App\Models\TPayments;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function getData() {
        $now = Carbon::now();
        
        $customer_count = Customers::where('status', 1)->count();
        
        $cust_packages = TCustomersPackages::rightJoin('m_packages as p', function($join) {
            $join->on('p.id', 't_customers_packages.package_id')
            ->where('t_customers_packages.status', 1);
        })
        ->leftJoin('m_customers as c', function($join) {
            $join->on('c.id', 't_customers_packages.customer_id')
            ->where('c.status', 1);
        })
        ->select('p.name', DB::raw('count(t_customers_packages.id) as total'))
        ->groupBy('p.name')->get();
        
        $revenue = TPayments::join('m_packages as p', function($join) {
            $join->on('p.id', 't_payments.package_id');
        })->whereMonth('t_payments.date_of_issued', $now->month)
        ->select('t_payments.*', 'p.price')->sum('p.price');
        
        $revenue_by_month = TPayments::join('m_packages as p', function($join) {
            $join->on('p.id', 't_payments.package_id');
        })
        ->select(DB::raw('MONTH(t_payments.date_of_issued) month'), DB::raw('sum(p.price) as total'))
        ->groupBy('month')->get();
        
        $current_payment = TPayments::join('m_packages as p', function($join) {
            $join->on('p.id', 't_payments.package_id');
        })
        ->join('m_customers as c', function($join) {
            $join->on('c.id', 't_payments.customer_id');
        })
        ->whereMonth('t_payments.date_of_issued', $now->month)
        ->select('t_payments.id', 'p.price', 'p.name as package', 'c.name as customer', 't_payments.bulan as bulan', 't_payments.tahun as tahun', 't_payments.date_of_issued as tanggal')->get();
        
        $data['customer'] = $customer_count;
        $data['cust_package'] = $cust_packages;
        $data['current_revenue'] = $revenue;
        $data['revenue_by_month'] = $revenue_by_month;
        $data['current_payment'] = $current_payment;
        return response()->json(['data' => $data, 'success' => true]);
    }
}
