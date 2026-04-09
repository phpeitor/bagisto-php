<?php

namespace Webkul\Shop\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $logoExtension = strtolower(pathinfo((string) $this->logo_path, PATHINFO_EXTENSION));
        $bannerExtension = strtolower(pathinfo((string) $this->banner_path, PATHINFO_EXTENSION));

        $isLogoGif = $logoExtension === 'gif';
        $isBannerGif = $bannerExtension === 'gif';

        $logoOriginalUrl = $this->logo_path ? Storage::url($this->logo_path) : null;
        $bannerOriginalUrl = $this->banner_path ? Storage::url($this->banner_path) : null;

        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'position' => $this->position,
            'display_mode' => $this->display_mode,
            'description' => $this->description,
            'logo' => $this->when($this->logo_path, [
                'small_image_url' => $isLogoGif ? $logoOriginalUrl : url('cache/small/'.$this->logo_path),
                'medium_image_url' => $isLogoGif ? $logoOriginalUrl : url('cache/medium/'.$this->logo_path),
                'large_image_url' => $isLogoGif ? $logoOriginalUrl : url('cache/large/'.$this->logo_path),
                'original_image_url' => $logoOriginalUrl,
            ]),
            'banner' => $this->when($this->banner_path, [
                'small_image_url' => $isBannerGif ? $bannerOriginalUrl : url('cache/small/'.$this->banner_path),
                'medium_image_url' => $isBannerGif ? $bannerOriginalUrl : url('cache/medium/'.$this->banner_path),
                'large_image_url' => $isBannerGif ? $bannerOriginalUrl : url('cache/large/'.$this->banner_path),
                'original_image_url' => $bannerOriginalUrl,
            ]),
            'meta' => [
                'title' => $this->meta_title,
                'keywords' => $this->meta_keywords,
                'description' => $this->meta_description,
            ],
            'translations' => $this->translations,
            'additional' => $this->additional,
        ];
    }
}
