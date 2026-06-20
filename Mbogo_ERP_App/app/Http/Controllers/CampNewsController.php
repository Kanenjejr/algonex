<?php

namespace App\Http\Controllers;

use App\Models\CampNew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class CampNewsController extends Controller
{
    public function index()
    {
        $news = CampNew::orderByDesc('created_at')->get();

        return view('admin.news', compact('news'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'publish_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after_or_equal:publish_at',
                'status' => 'required|in:draft,published,archived',
            ]);

            $data = $request->only([
                'title',
                'content',
                'publish_at',
                'expires_at',
                'status',
            ]);

            // store file in public/news
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $destination = public_path('news');

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                $image->move($destination, $imageName);
                $data['image'] = 'news/' . $imageName;
            }

            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            CampNew::create($data);

            Alert::success('Success', 'News created successfully');
            return redirect()->route('news.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to create news');
            return back()->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $realId = decrypt($id);
            $news = CampNew::findOrFail($realId);

            $request->validate([
                'title' => 'required|string|max:255',
                'content' => 'required|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
                'publish_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after_or_equal:publish_at',
                'status' => 'required|in:draft,published,archived',
            ]);

            $data = $request->only([
                'title',
                'content',
                'publish_at',
                'expires_at',
                'status',
            ]);

            // store file in public/news
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $destination = public_path('news');

                if (!file_exists($destination)) {
                    mkdir($destination, 0755, true);
                }

                $image->move($destination, $imageName);
                $data['image'] = 'news/' . $imageName;
            }

            $data['updated_by'] = Auth::id();

            $news->update($data);

            Alert::success('Success', 'News updated successfully');
            return redirect()->route('news.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to update news');
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $realId = decrypt($id);
            $news = CampNew::findOrFail($realId);

            $news->delete();

            Alert::success('Success', 'News deleted successfully');
            return redirect()->route('news.index');
        } catch (\Throwable $e) {
            Alert::error('Error', 'Failed to delete news');
            return back();
        }
    }
}