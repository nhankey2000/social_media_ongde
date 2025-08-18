{{-- File: resources/views/filament/resources/image-menu-resource/preview-image.blade.php --}}

<div class="space-y-4">
    <div class="text-center">
        @if($record->image_path && Storage::disk('public')->exists($record->image_path))
            <img
                    src="{{ asset('storage/' . $record->image_path) }}"
                    alt="Preview Image - {{ $record->menuCategory->name }}"
                    class="max-w-full h-auto mx-auto rounded-lg shadow-lg"
                    style="max-height: 400px;"
                    onload="this.style.opacity='1'"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block'"
                    style="opacity: 0; transition: opacity 0.3s;"
            >
            <div style="display: none;" class="text-center text-gray-500 p-8">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-2">Không thể tải ảnh</p>
            </div>
        @else
            <div class="text-center text-gray-500 p-8">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-2">Ảnh không tồn tại</p>
            </div>
        @endif
    </div>

    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Danh mục:</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $record->menuCategory->name }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Đường dẫn:</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100 break-all">{{ $record->image_path }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ngày tạo:</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $record->created_at->format('d/m/Y H:i:s') }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cập nhật lần cuối:</dt>
                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $record->updated_at->format('d/m/Y H:i:s') }}</dd>
            </div>
        </dl>
    </div>

    @if(Storage::disk('public')->exists($record->image_path))
        @php
            $filePath = Storage::disk('public')->path($record->image_path);
            $fileSize = number_format(filesize($filePath) / 1024, 2);
            $imageInfo = getimagesize($filePath);
        @endphp

        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
            <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-2">Thông tin file:</h4>
            <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-3 text-sm">
                <div>
                    <dt class="font-medium text-blue-700 dark:text-blue-300">Kích thước:</dt>
                    <dd class="text-blue-600 dark:text-blue-400">{{ $fileSize }} KB</dd>
                </div>

                @if($imageInfo)
                    <div>
                        <dt class="font-medium text-blue-700 dark:text-blue-300">Độ phân giải:</dt>
                        <dd class="text-blue-600 dark:text-blue-400">{{ $imageInfo[0] }} x {{ $imageInfo[1] }} px</dd>
                    </div>

                    <div>
                        <dt class="font-medium text-blue-700 dark:text-blue-300">Định dạng:</dt>
                        <dd class="text-blue-600 dark:text-blue-400">{{ strtoupper(image_type_to_extension($imageInfo[2], false)) }}</dd>
                    </div>
                @endif
            </dl>
        </div>
    @endif
</div>