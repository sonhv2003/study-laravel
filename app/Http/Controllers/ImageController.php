<?php

namespace App\Http\Controllers;

use App\Models\Image; 
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function index(Request $request)
    {
        $query = Image::select('id', 'link');
        if($request->has('search')) { $query->where('link', 'like', '%' . $request->search . '%'); }
        $news_list = $query->paginate(4);    
        return view('fontend.images')->with(compact('news_list')); 
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = $request->file('image')->getClientOriginalName();
            $path = $image->storeAs('public/images/upload', $filename);

            $new_image = new Image();
            $new_image->link = '/storage/images/upload/' . $filename;
            $new_image->save();
        }
        return redirect()->route('images.index');
    }

    public function destroy($id)
    {
        $image = Image::findOrFail($id);        
        $image->delete();
        return back();
    }
}
