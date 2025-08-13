<?php

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\YouTubeOAuthController;
use App\Http\Controllers\ActivationKeyController;
use App\Http\Controllers\ZooController;
use App\Http\Controllers\LicenseController;
use App\Models\YouTubeVideo;
use App\Models\License;
use App\Http\Controllers\KhuVuonMaQuaiController;
use App\Http\Controllers\SoTayChanNuoiController;
use App\Http\Controllers\BanhXeoCoTuController;
use App\Http\Controllers\DulieuTruyenThongController;
use App\Http\Controllers\DulieuTruyenThongBXController;
use App\Models\DataPost;
use App\Models\ImagesData;
use App\Models\DanhmucData;
use App\Models\DataPostNH;
use App\Models\DataImagesNH;
use App\Models\DanhmucNHS;
use App\Models\DataPostBX;
use App\Models\DataImagesBX;
use App\Models\DanhmucBX;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to licenses management
Route::get('/licenses', function () {
    return redirect()->route('licenses.index');
});

// Analytics routes
Route::get('/analytics/growth-chart-data', [AnalyticsController::class, 'getGrowthChartData'])->name('analytics.growth-chart-data');
Route::get('/admin/analytics/growth', function () {
    return view('admin.analytics.growth');
})->name('admin.analytics.growth');

// Social media routes
Route::post('/chatbot', [ChatbotController::class, 'handleMessage']);
Route::get('/facebook/redirect', [FacebookController::class, 'redirectToFacebook'])->name('facebook.redirect');
Route::get('/facebook/callback', [FacebookController::class, 'handleFacebookCallback'])->name('facebook.callback');
Route::get('/youtube/auth', [YouTubeOAuthController::class, 'redirectToGoogle']);
Route::get('/youtube/callback', [YouTubeOAuthController::class, 'handleGoogleCallback']);

// Legacy activation keys (for backward compatibility)
Route::get('/keys', [ActivationKeyController::class, 'index']);
Route::post('/keys', [ActivationKeyController::class, 'store']);
Route::delete('/keys/{id}', [ActivationKeyController::class, 'destroy']);

// Profile and Zoo
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show']);
Route::get('/truyen-thong', [App\Http\Controllers\TruyenThongController::class, 'show']);
Route::get('/zoo', [App\Http\Controllers\ZooController::class, 'show']);
Route::get('/du-lieu-truyen-thong', [App\Http\Controllers\DulieuTruyenThongController::class, 'show']);
Route::get('/du-lieu-truyen-thongNH', [App\Http\Controllers\DulieuTruyenThongNHController::class, 'show']);
Route::get('/du-lieu-truyen-thongBX', [App\Http\Controllers\DulieuTruyenThongBXController::class, 'show']);
// Night Hunters - Khu Vườn Ma Quái
Route::get('/khu-vuon-ma-quai', [KhuVuonMaQuaiController::class, 'index'])->name('khuvuonmaquai');
Route::get('/so-tay-chan-nuoi', [SoTayChanNuoiController::class, 'index'])->name('sotaychannuoi');
Route::get('/banh-xeo-co-tu', [BanhXeoCoTuController::class, 'index'])->name('banhxeocotu');

/*
|--------------------------------------------------------------------------
| License Management Routes (New System)
|--------------------------------------------------------------------------
*/

// API kiểm tra bản quyền - cho Python client
Route::get('/check-key', function (Request $request) {
    $machineId = $request->query('machine_id');
    $id = $request->query('id'); // Backward compatibility

    // Use machine_id if available, otherwise fall back to id
    $lookupId = $machineId ?: $id;

    if (!$lookupId) {
        return response()->json(['error' => 'missing_machine_id'], 400);
    }

    // First check new licenses table
    $license = License::where('machine_id', $lookupId)->first();

    if ($license) {
        $now = Carbon::now();
        $expire = Carbon::parse($license->expires_at);

        if ($expire->lt($now)) {
            return response()->json(['error' => 'expired'], 403);
        }

        return response()->json([
            'name' => $license->name,
            'expire_in_days' => $now->diffInDays($expire),
        ]);
    }

    // Fallback to old activation_keys table for backward compatibility
    $exists = DB::table('activation_keys')->where('hardware_id', $lookupId)->exists();

    if ($exists) {
        // Return legacy format for old clients
        if ($request->expectsJson() || $request->query('format') === 'json') {
            return response()->json([
                'name' => 'Legacy User',
                'expire_in_days' => 999, // Legacy keys don't expire
            ]);
        } else {
            return response('OK', 200)->header('Content-Type', 'text/plain');
        }
    }

    return response()->json(['error' => 'not_found'], 404);
});

