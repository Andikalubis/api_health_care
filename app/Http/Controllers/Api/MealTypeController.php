<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MealType;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MealTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil semua daftar tipe waktu makan.
     */
    public function index()
    {
        try {
            $mealTypes = MealType::all();
            return $this->successResponse($mealTypes, 'Berhasil mengambil daftar tipe waktu makan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar tipe waktu makan.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan tipe waktu makan baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:50',
            ]);

            $mealType = MealType::create($validated);

            return $this->successResponse($mealType, 'Berhasil membuat tipe waktu makan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat tipe waktu makan.', 500);
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari satu tipe waktu makan berdasarkan ID.
     */
    public function show($id)
    {
        try {
            $mealType = MealType::findOrFail($id);

            return $this->successResponse($mealType, 'Berhasil mengambil detail tipe waktu makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe waktu makan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail tipe waktu makan.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data tipe waktu makan yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $mealType = MealType::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:50',
            ]);

            $mealType->update($validated);

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
     * Menghapus tipe waktu makan dari database.
     */
    public function destroy($id)
    {
        try {
            $mealType = MealType::findOrFail($id);
            $mealType->delete();

            return $this->successResponse(null, 'Berhasil menghapus tipe waktu makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Tipe waktu makan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus tipe waktu makan.', 500);
        }
    }
}
