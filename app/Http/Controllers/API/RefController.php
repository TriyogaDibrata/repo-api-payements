<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackagesResource;
use App\Models\Packages;
use Illuminate\Http\Request;

class RefController extends Controller
{
    //Get all packages
    public function getPackages() {
        $data = Packages::latest()->get();

        return response()->json(['success' => true, 'data' => PackagesResource::collection($data), 'msg' => 'Packages fetched.']);
    }
}
