<?php

namespace App\Trait;

trait FilterByTab
{
    public function filterByTab(string $tab): void
    {
        $this->status = method_exists($this, 'normalizeTab')
            ? $this->normalizeTab($tab)
            : (strtolower($tab) === 'all' ? null : $tab);

        if (isset($this->paginatorInfo['currentPage'])) {
            $this->paginatorInfo['currentPage'] = 1;
        }

        match (true) {
            method_exists($this, 'search')   => $this->search(),
            method_exists($this, 'loadData') => $this->loadData(),
            default                          => null,
        };
    }
}