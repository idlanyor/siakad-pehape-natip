<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaturanController extends Controller
{
    public function index()
    {
        $settings = Pengaturan::orderBy('grup')->orderBy('id')->get()->groupBy('grup');
        $grupan   = Pengaturan::GRUPAN;

        return view('admin.cms.pengaturan', compact('settings', 'grupan'));
    }

    public function update(Request $request)
    {
        $inputs = $request->except(['_token', '_method']);

        foreach ($inputs as $key => $value) {
            $setting = Pengaturan::where('key', $key)->first();
            if (! $setting) continue;

            // Handle file upload (image type)
            if ($setting->tipe === 'image' && $request->hasFile($key)) {
                $file = $request->file($key);
                // Hapus file lama
                if ($setting->value) {
                    Storage::disk('public')->delete($setting->value);
                }
                $path = $file->store('cms', 'public');
                $setting->update(['value' => $path]);
            } elseif ($setting->tipe === 'boolean') {
                // Checkbox yang tidak tersubmit = 0
                $setting->update(['value' => $value ? '1' : '0']);
            } else {
                $setting->update(['value' => $value]);
            }
        }

        // Handle boolean yang tidak tersubmit (unchecked checkboxes)
        Pengaturan::where('tipe', 'boolean')->each(function ($s) use ($inputs) {
            if (! array_key_exists($s->key, $inputs)) {
                $s->update(['value' => '0']);
            }
        });

        Pengaturan::clearCache();

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
