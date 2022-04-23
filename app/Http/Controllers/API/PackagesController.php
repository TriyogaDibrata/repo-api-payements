<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackagesResource;
use App\Models\Packages;
use Illuminate\Http\Request;
use Validator;

class PackagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Packages::latest()->get();
        return response()->json(['success' => true, 'data' => PackagesResource::collection($data), 'msg' => 'Packages fetched.']);
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
            'desc' => 'required',
            'price' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $package = Packages::create([
            'name' => $request->name,
            'desc' => $request->desc,
            'price' => $request->price
         ]);
        
        return response()->json(['success' => true, 'msg' => 'Package successfully created.', 'data' => new PackagesResource($package)]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $package = Packages::find($id);
        if (is_null($package)) {
            return response()->json('Data not found', 404); 
        }
        return response()->json(['success' => true, 'data' => new PackagesResource($package)]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Packages $package)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'desc' => 'required',
            'price' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors());       
        }

        $package->name = $request->name;
        $package->desc = $request->desc;
        $package->price = $request->price;
        
        $package->save();
        
        return response()->json(['success' => true, 'msg' => 'Package updated successfully.', 'data' => new PackagesResource($package)]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Packages $package)
    {
        $package->delete();

        return response()->json(['msg' => 'Package deleted successfully', 'success' => true]);
    }
}
