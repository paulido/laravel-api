<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", format="int64"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string"),
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Get list of users",
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/User"))
     *     ),
     * )
     */
    public function index(Request $request)
    {

        // Vérifie si le paramètre 'all' est présent dans la requête
        $getAllUsers = $request->query('all', false);  // Par défaut, c'est false (paginer)

        if ($getAllUsers) {
            // Récupérer tous les utilisateurs sans pagination
            $users = User::orderBy('created_at', 'desc')->get();
        } else {
            // Récupérer les utilisateurs avec pagination (par exemple, 10 par page)
            $users = User::orderBy('created_at', 'desc')->paginate(25);
        }

        return response()->json([
            "status" => 200,
            "data" => ['users' => $users]
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Get a user by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User found",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(response=404, description="User not found")
     * )
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            "status" => 200,
            "data" => ['user' => $user]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
     */
    public function store(Request $request)
    {

        $request->validate(['email' => 'required|email|unique:users,email|max:255']);
        $user = User::create($request->all());
        return response()->json([
            "status" => 201,
            "message" => 'Request successful',
            "data" => ['user' => $user]
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Update a user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());
        return response()->json([
            "satus" => 200,
            "message" => 'Request successful',
            "data" => ['user' => $user]
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="User deleted")
     * )
     */
    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json([
            "status" => 200,
            "message" => 'User deleted successfully',
        ], 200);
    }
}
