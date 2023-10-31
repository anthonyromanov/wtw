<?php

namespace App\Services\MovieFinder;

interface RemoteRepositoryInterface
{
    public function find(string $imdbId);
}