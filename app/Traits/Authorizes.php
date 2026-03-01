<?php

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

trait Authorizes
{
    public function authorize($ability, $arguments = [])
    {
        if (empty($arguments) || $arguments === null) {
            if (! Gate::allows($ability)) {
                throw new AuthorizationException('This action is unauthorized.');
            }

            return;
        }

        if (! Gate::allows($ability, $arguments)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
