<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MealType;
use App\Services\MealTypeService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MealTypeController extends Controller
{
    protected $service;

    public function __construct(MealTypeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $mealTypes = $this->service->getAll($request);
            return $this->successResponse($mealTypes, 'Berhasil mengambil daftar tipe waktu makan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar tipe waktu makan.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:50',
            ]);

            $mealType = $this->service->create($validated);
            return $this->successResponse($mealType, 'Berhasil membuat tipe waktu makan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat tipe waktu makan.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $mealType = $this->service->findById($id);
            return $this->successResponse($mealType, 'Berhasil mengambil detail tipe waktu makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe waktu makan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail tipe waktu makan.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:50',
            ]);

            $mealType = $this->service->update($id, $validated);
            return $this->successResponse($mealType, 'Berhasil mengubah data tipe waktu makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe waktu makan tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data tipe waktu makan.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus tipe waktu makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe waktu makan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus tipe waktu makan.', 500);
        }
    }
}
