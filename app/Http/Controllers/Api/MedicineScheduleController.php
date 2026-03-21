<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MedicineScheduleService;
use App\Services\PatientDataService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MedicineScheduleController extends Controller
{
    protected $service;
    protected $patientService;

    public function __construct(MedicineScheduleService $service, PatientDataService $patientService)
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
            $schedules = $this->service->getAll($request, ['patient', 'medicine']);
            return $this->successResponse($schedules, 'Berhasil mengambil daftar jadwal obat.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar jadwal obat.', 500);
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
                'medicine_id' => 'required|exists:medicines,id',
                'dosage' => 'required|string|max:50',
                'drink_time' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            $patient = $this->patientService->findById($validated['patient_id']);
            if (!$this->patientService->checkOwnership($patient, $request->user())) {
                return $this->errorResponse('Akses ditolak untuk menambahkan data pada pasien ini.', 403);
            }

            $schedule = $this->service->create($validated);
            return $this->successResponse($schedule, 'Berhasil membuat jadwal obat baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Pasien tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat jadwal obat.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $medicineSchedule = $this->service->findById($id);

            if (!$this->service->checkOwnership($medicineSchedule, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($medicineSchedule->load(['patient', 'medicine', 'medicineHistories']), 'Berhasil mengambil detail jadwal obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal obat tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail jadwal obat.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $medicineSchedule = $this->service->findById($id);

            if (!$this->service->checkOwnership($medicineSchedule, $request->user())) {
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

            if (isset($validated['patient_id'])) {
                $patient = $this->patientService->findById($validated['patient_id']);
                if (!$this->patientService->checkOwnership($patient, $request->user())) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan data ke pasien ini.', 403);
                }
            }

            $medicineSchedule = $this->service->update($id, $validated);
            return $this->successResponse($medicineSchedule, 'Berhasil mengubah data jadwal obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal obat tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data jadwal obat.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $medicineSchedule = $this->service->findById($id);

            if (!$this->service->checkOwnership($medicineSchedule, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus jadwal obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal obat tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus jadwal obat.', 500);
        }
    }
}
