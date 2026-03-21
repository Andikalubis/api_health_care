<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MealScheduleService;
use App\Services\PatientDataService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MealScheduleController extends Controller
{
    protected $service;
    protected $patientService;

    public function __construct(MealScheduleService $service, PatientDataService $patientService)
    {
        $this->service = $service;
        $this->patientService = $patientService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $schedules = $this->service->getAll($request, ['patient', 'mealType']);
            return $this->successResponse($schedules, 'Berhasil mengambil daftar jadwal makan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar jadwal makan.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
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

            $patient = $this->patientService->findById($validated['patient_id']);
            if (!$this->patientService->checkOwnership($patient, $request->user())) {
                return $this->errorResponse('Akses ditolak untuk menambahkan jadwal makan pada pasien ini.', 403);
            }

            $schedule = $this->service->create($validated);
            return $this->successResponse($schedule, 'Berhasil membuat jadwal makan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pasien tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat jadwal makan.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $mealSchedule = $this->service->findById($id);

            if (!$this->service->checkOwnership($mealSchedule, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($mealSchedule->load(['patient', 'mealType']), 'Berhasil mengambil detail jadwal makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal makan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail jadwal makan.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $mealSchedule = $this->service->findById($id);

            if (!$this->service->checkOwnership($mealSchedule, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'patient_id' => 'sometimes|required|exists:patient_data,id',
                'meal_type_id' => 'sometimes|required|exists:meal_types,id',
                'meal_time' => 'sometimes|required',
                'notes' => 'nullable|string',
            ]);

            if (isset($validated['patient_id'])) {
                $patient = $this->patientService->findById($validated['patient_id']);
                if (!$this->patientService->checkOwnership($patient, $request->user())) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan jadwal makan ke pasien ini.', 403);
                }
            }

            $mealSchedule = $this->service->update($id, $validated);
            return $this->successResponse($mealSchedule, 'Berhasil mengubah data jadwal makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal makan tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data jadwal makan.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $mealSchedule = $this->service->findById($id);

            if (!$this->service->checkOwnership($mealSchedule, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus jadwal makan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal makan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus jadwal makan.', 500);
        }
    }
}
