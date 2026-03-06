<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Device;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DeviceSyncController extends Controller
{
    /**
     * Secure device push endpoint for T304F Mini (Push Mode) and compatible agents.
     * Token-based auth: X-Device-Key, Authorization: Bearer <token>, or body device_key.
     * Accepts:
     * - T304F format: logs[] with employee_code, timestamp, device_id (device_id must match authenticated device)
     * - Legacy: punches[] with device_user_id, punch_time, punch_sequence, punch_type
     */
    public function sync(Request $request): JsonResponse
    {
        Log::info('Device push request received', [
            'has_x_device_key' => $request->hasHeader('X-Device-Key'),
            'has_query_key' => $request->has('api_key') || $request->has('device_key'),
            'content_type' => $request->header('Content-Type'),
            'body_keys' => array_keys($request->all()),
        ]);

        $token = $this->resolveToken($request);
        if (! $token) {
            Log::warning('Device push: missing token. Send X-Device-Key header or body device_key.');
            return response()->json(['success' => false, 'message' => 'Missing device token'], 401);
        }

        $device = Device::where('api_key', $token)->where('is_active', true)->first();
        if (! $device) {
            Log::warning('Device push: invalid or inactive device for token.');
            return response()->json(['success' => false, 'message' => 'Invalid device token'], 401);
        }

        $payload = $request->input('logs') ?? $request->input('punches') ?? [];
        // T304F single-punch: body may be one object with PIN/UserID and DateTime (no logs/punches wrapper)
        if (! is_array($payload)) {
            $payload = [];
        }
        if (empty($payload) && ($request->has('PIN') || $request->has('UserID') || $request->has('employee_code'))) {
            $payload = [$request->only(['PIN', 'UserID', 'UserIDEx', 'DateTime', 'employee_code', 'timestamp', 'device_id', 'punch_sequence', 'punch_type'])];
        }
        // Form-urlencoded or other: try common field names from raw body
        if (empty($payload)) {
            $raw = $request->all();
            $single = [];
            foreach (['PIN', 'UserID', 'UserId', 'employee_code', 'user_id'] as $idKey) {
                if (! empty($raw[$idKey])) {
                    $single[$idKey] = $raw[$idKey];
                    break;
                }
            }
            foreach (['DateTime', 'datetime', 'dateTime', 'timestamp', 'punch_time', 'PunchTime'] as $timeKey) {
                if (! empty($raw[$timeKey])) {
                    $single[$timeKey] = $raw[$timeKey];
                    break;
                }
            }
            if (count($single) >= 2) {
                $payload = [array_merge($single, $request->only(['punch_sequence', 'punch_type']))];
            }
        }
        if (empty($payload)) {
            Log::warning('Device push: invalid or empty payload.', [
                'body_keys' => array_keys($request->all()),
                'sample' => json_encode(array_slice($request->all(), 0, 5)),
            ]);
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 422);
        }

        $inserted = 0;
        $now = now();

        foreach ($payload as $row) {
            $normalized = $this->normalizeLogRow($row, $device);
            if ($normalized === null) {
                continue;
            }

            if ($this->isDuplicate($device->id, $normalized['device_user_id'], $normalized['punch_time'])) {
                continue;
            }

            $employee = $this->resolveEmployee($device->location_id, $normalized['device_user_id'], $row);

            AttendanceLog::create([
                'device_id' => $device->id,
                'employee_id' => $employee?->id,
                'device_user_id' => $normalized['device_user_id'],
                'punch_time' => $normalized['punch_time'],
                'punch_sequence' => $normalized['punch_sequence'],
                'punch_type' => $normalized['punch_type'],
                'synced_at' => $now,
            ]);
            $inserted++;
        }

        $device->update(['last_sync_at' => $now]);

        Log::info('Device push: success', ['device_id' => $device->id, 'device_name' => $device->name, 'inserted' => $inserted]);

        return response()->json([
            'success' => true,
            'message' => 'Synced',
            'inserted' => $inserted,
            'device_id' => $device->id,
        ]);
    }

    private function resolveToken(Request $request): ?string
    {
        if ($request->header('X-Device-Key')) {
            return trim($request->header('X-Device-Key'));
        }
        if (preg_match('/^Bearer\s+(.+)$/i', $request->header('Authorization', ''), $m)) {
            return trim($m[1]);
        }
        foreach (['device_key', 'api_key', 'token', 'X-Device-Key'] as $key) {
            $v = $request->input($key);
            if (! empty($v) && is_string($v)) {
                return trim($v);
            }
        }
        // Many devices only allow setting URL; pass key in query string: ?api_key=xxx or ?device_key=xxx
        foreach (['device_key', 'api_key', 'token'] as $key) {
            $v = $request->query($key);
            if (! empty($v) && is_string($v)) {
                return trim($v);
            }
        }
        return null;
    }

    /**
     * Normalize T304F (employee_code, timestamp, device_id) or legacy (device_user_id, punch_time) to internal format.
     * T304F device_id is validated to match the authenticated device.
     */
    private function normalizeLogRow(array $row, Device $device): ?array
    {
        $isT304F = isset($row['employee_code']) && isset($row['timestamp']);
        if ($isT304F) {
            $employeeCode = (string) $row['employee_code'];
            $punchTime = $row['timestamp'] ?? null;
            $payloadDeviceId = $row['device_id'] ?? null;
            if ($payloadDeviceId !== null && (int) $payloadDeviceId !== (int) $device->id) {
                return null;
            }
            if (! $employeeCode || ! $punchTime) {
                return null;
            }
            $punchTime = Carbon::parse($punchTime);
            $employee = Employee::findByDeviceUserIdForLocation($device->location_id, $employeeCode);
            $deviceUserId = $employee ? (string) $employee->device_user_id : $employeeCode;
            return [
                'device_user_id' => $deviceUserId,
                'punch_time' => $punchTime,
                'punch_sequence' => (int) ($row['punch_sequence'] ?? 1),
                'punch_type' => $row['punch_type'] ?? null,
            ];
        }

        $deviceUserId = (string) ($row['device_user_id'] ?? $row['userId'] ?? $row['user_id'] ?? $row['PIN'] ?? $row['UserID'] ?? '');
        $punchTime = $row['punch_time'] ?? $row['timestamp'] ?? $row['dateTime'] ?? $row['DateTime'] ?? null;
        if (! $deviceUserId || ! $punchTime) {
            return null;
        }
        return [
            'device_user_id' => $deviceUserId,
            'punch_time' => Carbon::parse($punchTime),
            'punch_sequence' => (int) ($row['punch_sequence'] ?? 1),
            'punch_type' => $row['punch_type'] ?? null,
        ];
    }

    private function resolveEmployee(int $locationId, string $deviceUserId, array $row): ?Employee
    {
        if (isset($row['employee_code'])) {
            $emp = Employee::findByDeviceUserIdForLocation($locationId, (string) $row['employee_code']);
            if ($emp) {
                return $emp;
            }
        }
        return Employee::findByDeviceUserIdForLocation($locationId, $deviceUserId);
    }

    private function isDuplicate(int $deviceId, string $deviceUserId, Carbon $punchTime): bool
    {
        return AttendanceLog::where('device_id', $deviceId)
            ->where('device_user_id', $deviceUserId)
            ->whereBetween('punch_time', [$punchTime->copy()->startOfMinute(), $punchTime->copy()->endOfMinute()])
            ->exists();
    }
}
