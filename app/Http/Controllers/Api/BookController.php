<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Http\Resources\BookResource;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    public function index(Request $request)
    {
        // Validasi menggunakan Validator dengan pesan kustom
        $validator = Validator::make($request->all(), [
            'paginate' => 'nullable|integer|min:1|max:10',
            'sort' => 'nullable|in:asc,desc',
        ], [
            'paginate.integer' => 'The paginate field must be an integer.',
            'paginate.min' => 'The paginate field must be a positive integer greater than or equal to 1.',
            'paginate.max' => 'The paginate field must not be greater than 10.',
            'sort.in' => 'The sort field must be either "asc" or "desc".',
        ]);
    
        // Jika validasi gagal, return response dengan error message
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'data' => [
                    'errors' => $validator->errors()
                ]
            ], 422); // HTTP Status 422 - Unprocessable Entity
        }
    
        // Proses query jika validasi berhasil
        $query = Book::query();
    
        if ($request->has('sort')) {
            $query->orderBy('year', $request->sort);
        }
    
        $books = $query->paginate($request->paginate ?? 5); // Default 5 if paginate is not provided
    
        return response()->json([
            'status' => 'success',
            'message' => 'Books retrieved successfully',
            'data' => BookResource::collection($books),
        ]);
    }

    // Endpoint: GET /api/books/search?title=The Hobbit
    public function search(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
        ]);

        $books = Book::where('title', 'like', '%' . $request->title . '%')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Search results for title',
            'data' => BookResource::collection($books),
        ]);
    }

    // Endpoint: GET /api/books/filter/year?year=1997
    public function filterByYear(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
        ]);

        $books = Book::where('year', $request->year)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Books filtered by year',
            'data' => BookResource::collection($books),
        ]);
    }

    // Endpoint: GET /api/books/filter?publisher=Gramedia&author=Andrea Hirata
    public function filterByPublisherAndAuthor(Request $request)
    {
        $request->validate([
            'publisher' => 'required|string',
            'author' => 'required|string',
        ]);

        $books = Book::where('publisher', $request->publisher)
                     ->where('author', $request->author)
                     ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Books filtered by publisher and author',
            'data' => BookResource::collection($books),
        ]);
    }

    // Endpoint: GET /api/books/filter/year-range?from=2000&to=2010
    public function filterByYearRange(Request $request)
    {
        $request->validate([
            'from' => 'required|integer',
            'to' => 'required|integer|gte:from',
        ]);

        $books = Book::whereBetween('year', [$request->from, $request->to])->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Books filtered by year range',
            'data' => BookResource::collection($books),
        ]);
    }

    public function sortByYear(Request $request)
    {
    $order = $request->query('order', 'asc'); // default asc

    $books = Book::orderBy('year', $order)->get();

    return response()->json([
        'success' => true,
        'message' => 'Books sorted by year',
        'data' => $books
    ]);
    }
}