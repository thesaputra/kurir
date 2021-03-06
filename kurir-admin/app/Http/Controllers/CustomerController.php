<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\Http\Controllers\Controller;
use App\Models\Customer;

use Yajra\Datatables\Datatables;

class CustomerController extends Controller
{
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('customers.index');
  }

  public function customer_data()
  {
    \DB::statement(\DB::raw('set @rownum=0'));
    $customers = Customer::select([
      \DB::raw('@rownum  := @rownum  + 1 AS rownum'),
      'id',
      'code',
      'name',
      'address',
      'pic_phone',
	  'pic_name'
    ]);
    return Datatables::of($customers)
    ->addColumn('action', function ($customer) {
      return
      '
      <div class="col-md-9">
      <a href="./customer/edit/'.$customer->id.'" class="inline btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>
      </div>
      ';
    })

    ->make(true);
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    return view('customers.create');
  }

  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {
    $this->validation_rules($request);

    $customer=$request->input();
    Customer::create($customer);
    Session::flash('flash_message', 'Data customer berhasil ditambahkan!');
    
    return redirect('admin/customer');
  }

  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show($id)
  {
    //
  }

  /**
  * Show the form for editing the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function edit($id)
  {
    $customer=Customer::find($id);
    return view('customers.edit',compact('customer'));
  }

  /**
  * Update the specified resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function update(Request $request, $id)
  {
    $customerUpdate=$request->input();
    $customer=Customer::find($id);
    $customer->update($customerUpdate);

    Session::flash('flash_message', 'Data customer berhasil diupdate!');

    return redirect('admin/customer');
  }

  public function delete_customer(Request $request, $id)
  {

    $transUser=$request->input();

    $trans = Customer::find($id);

    $trans->update($transUser);

    Session::flash('flash_message', 'Data customer berhasil dihapus!');

    return redirect('admin/customer');
  }
  
  public function customer_autocomplete(Request $request)
  {
    $term = $request->term;

    $results = array();

    $queries = \DB::table('customers')
    ->where('name', 'LIKE', '%'.$term.'%')
    ->orWhere('code', 'LIKE', '%'.$term.'%')
    ->take(25)->get();

    foreach ($queries as $query)
    {
      $results[] = [ 'id' => $query->id, 'name' => $query->name.' - '.$query->address, 'code' => $query->code, 'address' => $query->address ];
    }

    return response()->json($results);
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id)
  {
    //
  }

  private function validation_rules($request)
  {
    $this->validate($request, [
      'code' => 'required|unique:customers',
      'name' => 'required|unique:customers',
      'address' => 'required'
    ]);
  }
}
