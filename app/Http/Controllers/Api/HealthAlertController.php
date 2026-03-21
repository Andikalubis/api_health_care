<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HealthAlertService;
use App\Services\HealthCheckService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class HealthAlertController extends Controller
{
    protected $service;
    protected $healthCheckService;

    public function __construct(HealthAlertService $service, HealthCheckService $healthCheckService)
    {
        $this->service = $service;
        $this->healthCheckService = $healthCheckService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $alerts = $this->service->getAll($request, ['healthCheck']);
            return $this->successResponse($alerts, 'Berhasil mengambil daftar peringatan kesehatan.');
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil daftar peringatan kesehatan.', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'health_check_id' => 'required|exists:health_checks,id',
                'alert_level' => 'required|string|max:20',
                'message' => 'required|string',
                'sent_status' => 'boolean',
            ]);

            $healthCheck = $this->healthCheckService->findById($validated['health_check_id']);
            if (!$this->healthCheckService->checkOwnership($healthCheck, $request->user())) {
                return $this->errorResponse('Akses ditolak untuk menambahkan peringatan pada cek kesehatan ini.', 403);
            }

            $alert = $this->service->create($validated);
            return $this->successResponse($alert, 'Berhasil membuat peringatan kesehatan baru.', 201);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Cek kesehatan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat membuat peringatan kesehatan.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $healthAlert = $this->service->findById($id);

            if (!$this->service->checkOwnership($healthAlert, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            return $this->successResponse($healthAlert->load('healthCheck'), 'Berhasil mengambil detail peringatan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Peringatan kesehatan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengambil detail peringatan kesehatan.', 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $healthAlert = $this->service->findById($id);

            if (!$this->service->checkOwnership($healthAlert, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $validated = $request->validate([
                'health_check_id' => 'sometimes|required|exists:health_checks,id',
                'alert_level' => 'sometimes|required|string|max:20',
                'message' => 'sometimes|required|string',
                'sent_status' => 'boolean',
            ]);

            if (isset($validated['health_check_id'])) {
                $healthCheck = $this->healthCheckService->findById($validated['health_check_id']);
                if (!$this->healthCheckService->checkOwnership($healthCheck, $request->user())) {
                    return $this->errorResponse('Akses ditolak untuk memindahkan peringatan ke cek kesehatan ini.', 403);
                }
            }

            $healthAlert = $this->service->update($id, $validated);
            return $this->successResponse($healthAlert, 'Berhasil mengubah data peringatan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Peringatan kesehatan tidak ditemukan.', 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat mengubah data peringatan kesehatan.', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $healthAlert = $this->service->findById($id);

            if (!$this->service->checkOwnership($healthAlert, $request->user())) {
                return $this->errorResponse('Akses ditolak.', 403);
            }

            $this->service->delete($id);
            return $this->successResponse(null, 'Berhasil menghapus peringatan kesehatan.');
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Peringatan kesehatan tidak ditemukan.', 404);
        } catch (\Exception $e) {
            return $this->errorResponse('Terjadi kesalahan saat menghapus peringatan kesehatan.', 500);
        }
    }
}
