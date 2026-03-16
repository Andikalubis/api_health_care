<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MealSchedule;
use App\Models\PatientData;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MealScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     * Mengambil daftar jadwal makan.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = MealSchedule::with(['patient', 'mealType']);

            if ($user->role->value !== 'admin') {
                $query->whereHas('patient', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $schedules = $query->get();

            return $this->successResponse($schedules, 'Berhasil mengambil daftar jadwal makan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar jadwal makan.', 500, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan jadwal makan baru ke dalam database.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patient_data,id',
                'meal_type_id' => 'required|exists:meal_types,id',
                'meal_time' => 'required',
                'notes' => 'nullable|string',
            ]);

            if ($request->user()->role->value !== 'admin') {
                $patient = PatientData::find($validated['patient_id']);
                if ($patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk menambahkan jadwal makan pada pasien ini.', 403);
                }
            }

            $schedule = MealSchedule::create($validated);

            return $this->successResponse($schedule, 'Berhasil membuat jadwal makan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat jadwal makan.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     * Menampilkan detail dari jadwal makan berdasarkan ID.
     */
    public function show(Request $request, $id)
    {
        try {
            $mealSchedule = MealSchedule::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $mealSchedule->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($mealSchedule->load(['patient', 'mealType']), 'Berhasil mengambil detail jadwal makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal makan tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail jadwal makan.', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     * Mengubah data jadwal makan yang sudah ada di database.
     */
    public function update(Request $request, $id)
    {
        try {
            $mealSchedule = MealSchedule::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $mealSchedule->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'patient_id' => 'sometimes|required|exists:patient_data,id',
                'meal_type_id' => 'sometimes|required|exists:meal_types,id',
                'meal_time' => 'sometimes|required',
                'notes' => 'nullable|string',
            ]);

            if (isset($validated['patient_id']) && $request->user()->role->value !== 'admin') {
                $patient = PatientData::find($validated['patient_id']);
                if ($patient->user_id !== $request->user()->id) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan jadwal makan ke pasien ini.', 403);
                }
            }

            $mealSchedule->update($validated);

            return $this->successResponse($mealSchedule, 'Berhasil mengubah data jadwal makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal makan tidak ditemukan.', 404, $e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data jadwal makan.', 500, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus jadwal makan dari database.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $mealSchedule = MealSchedule::findOrFail($id);

            if ($request->user()->role->value !== 'admin' && $mealSchedule->patient->user_id !== $request->user()->id) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $mealSchedule->delete();

            return $this->successResponse(null, 'Berhasil menghapus jadwal makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal makan tidak ditemukan.', 404, $e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus jadwal makan.', 500, $e->getMessage());
        }
    }
}
