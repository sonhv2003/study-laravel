<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Article;
 
class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::select('id', 'title', 'author', 'category', 'description', 'image', 'updated_at');   
        $filters = ['title', 'author', 'category'];
        foreach ($filters as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, 'like', '%' . $request->input($filter) . '%');
            }
        }
        $news_list = $query->paginate(4);    
        return view('fontend.articles')->with('news_list', $news_list);
    }

    public function create()
    {
        return view('backend.articles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'required|image',
        ]);
    
        $filename = $request->file('image')->getClientOriginalName();
        $path = $request->file('image')->storeAs('public/images/articles', $filename);
    
        Article::create([
            'title' => $request->title,
            'author' => $request->author,
            'category' => $request->category,
            'description' => $request->description,
            'image' => '/storage/images/articles/' . $filename,
        ]);
    
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
    
        $articleData = $request->only('title', 'author', 'category', 'description');
    
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = $image->getClientOriginalName();
            $path = $image->storeAs('public/images/articles', $filename);
            $articleData['image'] = '/storage/images/articles/' . $filename;
        }
    
        $article->update($articleData);
    
        return redirect()->route('articles.index');
    }

    public function destroy($id)
    {
        $article = Article::findOrFail($id);
        $imagePath = public_path($article->image);   
        if (file_exists($imagePath)) { unlink($imagePath); }
        $article->delete();    
        return redirect()->route('articles.index');
    }
}
