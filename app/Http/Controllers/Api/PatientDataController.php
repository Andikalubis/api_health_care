<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PatientData;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class PatientDataController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil daftar data pasien.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = PatientData::with('user');

            if ($user->role->value !== 'admin') {
                $query->where('user_id', $user->id);
            }

            $patientData = $query->get();

            return $this->successResponse($patientData, 'Berhasil mengambil daftar data pasien.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar data pasien.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan data pasien baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'name' => 'required|string|max:100',
                'gender' => 'required|string|max:10',
                'birth_date' => 'required|date',
                'height' => 'required|numeric',
                'weight' => 'required|numeric',
                'blood_type' => 'required|string|max:5',
            ]);

            $patientData = PatientData::create($validated);

            return $this->successResponse($patientData, 'Berhasil membuat data pasien baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat data pasien.', 500);
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari data pasien berdasarkan ID.
     */
    public function show(Request $request, $id)
    {
        try {
            $patientData = PatientData::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $patientData->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($patientData->load('user', 'healthChecks', 'vitalSigns', 'medicineSchedules', 'mealSchedules'), 'Berhasil mengambil detail data pasien.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Data pasien tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail data pasien.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data pasien yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $patientData = PatientData::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $patientData->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'name' => 'sometimes|required|string|max:100',
                'gender' => 'sometimes|required|string|max:10',
                'birth_date' => 'sometimes|required|date',
                'height' => 'sometimes|required|numeric',
                'weight' => 'sometimes|required|numeric',
                'blood_type' => 'sometimes|required|string|max:5',
            ]);

            $patientData->update($validated);

            return $this->successResponse($patientData, 'Berhasil mengubah data pasien.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Data pasien tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data pasien.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus data pasien dari database.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $patientData = PatientData::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $patientData->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $patientData->delete();

            return $this->successResponse(null, 'Berhasil menghapus data pasien.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Data pasien tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus data pasien.', 500);
        }
    }
}
