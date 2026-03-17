<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil semua daftar obat.
     */
    public function index()
    {
        try {
            $medicines = Medicine::all();
            return $this->successResponse($medicines, 'Berhasil mengambil daftar obat.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar obat.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan obat baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
            ]);

            $medicine = Medicine::create($validated);

            return $this->successResponse($medicine, 'Berhasil membuat obat baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat obat baru.', 500);
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari satu obat berdasarkan ID.
     */
    public function show($id)
    {
        try {
            $medicine = Medicine::findOrFail($id);

            return $this->successResponse($medicine, 'Berhasil mengambil detail obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Obat tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail obat.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data obat yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $medicine = Medicine::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:100',
                'description' => 'nullable|string',
            ]);

            $medicine->update($validated);

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
     * Menghapus obat dari database.
     */
    public function destroy($id)
    {
        try {
            $medicine = Medicine::findOrFail($id);
            $medicine->delete();

            return $this->successResponse(null, 'Berhasil menghapus obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Obat tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus obat.', 500);
        }
    }
}