// License management web interface
Route::get('/licenses', [LicenseController::class, 'index'])->name('licenses.index');
Route::post('/licenses', [LicenseController::class, 'store'])->name('licenses.store');
Route::delete('/licenses/{id}', [LicenseController::class, 'destroy'])->name('licenses.destroy');
Route::post('/licenses/{id}/extend', [LicenseController::class, 'extend'])->name('licenses.extend');

/*
|--------------------------------------------------------------------------
| Storage Routes
|--------------------------------------------------------------------------
*/

Route::get('/storage/youtube-videos/{filename}', function ($filename) {
    $path = 'youtube-videos/' . $filename;

    if (!Storage::disk('local')->exists($path)) {
        abort(404, 'Video file not found');
    }

    $file = Storage::disk('local')->get($path);
    $mimeType = Storage::disk('local')->mimeType($path);

    return Response::make($file, 200, [
        'Content-Type' => $mimeType,
        'Content-Disposition' => 'inline; filename="' . $filename . '"',
        'Accept-Ranges' => 'bytes',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->name('storage.youtube-videos');
// API Routes cho Data Posts
Route::get('/api/data-posts', function () {
    try {
        $posts = DataPost::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API Routes cho Images Data
Route::get('/api/images-data', function () {
    try {
        $query = ImagesData::query();

        // Filter by type if provided
        if (request()->has('type')) {
            $query->where('type', request()->type);
        }

        $images = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API chi tiết Data Post
Route::get('/api/data-posts/{id}', function ($id) {
    try {
        $post = DataPost::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy bài viết'
        ], 404);
    }
});

// API chi tiết Images Data
Route::get('/api/images-data/{id}', function ($id) {
    try {
        $image = ImagesData::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $image
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy media'
        ], 404);
    }
});

// Route cho HTML page
Route::get('/media-page', function () {
    return view('media-page'); // Nếu bạn muốn dùng Blade view
});

// Hoặc serve HTML trực tiếp
Route::get('/dashboard', function () {
    return response()->file(public_path('dashboard.html'));
});
// API lấy tất cả danh mục
Route::get('/api/categories', function () {
    try {
        $categories = DanhmucData::orderBy('ten_danh_muc', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy bài viết theo danh mục
Route::get('/api/categories/{categoryId}/posts', function ($categoryId) {
    try {
        $query = DataPost::query();

        if ($categoryId !== 'all') {
            $query->where('id_danhmuc_data', $categoryId);
        }

        $posts = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy ảnh theo danh mục
Route::get('/api/categories/{categoryId}/images', function ($categoryId) {
    try {
        $query = ImagesData::where('type', 'image');

        if ($categoryId !== 'all') {
            $query->where('id_danhmuc_data', $categoryId);
        }

        $images = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy video theo danh mục
Route::get('/api/categories/{categoryId}/videos', function ($categoryId) {
    try {
        $query = ImagesData::where('type', 'video');

        if ($categoryId !== 'all') {
            $query->where('id_danhmuc_data', $categoryId);
        }

        $videos = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $videos
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy chi tiết danh mục
Route::get('/api/categories/{id}', function ($id) {
    try {
        $category = DanhmucData::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy danh mục'
        ], 404);
    }
});

// Thêm vào đầu file routes/web.php hoặc routes/api.php


// API Routes cho Data Posts NH
Route::get('/api/data-posts-nh', function () {
    try {
        $query = DataPostNH::query();

        // Filter by type if provided
        if (request()->has('type')) {
            $query->where('type', request()->type);
        }

        $posts = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API Routes cho Data Images NH
Route::get('/api/data-images-nh', function () {
    try {
        $query = DataImagesNH::query();

        // Filter by type if provided
        if (request()->has('type')) {
            $query->where('type', request()->type);
        }

        $images = $query->orderBy('id', 'desc')->get(); // Sử dụng 'id' thay vì 'created_at' vì DataImagesNH không có timestamps

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API chi tiết Data Post NH
Route::get('/api/data-posts-nh/{id}', function ($id) {
    try {
        $post = DataPostNH::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy bài viết'
        ], 404);
    }
});

// API chi tiết Data Images NH
Route::get('/api/data-images-nh/{id}', function ($id) {
    try {
        $image = DataImagesNH::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $image
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy media'
        ], 404);
    }
});

// API lấy tất cả danh mục NH
Route::get('/api/danhmuc-nhs', function () {
    try {
        $categories = DanhmucNHS::orderBy('ten_danh_muc', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy bài viết theo danh mục NH
Route::get('/api/danhmuc-nhs/{categoryId}/posts', function ($categoryId) {
    try {
        $query = DataPostNH::query();

        if ($categoryId !== 'all') {
            $query->where('id_danhmuc_data', $categoryId);
        }

        $posts = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy ảnh theo danh mục NH
Route::get('/api/danhmuc-nhs/{categoryId}/images', function ($categoryId) {
    try {
        $query = DataImagesNH::where('type', 'image');

        if ($categoryId !== 'all') {
            $query->where('id_danhmuc_data', $categoryId);
        }

        $images = $query->orderBy('id', 'desc')->get(); // Sử dụng 'id' thay vì 'created_at'

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy video theo danh mục NH
Route::get('/api/danhmuc-nhs/{categoryId}/videos', function ($categoryId) {
    try {
        $query = DataImagesNH::where('type', 'video');

        if ($categoryId !== 'all') {
            $query->where('id_danhmuc_data', $categoryId);
        }

        $videos = $query->orderBy('id', 'desc')->get(); // Sử dụng 'id' thay vì 'created_at'

        return response()->json([
            'success' => true,
            'data' => $videos
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy chi tiết danh mục NH
Route::get('/api/danhmuc-nhs/{id}', function ($id) {
    try {
        $category = DanhmucNHS::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy danh mục'
        ], 404);
    }
});

// API Routes cho Data Posts BX
Route::get('/api/data-posts-bx', function () {
    try {
        $query = DataPostBX::query();

        // Filter by type if provided
        if (request()->has('type')) {
            $query->where('type', request()->type);
        }

        $posts = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API Routes cho Data Images BX
Route::get('/api/data-images-bx', function () {
    try {
        $query = DataImagesBX::query();

        // Filter by type if provided
        if (request()->has('type')) {
            $query->where('type', request()->type);
        }

        $images = $query->orderBy('created_at', 'desc')->get(); // Đã thêm timestamps cho DataImagesBX

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API chi tiết Data Post BX
Route::get('/api/data-posts-bx/{id}', function ($id) {
    try {
        $post = DataPostBX::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy bài viết'
        ], 404);
    }
});

// API chi tiết Data Images BX
Route::get('/api/data-images-bx/{id}', function ($id) {
    try {
        $image = DataImagesBX::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $image
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy media'
        ], 404);
    }
});

// API lấy tất cả danh mục BX
Route::get('/api/danhmuc-bxs', function () {
    try {
        $categories = DanhmucBX::orderBy('ten_danh_muc', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy bài viết theo danh mục BX
Route::get('/api/danhmuc-bxs/{categoryId}/posts', function ($categoryId) {
    try {
        $query = DataPostBX::query();

        if ($categoryId !== 'all') {
            $query->where('id_danhmuc_data', $categoryId);
        }

        $posts = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy ảnh theo danh mục BX
Route::get('/api/danhmuc-bxs/{categoryId}/images', function ($categoryId) {
    try {
        $query = DataImagesBX::where('type', 'image');

        if ($categoryId !== 'all') {
            $query->where('id_danhmuc_data', $categoryId);
        }

        $images = $query->orderBy('created_at', 'desc')->get(); // Đã thêm timestamps

        return response()->json([
            'success' => true,
            'data' => $images
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy video theo danh mục BX
Route::get('/api/danhmuc-bxs/{categoryId}/videos', function ($categoryId) {
    try {
        $query = DataImagesBX::where('type', 'video');

        if ($categoryId !== 'all') {
            $query->where('id_danhmuc_data', $categoryId);
        }

        $videos = $query->orderBy('created_at', 'desc')->get(); // Đã thêm timestamps

        return response()->json([
            'success' => true,
            'data' => $videos
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Lỗi tải dữ liệu: ' . $e->getMessage()
        ], 500);
    }
});

// API lấy chi tiết danh mục BX
Route::get('/api/danhmuc-bxs/{id}', function ($id) {
    try {
        $category = DanhmucBX::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy danh mục'
        ], 404);
    }
});