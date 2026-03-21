<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Services\MedicineService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MedicineController extends Controller
{
    protected $service;

    public function __construct(MedicineService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $medicines = $this->service->getAll($request);
            return $this->successResponse($medicines, 'Berhasil mengambil daftar obat.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar obat.', 500);
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
                'description' => 'nullable|string',
            ]);

            $medicine = $this->service->create($validated);
            return $this->successResponse($medicine, 'Berhasil membuat obat baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat obat baru.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $medicine = $this->service->findById($id);
            return $this->successResponse($medicine, 'Berhasil mengambil detail obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Obat tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail obat.', 500);
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
                'description' => 'nullable|string',
            ]);

            $medicine = $this->service->update($id, $validated);
            return $this->successResponse($medicine, 'Berhasil mengubah data obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Obat tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data obat.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Obat tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus obat.', 500);
        }
    }
}
