<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CollectionHelper
{
    public static function paginate(Collection $collection, int $perPage = 15, int $page = null, array $options = [])
    {
        $page = $page ?: request()->get('page', 1);
        $items = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        
        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            array_merge($options, [
                'path' => request()->url(),
                'pageName' => 'page',
            ])
        );
    }
}