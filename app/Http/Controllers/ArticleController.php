<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Article;
 
class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::select('id', 'title', 'author', 'category', 'description','image', 'updated_at');
    
        $filters = ['title', 'author', 'category'];
        foreach($filters as $key => $val)
        {
            if($request->has($val)) { $query->where($val, 'like', '%' . $request->$val . '%'); }
        }
        $news_list = $query->paginate(4);    
        return view('fontend.articles')->with(compact('news_list')); 
    }

    public function create()
    {
        return view('backend.articles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'author'     => 'required|string|max:255',
            'category'        => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image',
        ]);

        $article = new Article();
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = $request->file('image')->getClientOriginalName();
            $path = $image->storeAs('public/images/articles', $filename);
            $article->image = '/storage/images/articles/' . $filename;
        }

        $article->title = $request->title;
        $article->author = $request->author;
        $article->category = $request->category;
        $article->description = $request->description;
        $article->save();

        return redirect()->route('articles.index');
    }

    public function show(Article $article)
    {
        //
    }

    public function edit($id)
    {
        $news = Article::findOrFail($id);
        return view('backend.articles.edit', compact('news'));
    }

    public function update(Request $request, $id) 
    {
        $article = Article::findOrFail($id);

        $request->validate([
            'title'         => 'required|string|max:255',
            'author'        => 'required|string|max:255',
            'category'      => 'required|string|max:255',
            'description'   => 'required|string',
            'image'         => 'nullable|image',
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = $request->file('image')->getClientOriginalName();
            $path = $image->storeAs('public/images/articles', $filename);
            $article->image = '/storage/images/articles/' . $filename;
        }

        $article->update([
            'title'         => $request->title,
            'author'        => $request->author,
            'category'      => $request->category,
            'image'         => $article->image,
            'description'   => $request->description
        ]);

        return redirect()->route('articles.index');
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);        
        $article->delete();
        return back();
    }
}
