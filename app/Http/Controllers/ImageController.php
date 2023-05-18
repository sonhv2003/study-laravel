<?php

namespace App\Http\Controllers;

use App\Models\Image; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

            Image::create([
                'link' => '/storage/images/upload/' . $filename,
            ]);
        }
        return back();
    }
    
    public function destroy($id)
    {
        $image = Image::findOrFail($id);
        $imagePath = 'public' . $image->link;
        $imagePath = str_replace('/storage','',$imagePath);
        if (Storage::exists($imagePath)) { Storage::delete($imagePath); }
        $image->delete();
        return back();
    }
}
