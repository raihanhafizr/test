<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="Library Management System API",
 *     version="1.0.0",
 *     description="API untuk sistem perpustakaan"
 * )
 * @OA\Server(
 *     url="http://127.0.0.1:8001",
 *     description="Local server"
 * )
 */

/**
 * @OA\Schema(
 *     schema="Book",
 *     type="object",
 *     @OA\Property(
 *         property="code",
 *         type="string",
 * 
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *       
 *     ),
 *     @OA\Property(
 *         property="author",
 *         type="string",
 *       
 *     ),
 *     @OA\Property(
 *         property="stock",
 *         type="integer",
 *         
 *     )
 * )
 */

class BookController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/books",
     *     summary="Get all books",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Book")
     *         )
     *     )
     * )
     */
    public function index()
    {
        // Memanggil semua buku dengan relasi borrows
        $books = Book::with('borrows')->get();

        return response()->json($books);
    }
}
