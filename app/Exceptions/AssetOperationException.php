<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssetOperationException extends Exception
{
    /**
     * Balas JSON 422 untuk API, atau kembali ke halaman sebelumnya dengan pesan error untuk web.
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json(['message' => $this->getMessage()], 422);
        }

        return back()->withInput()->with('error', $this->getMessage());
    }
}
