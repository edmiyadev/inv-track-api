<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
trait Authorizes
{
    public function authorize($ability, $arguments = [])
    {

        if (!Gate::allows($ability, $arguments)) {
            abort(403, 'This action is unauthorized.');
        }
    }
}
