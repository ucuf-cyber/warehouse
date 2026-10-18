<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengaturan\PrinterSettingsRequest;
use App\Http\Requests\Pengaturan\StoreProfileRequest;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\BackupCompletedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Yajra\DataTables\Facades\DataTables;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('admin.pengaturan.index', [
            'storeName' => Setting::get('store_name', config('app.name')),
            'storeAddress' => Setting::get('store_address'),
            'storePhone' => Setting::get('store_phone'),
            'storeLogo' => Setting::get('store_logo'),
            'barcodeWidth' => Setting::get('barcode_width_mm', config('bms.barcode_printer.width_mm')),
            'barcodeHeight' => Setting::get('barcode_height_mm', config('bms.barcode_printer.height_mm')),
            'backups' => $this->listBackups(),
        ]);
    }

    public function updateProfile(StoreProfileRequest $request): JsonResponse
    {
        Setting::set('store_name', $request->string('store_name')->toString());
        Setting::set('store_address', $request->string('store_address')->toString());
        Setting::set('store_phone', $request->string('store_phone')->toString());

        if ($request->hasFile('logo')) {
            $oldLogo = Setting::get('store_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            Setting::set('store_logo', $request->file('logo')->store('logo', 'public'));
        }

        return response()->json(['success' => true, 'data' => null, 'message' => 'Profil toko berhasil disimpan.']);
    }

    public function updatePrinter(PrinterSettingsRequest $request): JsonResponse
    {
        Setting::set('barcode_width_mm', $request->integer('barcode_width_mm'));
        Setting::set('barcode_height_mm', $request->integer('barcode_height_mm'));

        return response()->json(['success' => true, 'data' => null, 'message' => 'Pengaturan printer berhasil disimpan.']);
    }

    private function listBackups(): array
    {
        $directory = config('backup.backup.name');
        $disk = Storage::disk('local');

        if (! $disk->exists($directory)) {
            return [];
        }

        return collect($disk->files($directory))
            ->filter(fn ($file) => str_ends_with($file, '.zip'))
            ->map(fn ($file) => [
                'path' => $file,
                'name' => basename($file),
                'size' => $disk->size($file),
                'date' => $disk->lastModified($file),
            ])
            ->sortByDesc('date')
            ->values()
            ->all();
    }

    public function backupIndex(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->listBackups(), 'message' => '']);
    }

    public function backupRun(): JsonResponse
    {
        try {
            $exitCode = Artisan::call('backup:run', ['--only-db' => true]);

            // spatie/laravel-backup catches its own exceptions internally and
            // reports failure via the command's exit code, not by throwing —
            // so a non-zero exit code has to be checked explicitly here.
            if ($exitCode !== 0) {
                $output = trim(Artisan::output());
                Notification::send(User::all(), new BackupCompletedNotification(false, $output));

                return response()->json(['success' => false, 'data' => null, 'message' => 'Backup gagal: '.$output], 500);
            }

            Notification::send(User::all(), new BackupCompletedNotification(true));

            return response()->json(['success' => true, 'data' => $this->listBackups(), 'message' => 'Backup berhasil dibuat.']);
        } catch (\Throwable $e) {
            Notification::send(User::all(), new BackupCompletedNotification(false, $e->getMessage()));

            return response()->json(['success' => false, 'data' => null, 'message' => 'Backup gagal: '.$e->getMessage()], 500);
        }
    }

    public function backupDownload(Request $request): StreamedResponse
    {
        $path = $request->query('path');
        $directory = config('backup.backup.name');

        abort_unless($path && str_starts_with($path, $directory.'/'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function backupDelete(Request $request): JsonResponse
    {
        $path = $request->string('path')->toString();
        $directory = config('backup.backup.name');

        if (! str_starts_with($path, $directory.'/')) {
            return response()->json(['success' => false, 'data' => null, 'message' => 'Path tidak valid.'], 422);
        }

        Storage::disk('local')->delete($path);

        return response()->json(['success' => true, 'data' => null, 'message' => 'File backup dihapus.']);
    }

    public function activityLogData(Request $request)
    {
        $query = Activity::query()->with('causer:id,name')->latest();

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->string('log_name'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        return DataTables::of($query)
            ->addColumn('causer_name', fn (Activity $a) => $a->causer->name ?? 'System')
            ->addColumn('description_full', fn (Activity $a) => $a->description)
            ->toJson();
    }
}
