<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Member;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BorrowController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/borrow",
     *     summary="Borrow a book",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="book_id", type="integer"),
     *             @OA\Property(property="member_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book borrowed successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function borrow(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'member_id' => 'required|exists:members,id',
        ]);

        $book = Book::find($validated['book_id']);
        $member = Member::find($validated['member_id']);

        // Cek apakah member sedang dalam masa penalti
        if ($member->isPenalized()) {
            return response()->json(['message' => 'Anda sedang dalam masa penalti dan tidak dapat meminjam buku'], 400);
        }

        // Cek apakah member sudah meminjam lebih dari 2 buku
        if ($member->borrows()->whereNull('returned_at')->count() >= 2) {
            return response()->json(['message' => 'Anda tidak dapat meminjam lebih dari 2 buku pada saat yang sama'], 400);
        }

        // Cek apakah buku tersedia
        if ($book->stock > 0) {
            // Kurangi stok buku
            $book->stock -= 1;
            $book->save();

            // Catat peminjaman
            Borrow::create([
                'book_id' => $book->id,
                'member_id' => $member->id,
                'borrowed_at' => now(),
            ]);

            return response()->json([
                'message' => 'Buku berhasil dipinjam',
                'book' => $book,
                'member' => $member,
            ]);
        } else {
            return response()->json(['message' => 'Buku tidak tersedia'], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/return",
     *     summary="Return a book",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="borrow_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Book returned successfully"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function return(Request $request)
    {
        $request->validate([
            'borrow_id' => 'required|exists:borrows,id',
        ]);

        $borrow = Borrow::findOrFail($request->borrow_id);

        if ($borrow->returned_at) {
            return response()->json(['message' => 'Buku sudah dikembalikan'], 400);
        }

        $borrowedAt = Carbon::parse($borrow->borrowed_at);
        $now = Carbon::now();

        // Cek apakah peminjaman lebih dari 7 hari
        $diffInDays = $borrowedAt->diffInDays($now);

        if ($diffInDays > 7) {
            $borrow->member->penalty_end_at = $now->addDays(3);
            $borrow->member->save(['timestamps' => false]); // Menyimpan tanpa timestamps
            $message = 'Anda terkena penalty selama 3 hari karena keterlambatan';
        } else {
            $message = 'Buku berhasil dikembalikan tepat waktu';
        }

        $borrow->returned_at = $now;
        $borrow->save();

        // Kembalikan stok buku
        $borrow->book->stock += 1;
        $borrow->book->save();

        return response()->json(['message' => $message]);
    }
}
