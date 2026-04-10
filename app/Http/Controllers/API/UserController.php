<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Listar usuarios
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $perPage = $request->integer('per_page', 20);
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Crear nuevo usuario
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'data' => $user,
        ], 201);
    }

    /**
     * Obtener detalles del usuario actual
     */
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Obtener detalles de un usuario específico
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'password' => ['sometimes', 'min:8'],
            'role' => ['sometimes', auth()->user()->role === 'admin' ? 'in:admin,cajero' : 'prohibited'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'data' => $user,
        ]);
    }

    /**
     * Eliminar usuario
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente',
        ]);
    }

    /**
     * Estadísticas de usuarios
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_usuarios' => User::count(),
            'admins' => User::where('role', 'admin')->count(),
            'cajeros' => User::where('role', 'cajero')->count(),
        ];

        return response()->json($stats);
    }
}
