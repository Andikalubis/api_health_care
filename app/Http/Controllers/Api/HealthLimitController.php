<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthLimit;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class HealthLimitController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil daftar batas kesehatan.
     */
    public function index()
    {
        try {
            $limits = HealthLimit::with('healthType')->get();

            return $this->successResponse($limits, 'Berhasil mengambil daftar batas kesehatan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar batas kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan batas kesehatan baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            if ($request->user()->role->value !== 'admin') {
                return $this->errorResponse('Hanya admin yang dapat mengatur batas kesehatan.', 403);
            }

            $validated = $request->validate([
                'health_type_id' => 'required|exists:health_types,id',
                'warning_min' => 'required|numeric',
                'warning_max' => 'required|numeric',
                'danger_min' => 'required|numeric',
                'danger_max' => 'required|numeric',
            ]);

            $limit = HealthLimit::create($validated);

            return $this->successResponse($limit, 'Berhasil membuat batas kesehatan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat batas kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari batas kesehatan berdasarkan ID.
     */
    public function show($id)
    {
        try {
            $healthLimit = HealthLimit::findOrFail($id);

            return $this->successResponse($healthLimit->load('healthType'), 'Berhasil mengambil detail batas kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Batas kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail batas kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data batas kesehatan yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            if ($request->user()->role->value !== 'admin') {
                return $this->errorResponse('Hanya admin yang dapat mengubah batas kesehatan.', 403);
            }

            $healthLimit = HealthLimit::findOrFail($id);

            $validated = $request->validate([
                'health_type_id' => 'sometimes|required|exists:health_types,id',
                'warning_min' => 'sometimes|required|numeric',
                'warning_max' => 'sometimes|required|numeric',
                'danger_min' => 'sometimes|required|numeric',
                'danger_max' => 'sometimes|required|numeric',
            ]);

            $healthLimit->update($validated);

            return $this->successResponse($healthLimit, 'Berhasil mengubah data batas kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Batas kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data batas kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus batas kesehatan dari database.
     */
    public function destroy(Request $request, $id)
    {
        try {
            if ($request->user()->role->value !== 'admin') {
                return $this->errorResponse('Hanya admin yang dapat menghapus batas kesehatan.', 403);
            }

            $healthLimit = HealthLimit::findOrFail($id);
            $healthLimit->delete();

            return $this->successResponse(null, 'Berhasil menghapus batas kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Batas kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus batas kesehatan.', 500, $e->getMessage());
        }
    }
}
