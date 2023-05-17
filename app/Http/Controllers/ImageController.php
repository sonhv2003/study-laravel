<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function index(Request $request)
    {
        $query = Image::select('id', 'link');
        $news_list = $query->paginate(4);    
        // dd($news_list);die;
        return view('fontend.images')->with(compact('news_list')); 
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
        ]);

        $image = new Image();
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = $request->file('image')->getClientOriginalName();
            $path = $image->storeAs('public/images/upload', $filename);
            $image->link = '/storage/images/upload/' . $filename;
        }
        return redirect()->route('images.index');
    }

    public function destroy($id)
    {
    }
}
