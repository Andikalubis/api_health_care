<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthLimit;
use App\Services\HealthLimitService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class HealthLimitController extends Controller
{
    protected $service;

    public function __construct(HealthLimitService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $limits = $this->service->getAll($request, ['healthType']);
            return $this->successResponse($limits, 'Berhasil mengambil daftar batas kesehatan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar batas kesehatan.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            if (!$request->user()->isAdmin()) {
                return $this->errorResponse('Hanya admin yang dapat mengatur batas kesehatan.', 403);
            }

            $validated = $request->validate([
                'health_type_id' => 'required|exists:health_types,id',
                'warning_min' => 'required|numeric',
                'warning_max' => 'required|numeric',
                'danger_min' => 'required|numeric',
                'danger_max' => 'required|numeric',
            ]);

            $limit = $this->service->create($validated);
            return $this->successResponse($limit, 'Berhasil membuat batas kesehatan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat batas kesehatan.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $healthLimit = $this->service->findById($id);

            if (!$this->service->checkOwnership($healthLimit, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($healthLimit->load('healthType'), 'Berhasil mengambil detail batas kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Batas kesehatan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail batas kesehatan.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            if (!$request->user()->isAdmin()) {
                return $this->errorResponse('Hanya admin yang dapat mengubah batas kesehatan.', 403);
            }

            $healthLimit = $this->service->findById($id);

            if (!$this->service->checkOwnership($healthLimit, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'health_type_id' => 'sometimes|required|exists:health_types,id',
                'warning_min' => 'sometimes|required|numeric',
                'warning_max' => 'sometimes|required|numeric',
                'danger_min' => 'sometimes|required|numeric',
                'danger_max' => 'sometimes|required|numeric',
            ]);

            $healthLimit = $this->service->update($id, $validated);
            return $this->successResponse($healthLimit, 'Berhasil mengubah data batas kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Batas kesehatan tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data batas kesehatan.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            if (!$request->user()->isAdmin()) {
                return $this->errorResponse('Hanya admin yang dapat menghapus batas kesehatan.', 403);
            }

            $healthLimit = $this->service->findById($id);

            if (!$this->service->checkOwnership($healthLimit, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus batas kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Batas kesehatan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus batas kesehatan.', 500);
        }
    }
}
