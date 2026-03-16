<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class HealthTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil semua daftar tipe kesehatan.
     */
    public function index()
    {
        try {
            $healthTypes = HealthType::all();
            return $this->successResponse($healthTypes, 'Berhasil mengambil daftar tipe kesehatan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar tipe kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan tipe kesehatan baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'unit' => 'required|string|max:20',
                'normal_min' => 'required|numeric',
                'normal_max' => 'required|numeric',
                'description' => 'nullable|string',
            ]);

            $healthType = HealthType::create($validated);

            return $this->successResponse($healthType, 'Berhasil membuat tipe kesehatan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat tipe kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari satu tipe kesehatan berdasarkan ID.
     */
    public function show($id)
    {
        try {
            $healthType = HealthType::findOrFail($id);

            return $this->successResponse($healthType, 'Berhasil mengambil detail tipe kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail tipe kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data tipe kesehatan yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $healthType = HealthType::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:100',
                'unit' => 'sometimes|required|string|max:20',
                'normal_min' => 'sometimes|required|numeric',
                'normal_max' => 'sometimes|required|numeric',
                'description' => 'nullable|string',
            ]);

            $healthType->update($validated);

            return $this->successResponse($healthType, 'Berhasil mengubah data tipe kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data tipe kesehatan.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus tipe kesehatan dari database.
     */
    public function destroy($id)
    {
        try {
            $healthType = HealthType::findOrFail($id);
            $healthType->delete();

            return $this->successResponse(null, 'Berhasil menghapus tipe kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe kesehatan tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus tipe kesehatan.', 500, $e->getMessage());
        }
    }
}
