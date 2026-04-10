<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\SaleController;
use App\Http\Controllers\API\CashRegisterController;
use App\Http\Controllers\API\InventoryMovementController;
use App\Http\Controllers\API\AlertController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\CreditSaleController;

// ─── Autenticación (sin protección) ────────────────────────────
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // ─── Auth ──────────────────────────────────────────────────────
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // ─── Categorías ───────────────────────────────────────────────
    Route::get('categories/stats', [CategoryController::class, 'stats']);
    Route::apiResource('categories', CategoryController::class);
    Route::get('categories/{category}/products', [CategoryController::class, 'withProducts']);

    // ─── Productos ────────────────────────────────────────────────
    Route::get('products/statistics', [ProductController::class, 'statistics']);
    Route::get('products/stats', [ProductController::class, 'stats']);
    Route::get('products/low-stock', [ProductController::class, 'lowStock']);
    Route::get('products/out-of-stock', [ProductController::class, 'outOfStock']);
    Route::apiResource('products', ProductController::class);

    // ─── Ventas ────────────────────────────────────────────────────
    Route::get('sales/report', [SaleController::class, 'report']);
    Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show']);


    // ─── Cajas Registradoras ───────────────────────────────────────
    Route::get('cash-registers', [CashRegisterController::class, 'index']);
    Route::post('cash-registers/open', [CashRegisterController::class, 'open']);
    Route::post('cash-registers/{cashRegister}/close', [CashRegisterController::class, 'close']);
    Route::get('cash-registers/my-open-register', [CashRegisterController::class, 'myOpenRegister']);
    Route::get('cash-registers/{cashRegister}', [CashRegisterController::class, 'show']);
    Route::get('cash-registers/{cashRegister}/summary', [CashRegisterController::class, 'summary']);

    // ─── Movimientos de Inventario ─────────────────────────────────
    Route::apiResource('inventory-movements', InventoryMovementController::class)->only(['index', 'store', 'show']);
    Route::get('inventory-movements/by-product/{productId}', [InventoryMovementController::class, 'byProduct']);
    Route::get('inventory-movements/summary', [InventoryMovementController::class, 'summary']);

    // ─── Alertas ───────────────────────────────────────────────────
    Route::post('alerts/mark-all-as-read', [AlertController::class, 'markAllAsRead']);
    Route::get('alerts/unread-count', [AlertController::class, 'unreadCount']);
    Route::get('alerts/summary', [AlertController::class, 'summary']);
    Route::delete('alerts/delete-read', [AlertController::class, 'deleteRead']);
    Route::apiResource('alerts', AlertController::class)->only(['index', 'show']);
    Route::post('alerts/{alert}/mark-as-read', [AlertController::class, 'markAsRead']);

    // ─── Usuarios ──────────────────────────────────────────────────
    Route::get('users/me', [UserController::class, 'me']);
    Route::apiResource('users', UserController::class);
    Route::get('users/stats', [UserController::class, 'stats']);

    // ─── Clientes (Sistema de Crédito) ──────────────────────────────
    Route::apiResource('customers', CustomerController::class);
    Route::get('customers/{customer}/credit-history', [CustomerController::class, 'creditHistory']);
    Route::get('customers/{customer}/summary', [CustomerController::class, 'summary']);

    // ─── Ventas a Crédito ──────────────────────────────────────────
    Route::apiResource('credit-sales', CreditSaleController::class)->only(['index', 'store', 'show']);
    Route::post('credit-sales/{creditSale}/payment', [CreditSaleController::class, 'registerPayment']);
    Route::get('credit-sales/{creditSale}/payments', [CreditSaleController::class, 'payments']);
});
