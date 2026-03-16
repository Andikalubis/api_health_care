<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineSchedule;
use App\Models\PatientData;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MedicineScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil daftar jadwal obat.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = MedicineSchedule::with(['patient', 'medicine']);

            if ($user->role->value !== 'admin') {
                $query->whereHas('patient', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $schedules = $query->get();

            return $this->successResponse($schedules, 'Berhasil mengambil daftar jadwal obat.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar jadwal obat.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan jadwal obat baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patient_data,id',
                'medicine_id' => 'required|exists:medicines,id',
                'dosage' => 'required|string|max:50',
                'drink_time' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            if ($request->user()->role->value !== 'admin') {
                $patient = PatientData::find($validated['patient_id']);
                if ($patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk menambahkan data pada pasien ini.', 403);
                }
            }

            $schedule = MedicineSchedule::create($validated);

            return $this->successResponse($schedule, 'Berhasil membuat jadwal obat baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat jadwal obat.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari jadwal obat berdasarkan ID.
     */
    public function show(Request $request, $id)
    {
        try {
            $medicineSchedule = MedicineSchedule::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $medicineSchedule->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($medicineSchedule->load(['patient', 'medicine', 'medicineHistories']), 'Berhasil mengambil detail jadwal obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal obat tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail jadwal obat.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data jadwal obat yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $medicineSchedule = MedicineSchedule::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $medicineSchedule->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'patient_id' => 'sometimes|required|exists:patient_data,id',
                'medicine_id' => 'sometimes|required|exists:medicines,id',
                'dosage' => 'sometimes|required|string|max:50',
                'drink_time' => 'sometimes|required',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|required|date',
                'notes' => 'nullable|string',
            ]);

            if (isset($validated['patient_id']) && $request->user()->role->value !== 'admin') {
                $patient = PatientData::find($validated['patient_id']);
                if ($patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan data ke pasien ini.', 403);
                }
            }

            $medicineSchedule->update($validated);

            return $this->successResponse($medicineSchedule, 'Berhasil mengubah data jadwal obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal obat tidak ditemukan.', 404, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data jadwal obat.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus jadwal obat dari database.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $medicineSchedule = MedicineSchedule::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $medicineSchedule->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $medicineSchedule->delete();

            return $this->successResponse(null, 'Berhasil menghapus jadwal obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal obat tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus jadwal obat.', 500, $e->getMessage());
        }
    }
}
