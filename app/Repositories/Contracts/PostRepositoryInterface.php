<?php

namespace App\Repositories\Contracts;

interface PostRepositoryInterface
{
    public function paginate(int $perPage = 10, ?string $search = null);
}
