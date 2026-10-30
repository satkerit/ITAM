<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function view(User $user, Asset $asset): bool
    {
        return $user->canAccessLocation($asset->location_id);
    }

    public function update(User $user, Asset $asset): bool
    {
        return $this->view($user, $asset);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $this->view($user, $asset);
    }
}
