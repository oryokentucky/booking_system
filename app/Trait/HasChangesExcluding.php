<?php

namespace App\Trait;

trait HasChangesExcluding
{
    /**
     * Update the model and return the diff of changes
     *
     * @param  array  $data  Data to update
     * @param  array  $exclude  Fields to exclude from diff
     * @return array|null Returns ['old' => [...], 'new' => [...]] or null if no changes
     */
    public function changesExcluding(array $data, array $exclude = ['updated_at', 'created_at']): ?array
    {
        $originalValues = $this->getOriginal();

        $this->update($data);

        $changes = collect($this->getChanges())->except($exclude)->toArray();

        if (empty($changes)) {
            return null;
        }

        $old = [];
        foreach (array_keys($changes) as $key) {
            $old[$key] = $originalValues[$key] ?? null;
        }

        return [
            'old' => $old,
            'new' => $changes,
        ];
    }
}
