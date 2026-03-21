<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class BaseService
{
    protected $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records with ownership filtering and relations.
     */
    public function getAll(Request $request, array $load = []): Collection
    {
        $query = $this->model->newQuery();

        if (!empty($load)) {
            $query->with($load);
        }

        $this->applyOwnershipFilter($query, $request->user());

        return $query->get();
    }

    /**
     * Find a record by ID.
     */
    public function findById($id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new record.
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing record.
     */
    public function update($id, array $data): Model
    {
        $record = $this->findById($id);
        $record->update($data);
        return $record;
    }

    /**
     * Delete a record.
     */
    public function delete($id): bool
    {
        $record = $this->findById($id);
        return $record->delete();
    }

    /**
     * Apply ownership filter to the query.
     */
    protected function applyOwnershipFilter($query, $user)
    {
        if (!$user->isAdmin() && isset($this->model->user_id)) {
            $query->where('user_id', $user->id);
        }
    }

    /**
     * Check if the user is the owner of the record.
     */
    public function checkOwnership($record, $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Standard user_id check
        if (isset($record->user_id) && $record->user_id === $user->id) {
            return true;
        }

        if (isset($record->patient_id)) {
            $patient = $record->patient;
            if ($patient && $patient->user_id === $user->id) {
                return true;
            }
        }

        return false;
    }
}
