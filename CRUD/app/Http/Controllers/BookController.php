<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookCollection;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): BookCollection
    {
        $limit = 10;
        $page_no = ($request->page * $limit) - $limit;

        $books = Book::query()
            ->offset($page_no)
            ->limit($limit)
            ->orderBy('published_date')
            ->get();

        return new BookCollection($books);

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): BookResource
    {
        $request->validate([
            'book_name' => 'required|max:50',
            'book_cover_url' => 'required|image:jpeg,png,jpg,gif,svg,bmp',
            'published_date' => 'required',
            'have_rating_over_4' => 'required',
        ]);

        if ($request->hasFile('book_cover_url')) {
            $destination_path = 'public/images';
            $book_cover_url = $request->file('book_cover_url');
            $file_name = $book_cover_url->getClientOriginalName();
            $path = $request->file('book_cover_url')->storeAs($destination_path, $file_name);
            $url = Storage::url($path);
        }

        $book = Book::query()->create([
            'user_id' => $request->user()->id,
            'book_name' => $request->get('book_name'),
            'book_cover_url' => $url,
            'published_date' => $request->get('published_date'),
            'description' => $request->get('description'),
            'have_rating_over_4' => $request->get('have_rating_over_4'),
        ]);


        return new BookResource($book);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     */
    public function show($id)
    {
        $book = Book::query()->findOrFail($id);

        if ($book == null) {
            return response([
                'message' => 'Invalid ID'
            ], 422);
        }

        return new BookResource($book);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     */
    public function update(Request $request, $id)
    {
        $book = Book::query()->findOrFail($id)->first();

        if ($book == null) {
            return response([
                'message' => 'Invalid ID'
            ], 422);
        }

        if (Auth::id() != $book['user_id'])
            return response([
                'Message' => 'You can\'t update a book you don\'t publish'
            ], 401);

        $book_name = $request->input('book_name');
        $published_date = $request->input('published_date');
        $description = $request->input('description');
        $have_rating_over_4 = $request->input('have_rating_over_4');

        if ($request->hasFile('book_cover_url')) {
            $destination_path = 'public/images';
            $book_cover_url = $request->file('book_cover_url');
            $file_name = $book_cover_url->getClientOriginalName();
            $path = $request->file('book_cover_url')->storeAs($destination_path, $file_name);
            $url = Storage::url($path);
            $book['book_cover_url'] = $url;
        }


        if ($book_name) {
            $book['book_name'] = $book_name;
        }
        if ($published_date) {
            $book['published_date'] = $published_date;
        }
        if ($description) {
            $book['description'] = $description;
        }
        if ($have_rating_over_4) {
            $book['have_rating_over_4'] = $have_rating_over_4;
        }

        $book->save();


        return new BookResource($book);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     */
    public function destroy($id)
    {
        $book = Book::query()->findOrFail($id)->first();

        if ($book == null) {
            return response([
                'message' => 'Invalid ID'
            ], 422);
        }

        if (Auth::id() != $book['user_id'])
            return response([
                'Message' => 'You can\'t update a book you don\'t publish'
            ], 401);

        $book->delete();

        return new BookResource($book);
    }
}
