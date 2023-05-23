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
        $users_list = $query->paginate('10');
        return view('fontend.excel')->with(compact('users_list'));
    }

    public function create()
    {
        return view('backend.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);
    
        return redirect()->route('users.index');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('backend.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|string|max:255',
        ]);
        $userData = $request->only('name', 'email');
        $user->update($userData);
        return redirect()->route('users.index');
    }

    public function destroy($id)
    {
        $article = User::findOrFail($id);
        $article->delete();    
        return back();
    }

    public function exportExcel()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    public function importExcel(Request $request)
    {
        Excel::import(new UsersImport, $request->file('file'));
        return redirect()->route('users.index');
    }
}
