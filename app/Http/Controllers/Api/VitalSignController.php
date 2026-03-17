<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VitalSign;
use App\Models\PatientData;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class VitalSignController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil daftar tanda-tanda vital.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = VitalSign::with('patient');

            if ($user->role->value !== 'admin') {
                $query->whereHas('patient', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $vitalSigns = $query->get();

            return $this->successResponse($vitalSigns, 'Berhasil mengambil daftar tanda-tanda vital.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar tanda-tanda vital.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan data tanda-tanda vital baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patient_data,id',
                'blood_pressure' => 'required|string|max:20',
                'heart_rate' => 'required|integer',
                'body_temperature' => 'required|numeric',
                'breathing_rate' => 'required|integer',
                'oxygen_level' => 'required|integer',
                'check_time' => 'required|date',
            ]);

            if ($request->user()->role->value !== 'admin') {
                $patient = PatientData::find($validated['patient_id']);
                if ($patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk menambahkan data pada pasien ini.', 403);
                }
            }

            $vitalSign = VitalSign::create($validated);

            return $this->successResponse($vitalSign, 'Berhasil membuat data tanda-tanda vital baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat data tanda-tanda vital.', 500);
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari data tanda-tanda vital berdasarkan ID.
     */
    public function show(Request $request, $id)
    {
        try {
            $vitalSign = VitalSign::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $vitalSign->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($vitalSign->load('patient'), 'Berhasil mengambil detail data tanda-tanda vital.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Data tanda-tanda vital tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail data tanda-tanda vital.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data tanda-tanda vital yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $vitalSign = VitalSign::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $vitalSign->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'patient_id' => 'sometimes|required|exists:patient_data,id',
                'blood_pressure' => 'sometimes|required|string|max:20',
                'heart_rate' => 'sometimes|required|integer',
                'body_temperature' => 'sometimes|required|numeric',
                'breathing_rate' => 'sometimes|required|integer',
                'oxygen_level' => 'sometimes|required|integer',
                'check_time' => 'sometimes|required|date',
            ]);

            if (isset($validated['patient_id']) && $request->user()->role->value !== 'admin') {
                $patient = PatientData::find($validated['patient_id']);
                if ($patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan data ke pasien ini.', 403);
                }
            }

            $vitalSign->update($validated);

            return $this->successResponse($vitalSign, 'Berhasil mengubah data tanda-tanda vital.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Data tanda-tanda vital tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data tanda-tanda vital.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus data tanda-tanda vital dari database.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $vitalSign = VitalSign::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $vitalSign->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $vitalSign->delete();

            return $this->successResponse(null, 'Berhasil menghapus data tanda-tanda vital.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Data tanda-tanda vital tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus data tanda-tanda vital.', 500);
        }
    }
}
