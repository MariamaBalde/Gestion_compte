<?php

namespace App\Exceptions;

use Exception;

class CompteNotFoundException extends Exception
{
    public function render()
    {
        return response()->json([
            'success' => false,
            'message' => 'Compte non trouvé',
        ], 404);
    }
}
