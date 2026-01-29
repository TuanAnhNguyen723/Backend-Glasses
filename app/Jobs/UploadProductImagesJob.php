<?php

namespace App\Jobs;

use App\Models\ProductImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadProductImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Số lần thử lại khi job thất bại.
     */
    public int $tries = 3;

    /**
     * Thư mục tạm chứa file (relative to storage/app), VD: temp-product-uploads/123_1738_abc
     */
    public function __construct(
        public int $productId,
        public string $tempDir
    ) {}

    public function handle(): void
    {
        $localDisk = Storage::disk('local');
        if (!$localDisk->exists($this->tempDir)) {
            Log::warning("UploadProductImagesJob: temp dir not found: {$this->tempDir}");
            return;
        }

        $files = $localDisk->files($this->tempDir);
        sort($files);

        /** @var \Illuminate\Database\Eloquent\Collection<int, ProductImage> $images */
        $images = ProductImage::where('product_id', $this->productId)
            ->whereNull('image_path')
            ->orderBy('sort_order')
            ->get();

        if ($images->count() !== count($files)) {
            Log::warning("UploadProductImagesJob: count mismatch product_id={$this->productId}");
            $this->cleanupTempDir($localDisk);
            return;
        }

        foreach ($images as $index => $image) {
            $relativePath = $files[$index] ?? null;
            if (!$relativePath || !$localDisk->exists($relativePath)) {
                continue;
            }

            try {
                $contents = $localDisk->get($relativePath);
                $extension = pathinfo($relativePath, PATHINFO_EXTENSION) ?: 'jpg';
                $b2Path = 'products/' . $this->productId . '_' . time() . '_' . $index . '.' . $extension;

                $stored = Storage::disk('backblaze')->put($b2Path, $contents);
                if ($stored) {
                    $image->update(['image_path' => $b2Path]);
                }
            } catch (\Throwable $e) {
                Log::error("UploadProductImagesJob: upload failed index={$index}: " . $e->getMessage());
            }

            $localDisk->delete($relativePath);
        }

        $this->cleanupTempDir($localDisk);
    }

    private function cleanupTempDir($localDisk): void
    {
        try {
            if ($localDisk->exists($this->tempDir)) {
                $localDisk->deleteDirectory($this->tempDir);
            }
        } catch (\Throwable $e) {
            Log::warning("UploadProductImagesJob: cleanup failed: " . $e->getMessage());
        }
    }
}
