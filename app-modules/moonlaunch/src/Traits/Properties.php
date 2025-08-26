<?php

namespace Modules\Moonlaunch\Traits;

use MoonShine\Support\Enums\PageType;

trait Properties
{
    /**
     * Setter genérico
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

    protected function async(bool $isAsync): static
    {
        return $this->set('isAsync', $isAsync);
    }

    protected function precognitive(bool $precognitive): static
    {
        return $this->set('isPrecognitive', $precognitive);
    }

    protected function redirectAfterSave(PageType $redirectAfterSave): static
    {
        return $this->set('redirectAfterSave', $redirectAfterSave);
    }

    // ─────────────────────────────────────────────
    // 🔹 Modal
    // ─────────────────────────────────────────────

    protected function createInModal(bool $createInModal = true): static
    {
        return $this->set('createInModal', $createInModal);
    }

    protected function editInModal(bool $editInModal = true): static
    {
        return $this->set('editInModal', $editInModal);
    }

    protected function detailInModal(bool $detailInModal = true): static
    {
        return $this->set('detailInModal', $detailInModal);
    }

    protected function allInModal(): static
    {
        return $this
            ->createInModal(true)
            ->editInModal(true)
            ->detailInModal(true);
    }

    // ─────────────────────────────────────────────
    // 🔹 UI / Table
    // ─────────────────────────────────────────────

    protected function columnSelection(bool $columnSelection = true): static
    {
        return $this->set('columnSelection', $columnSelection);
    }

    protected function stickyTable(bool $stickyTable = true): static
    {
        return $this->set('stickyTable', $stickyTable);
    }

    protected function errorsAbove(bool $errorsAbove = true): static
    {
        return $this->set('errorsAbove', $errorsAbove);
    }

    // ─────────────────────────────────────────────
    // 🔹 Pagination
    // ─────────────────────────────────────────────

    protected function itemsPerPage(int $itemsPerPage): static
    {
        return $this->set('itemsPerPage', $itemsPerPage);
    }
}
