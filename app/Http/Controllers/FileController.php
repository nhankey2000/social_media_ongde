<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\File;

class FileController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:551200', // Tăng lên 50MB
        ]);

        try {
            $file = $request->file('zip_file');
            $path = $file->store('zips', 'public');

            $fileModel = File::create([
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'size' => $file->getSize(),
            ]);

            return redirect()->route('licenses.index')->with('success', 'Đã upload file: ' . $fileModel->name);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi upload file: ' . $e->getMessage());
        }
    }

    public function download($id)
    {
        try {
            $file = File::findOrFail($id);
            return Storage::disk('public')->download($file->path, $file->name);
        } catch (\Exception $e) {
            abort(404, 'File không tồn tại');
        }
    }

    public function destroy($id)
    {
        try {
            $file = File::findOrFail($id);
            Storage::disk('public')->delete($file->path);
            $file->delete();
            return redirect()->route('licenses.index')->with('success', 'Đã xóa file: ' . $file->name);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi xóa file: ' . $e->getMessage());
        }
    }
}