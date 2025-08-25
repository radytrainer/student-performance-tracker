<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin']);
    }

    /**
     * Return all system settings merged with defaults.
     */
    public function index(Request $request)
    {
        $defaults = self::defaults();
        $rows = SystemSetting::all();
        $data = $defaults;
        foreach ($rows as $row) {
            $key = $row->setting_key;
            if (!array_key_exists($key, $defaults)) continue;
            $val = $this->decodeValue($row->setting_value);
            $data[$key] = $this->coerceType($key, $val);
        }
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Update one or many settings.
     */
    public function update(Request $request)
    {
        $defaults = self::defaults();
        $input = $request->all();
        $allowedKeys = array_keys($defaults);

        $payload = [];
        foreach ($input as $k => $v) {
            if (!in_array($k, $allowedKeys, true)) continue;
            $payload[$k] = $this->normalizeValue($k, $v);
        }

        if (empty($payload)) {
            return response()->json(['success' => false, 'message' => 'No valid settings provided'], 422);
        }

        foreach ($payload as $k => $v) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $k],
                [
                    'setting_value' => $this->encodeValue($v),
                    'updated_by' => Auth::id(),
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * Reset all settings to defaults (only known keys).
     */
    public function reset(Request $request)
    {
        $defaults = self::defaults();
        foreach ($defaults as $k => $v) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $k],
                [
                    'setting_value' => $this->encodeValue($v),
                    'updated_by' => Auth::id(),
                ]
            );
        }
        return response()->json(['success' => true, 'data' => $defaults]);
    }

    /**
     * Placeholder: Initiate a manual backup (no-op for now).
     */
    public function backup(Request $request)
    {
        // In a future iteration, dispatch a job to perform a backup
        return response()->json(['success' => true, 'message' => 'Backup initiated']);
    }

    /**
     * Placeholder: Run maintenance tasks (no-op for now).
     */
    public function maintenance(Request $request)
    {
        // e.g., optimize:clear, queue:restart, etc.
        return response()->json(['success' => true, 'message' => 'Maintenance started']);
    }

    /**
     * Default settings map.
     */
    public static function defaults(): array
    {
        return [
            // General
            'schoolName' => 'Riverside High School',
            'academicYear' => '2024-2025',
            'timeZone' => 'America/New_York',

            // Academic
            'gradingScale' => 'letter',
            'passingGrade' => 60,
            'requiredAttendance' => 75,
            'allowLateSubmissions' => true,

            // Notifications
            'emailNotifications' => true,
            'smsNotifications' => false,
            'parentNotifications' => true,

            // Security
            'sessionTimeout' => 120,
            'requireTwoFactor' => false,
            'enforcePasswordPolicy' => true,

            // Backup
            'backupFrequency' => 'daily',
        ];
    }

    private function types(): array
    {
        return [
            'schoolName' => 'string',
            'academicYear' => 'string',
            'timeZone' => 'string',
            'gradingScale' => 'string',
            'passingGrade' => 'integer',
            'requiredAttendance' => 'integer',
            'allowLateSubmissions' => 'boolean',
            'emailNotifications' => 'boolean',
            'smsNotifications' => 'boolean',
            'parentNotifications' => 'boolean',
            'sessionTimeout' => 'integer',
            'requireTwoFactor' => 'boolean',
            'enforcePasswordPolicy' => 'boolean',
            'backupFrequency' => 'string',
        ];
    }

    private function normalizeValue(string $key, $value)
    {
        $type = $this->types()[$key] ?? 'string';
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            case 'integer':
                return (int) $value;
            default:
                return is_array($value) || is_object($value) ? (array) $value : (string) $value;
        }
    }

    private function coerceType(string $key, $value)
    {
        $type = $this->types()[$key] ?? 'string';
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            default:
                return $value;
        }
    }

    private function encodeValue($value): string
    {
        return json_encode($value);
    }

    private function decodeValue(?string $value)
    {
        if ($value === null) return null;
        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            return $decoded;
        } catch (\Throwable $e) {
            return $value; // fallback
        }
    }
}
