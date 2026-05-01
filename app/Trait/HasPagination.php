<?php

namespace App\Trait;

use Illuminate\Pagination\LengthAwarePaginator;

trait HasPagination
{
    public $perPage = 10;
    
    public $paginatorInfo = [
        'currentPage' => 1,
    ];

    private function paginate(LengthAwarePaginator $paginator)
    {
        $this->paginatorInfo = [
            'currentPage' => $paginator->currentPage(),
            'firstItem' => $paginator->firstItem(),
            'lastItem' => $paginator->lastItem(),
            'total' => $paginator->total(),
            'hasMorePages' => $paginator->hasMorePages(),
        ];
    }

    public function updatedPerPage()
    {
        $this->paginatorInfo['currentPage'] = 1;
        $this->mount();
        $this->dispatch('scroll-to-top');
    }

    public function nextPage()
    {
        $this->paginatorInfo['currentPage']++;
        $this->mount();
        $this->dispatch('scroll-to-top');
    }

    public function prevPage()
    {
        $this->paginatorInfo['currentPage']--;
        $this->mount();
        $this->dispatch('scroll-to-top');
    }
}
