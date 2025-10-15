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
            'zip_file' => 'required|file|mimes:zip|max:102400', // Tăng lên 100MB nếu cần
        ]);

        try {
            $file = $request->file('zip_file');
            $originalName = $file->getClientOriginalName(); // Lấy tên gốc
            $path = $file->storeAs('zips', $originalName, 'public'); // Lưu với tên gốc

            $fileModel = File::create([
                'name' => $originalName, // Lưu tên gốc
                'path' => $path,
                'size' => $file->getSize(),
            ]);

            return redirect()->route('licenses.index')->with('success', 'Đã upload file: ' . $originalName);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi upload file: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        try {
            $file = File::where('name', $filename)->firstOrFail();
            return Storage::disk('public')->download($file->path, $file->name);
        } catch (\Exception $e) {
            abort(404, 'File không tồn tại hoặc đã bị xóa: ' . $e->getMessage());
        }
    }

    public function destroy($filename)
    {
        try {
            $file = File::where('name', $filename)->firstOrFail();
            Storage::disk('public')->delete($file->path);
            $file->delete();
            return redirect()->route('licenses.index')->with('success', 'Đã xóa file: ' . $file->name);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Lỗi xóa file: ' . $e->getMessage());
        }
    }
}