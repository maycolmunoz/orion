<?php

declare(strict_types=1);

namespace Modules\MoonLaunch\Traits;

use MoonShine\Support\Enums\PageType;

trait WithProperties
{
    /**
     * Setter
     */
    protected function set(string $property, mixed $value): static
    {
        $this->{$property} = $value;

        return $this;
    }

    // ─────────────────────────────────────────────
    // 🔹 Configuration
    // ─────────────────────────────────────────────

    protected function title(string $title): static
    {
        return $this->set('title', $title);
    }

    protected function setAlias(string $alias): static
    {
        return $this->set('alias', $alias);
    }

    protected function with(array $with): static
    {
        return $this->set('with', $with);
    }

    protected function column(string $column): static
    {
        return $this->set('column', $column);
    }

    protected function sortColumn(string $sortColumn): static
    {
        return $this->set('sortColumn', $sortColumn);
    }

    protected function sortDirection(string $sortDirection): static
    {
        return $this->set('sortDirection', $sortDirection);
    }

    protected function async(bool $isAsync): static
    {
        return $this->set('isAsync', $isAsync);
    }

    protected function redirectAfterSave(PageType $redirectAfterSave): static
    {
        return $this->set('redirectAfterSave', $redirectAfterSave);
    }

    protected function saveQueryState(): static
    {
        return $this->set('saveQueryState', true);
    }

    // ─────────────────────────────────────────────
    // 🔹 Modal
    // ─────────────────────────────────────────────

    protected function createInModal(): static
    {
        return $this->set('createInModal', true);
    }

    protected function editInModal(): static
    {
        return $this->set('editInModal', true);
    }

    protected function detailInModal(): static
    {
        return $this->set('detailInModal', true);
    }

    protected function allInModal(): static
    {
        return $this
            ->createInModal()
            ->editInModal()
            ->detailInModal();
    }

    // ─────────────────────────────────────────────
    // 🔹 Pagination
    // ─────────────────────────────────────────────

    protected function itemsPerPage(int $itemsPerPage): static
    {
        return $this->set('itemsPerPage', $itemsPerPage);
    }

    protected function cursorPaginate(): static
    {
        return $this->set('cursorPaginate', true);
    }

    protected function simplePaginate(): static
    {
        return $this->set('simplePaginate', true);
    }
}
