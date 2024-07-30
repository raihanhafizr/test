<?php

namespace App\Http\Controllers;

use App\Models\Member;
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
 *     schema="Member",
 *     type="object",
 *     title="Member",
 *     required={"id", "name", "email"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the member"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the member"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         description="Email address of the member"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the member was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the member was last updated"
 *     )
 * )
 */

class MemberController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/members",
     *     summary="Get all members",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Member")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $members = Member::withCount(['borrows' => function ($query) {
            $query->whereNull('returned_at');
        }])->get();
        return response()->json($members);
    }
}
