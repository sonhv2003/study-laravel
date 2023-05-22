<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index()
    {
        $query = User::select('id','name','email')->orderByDesc('id');
        $users_list = $query->paginate('4');
        return view('fontend.excel')->with(compact('users_list'));
    }

    public function exportUser()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function importUser(Request $request)
    {
        // dd($request->file('file'));die;
        Excel::import(new UsersImport, $request->file('file'));
        return view('fontend.excel');
    }
}
