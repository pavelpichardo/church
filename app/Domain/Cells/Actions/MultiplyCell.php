<?php

namespace App\Domain\Cells\Actions;

use App\Models\Cell;
use App\Support\Enums\CellStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MultiplyCell
{
    /**
     * Multiply a cell: the assistant becomes leader of the new child cell,
     * selected members move to the child, a new assistant is assigned to the parent,
     * and both cells remain active to keep growing.
     *
     * @param  Cell   $parentCell
     * @param  array  $data  Keys: name, host_id, address_line1, address_line2?, city?, state?,
     *                       postal_code?, child_assistant_id?, parent_new_assistant_id?,
     *                       meeting_day?, meeting_time?, member_ids (person IDs to move)
     */
    public function handle(Cell $parentCell, array $data): Cell
    {
        if (!$parentCell->assistant_id) {
            throw ValidationException::withMessages([
                'assistant' => 'La célula debe tener un asistente para poder multiplicarse.',
            ]);
        }

        return DB::transaction(function () use ($parentCell, $data) {
            // Create new cell — the parent's assistant becomes the leader
            $newCell = Cell::create([
                'name' => $data['name'],
                'leader_id' => $parentCell->assistant_id,
                'assistant_id' => $data['child_assistant_id'] ?? null,
                'host_id' => $data['host_id'] ?? null,
                'address_line1' => $data['address_line1'],
                'address_line2' => $data['address_line2'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'status' => CellStatus::Active,
                'parent_cell_id' => $parentCell->id,
                'max_capacity' => $parentCell->max_capacity,
                'meeting_day' => $data['meeting_day'] ?? $parentCell->meeting_day?->value,
                'meeting_time' => $data['meeting_time'] ?? $parentCell->meeting_time,
                'created_by' => Auth::id(),
            ]);

            // Move selected members to new cell
            $memberIds = $data['member_ids'] ?? [];
            if (!empty($memberIds)) {
                $now = now();

                // Mark members as left in parent cell
                $parentCell->members()
                    ->wherePivotIn('person_id', $memberIds)
                    ->wherePivotNull('left_at')
                    ->each(function ($member) use ($now) {
                        $member->pivot->update(['left_at' => $now]);
                    });

                // Add members to new cell
                $pivotData = [];
                foreach ($memberIds as $personId) {
                    $pivotData[$personId] = ['joined_at' => $now];
                }
                $newCell->members()->attach($pivotData);
            }

            // Assign new assistant to parent cell (old assistant is now leader of child)
            $parentCell->update([
                'assistant_id' => $data['parent_new_assistant_id'] ?? null,
            ]);

            return $newCell;
        });
    }
}
