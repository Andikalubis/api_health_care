<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MedicineHistoryService;
use App\Services\MedicineScheduleService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class MedicineHistoryController extends Controller
{
    protected $service;
    protected $scheduleService;

    public function __construct(MedicineHistoryService $service, MedicineScheduleService $scheduleService)
    {
        $this->service = $service;
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $histories = $this->service->getAll($request, ['medicineSchedule']);
            return $this->successResponse($histories, 'Berhasil mengambil daftar riwayat minum obat.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar riwayat minum obat.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'schedule_id' => 'required|exists:medicine_schedules,id',
                'taken_time' => 'required|date',
                'status' => 'required|string|max:20',
            ]);

            $schedule = $this->scheduleService->findById($validated['schedule_id']);
            if (!$this->scheduleService->checkOwnership($schedule, $request->user())) {
                return $this->errorResponse('Akses ditolak untuk menambahkan riwayat pada jadwal obat ini.', 403);
            }

            $history = $this->service->create($validated);
            return $this->successResponse($history, 'Berhasil membuat riwayat minum obat baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Jadwal obat tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat riwayat minum obat.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $medicineHistory = $this->service->findById($id);

            if (!$this->service->checkOwnership($medicineHistory, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($medicineHistory->load('medicineSchedule'), 'Berhasil mengambil detail riwayat minum obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Riwayat minum obat tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail riwayat minum obat.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $medicineHistory = $this->service->findById($id);

            if (!$this->service->checkOwnership($medicineHistory, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'schedule_id' => 'sometimes|required|exists:medicine_schedules,id',
                'taken_time' => 'sometimes|required|date',
                'status' => 'sometimes|required|string|max:20',
            ]);

            if (isset($validated['schedule_id'])) {
                $schedule = $this->scheduleService->findById($validated['schedule_id']);
                if (!$this->scheduleService->checkOwnership($schedule, $request->user())) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan riwayat ke jadwal obat ini.', 403);
                }
            }

            $medicineHistory = $this->service->update($id, $validated);
            return $this->successResponse($medicineHistory, 'Berhasil mengubah data riwayat minum obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Riwayat minum obat tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data riwayat minum obat.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $medicineHistory = $this->service->findById($id);

            if (!$this->service->checkOwnership($medicineHistory, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus riwayat minum obat.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Riwayat minum obat tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus riwayat minum obat.', 500);
        }
    }
}
