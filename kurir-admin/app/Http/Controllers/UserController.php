<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\Branch;

use Session;

use Yajra\Datatables\Datatables;

class UserController extends Controller
{
  public function index()
  {
    return view('users.index');
  }

  public function user_data()
  {
     \DB::statement(\DB::raw('set @rownum=0'));
   
    $users = \DB::table('users')
    ->join('branches', 'users.branch_id', '=', 'branches.id')
    ->select([\DB::raw('@rownum  := @rownum  + 1 AS rownum'),
      'users.id as userid',
      'branches.name as branch_id',
      'users.name',
      'email',
      'users.address',
      'users.phone'
    ]);
    return Datatables::of($users)
    ->addColumn('action', function ($user) {
      return '<a href="./user/edit/'.$user->userid.'" class="btn btn-xs btn-primary"><i class="glyphicon glyphicon-edit"></i> Edit</a>
      <button id="btn-delete" class="btn btn-xs btn-danger" data-remote="./user/destroy/'.$user->userid . '">Delete</button>
      ';
    })
    ->make(true);
  }

  public function create()
  {
    return view('users.create');
  }

  public function store(Request $request)
  {
    $this->validation_rules($request);

    $password = bcrypt($request->input('password'));
    $request->merge(array('password'=>$password));

    $user=$request->input();
    User::create($user);

    Session::flash('flash_message', 'Data pengguna berhasil ditambahkan!');

    return redirect('admin/users');
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
    $user=User::find($id);

    if ($user->id == 14111) {
      return redirect('admin/users');
    } 
    else 
    { 
      $branch = Branch::where('id', '=', $user->branch_id)->first();
      return view('users.edit',compact(['user','branch']));
    }

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
    $this->update_validation_rules($request);

    $password = bcrypt($request->input('password'));
    $request->merge(array('password'=>$password));


    $userUpdate=$request->input();

    $user=User::find($id);
    $user->update($userUpdate);

    Session::flash('flash_message', 'Data pengguna berhasil diupdate!');

    return redirect('admin/users');
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id)
  {
    $data = User::findOrFail($id);
    if ($data->id == 14111) {
      return redirect('admin/users');
    } 
    else 
    { 
    $data->delete();
    Session::flash('flash_message', 'Data berhasil dihapus');
    }

    return redirect()->back();
  }

  private function validation_rules($request)
  {
    $this->validate($request, [
      'name' => 'required',
      'email' => 'required|unique:users',
      'password' => 'required'
    ]);
  }

  private function update_validation_rules($request)
  {
    $this->validate($request, [
      'name' => 'required',
      'email' => 'required',
      'password' => 'required'
    ]);
  }
}
