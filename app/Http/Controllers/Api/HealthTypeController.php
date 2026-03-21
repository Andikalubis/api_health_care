<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthType;
use App\Services\HealthTypeService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class HealthTypeController extends Controller
{
    protected $service;

    public function __construct(HealthTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $healthTypes = $this->service->getAll($request);
            return $this->successResponse($healthTypes, 'Berhasil mengambil daftar tipe kesehatan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar tipe kesehatan.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
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

            $healthType = $this->service->create($validated);
            return $this->successResponse($healthType, 'Berhasil membuat tipe kesehatan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat tipe kesehatan.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $healthType = $this->service->findById($id);
            return $this->successResponse($healthType, 'Berhasil mengambil detail tipe kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe kesehatan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail tipe kesehatan.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:100',
                'unit' => 'sometimes|required|string|max:20',
                'normal_min' => 'sometimes|required|numeric',
                'normal_max' => 'sometimes|required|numeric',
                'description' => 'nullable|string',
            ]);

            $healthType = $this->service->update($id, $validated);
            return $this->successResponse($healthType, 'Berhasil mengubah data tipe kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe kesehatan tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data tipe kesehatan.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus tipe kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe kesehatan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus tipe kesehatan.', 500);
        }
    }
}
