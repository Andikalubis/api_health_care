<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicineHistory;
use App\Models\MedicineSchedule;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MedicineHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil daftar riwayat minum obat.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = MedicineHistory::with('medicineSchedule');

            if ($user->role->value !== 'admin') {
                $query->whereHas('medicineSchedule.patient', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $histories = $query->get();

            return $this->successResponse($histories, 'Berhasil mengambil daftar riwayat minum obat.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar riwayat minum obat.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan riwayat minum obat baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'schedule_id' => 'required|exists:medicine_schedules,id',
                'taken_time' => 'required|date',
                'status' => 'required|string|max:20',
            ]);

            if ($request->user()->role->value !== 'admin') {
                $schedule = MedicineSchedule::with('patient')->find($validated['schedule_id']);
                if ($schedule && $schedule->patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk menambahkan riwayat pada jadwal obat ini.', 403);
                }
            }

            $history = MedicineHistory::create($validated);

            return $this->successResponse($history, 'Berhasil membuat riwayat minum obat baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat riwayat minum obat.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari riwayat minum obat berdasarkan ID.
     */
    public function show(Request $request, $id)
    {
        try {
            $medicineHistory = MedicineHistory::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $medicineHistory->medicineSchedule->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($medicineHistory->load('medicineSchedule'), 'Berhasil mengambil detail riwayat minum obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Riwayat minum obat tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail riwayat minum obat.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data riwayat minum obat yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $medicineHistory = MedicineHistory::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $medicineHistory->medicineSchedule->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'schedule_id' => 'sometimes|required|exists:medicine_schedules,id',
                'taken_time' => 'sometimes|required|date',
                'status' => 'sometimes|required|string|max:20',
            ]);

            if (isset($validated['schedule_id']) && $request->user()->role->value !== 'admin') {
                $schedule = MedicineSchedule::with('patient')->find($validated['schedule_id']);
                if ($schedule && $schedule->patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan riwayat ke jadwal obat ini.', 403);
                }
            }

            $medicineHistory->update($validated);

            return $this->successResponse($medicineHistory, 'Berhasil mengubah data riwayat minum obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Riwayat minum obat tidak ditemukan.', 404, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data riwayat minum obat.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus riwayat minum obat dari database.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $medicineHistory = MedicineHistory::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $medicineHistory->medicineSchedule->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $medicineHistory->delete();

            return $this->successResponse(null, 'Berhasil menghapus riwayat minum obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Riwayat minum obat tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus riwayat minum obat.', 500, $e->getMessage());
        }
    }
}
