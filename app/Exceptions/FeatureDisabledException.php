<?php

namespace App\Exceptions;

use Exception;

class FeatureDisabledException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     */
    public function render($request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
            ], 403);
        }

        abort(403, $this->getMessage());
    }
}
