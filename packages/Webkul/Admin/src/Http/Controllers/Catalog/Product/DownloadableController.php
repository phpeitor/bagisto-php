<?php

namespace Webkul\Admin\Http\Controllers\Catalog\Product;

use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Product\Repositories\ProductDownloadableLinkRepository;
use Webkul\Product\Repositories\ProductRepository;

class DownloadableController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductDownloadableLinkRepository $productDownloadableLinkRepository
    ) {}

    /**
     * Returns the compare items of the customer.
     */
    public function options(int $id): JsonResponse
    {
        $product = $this->productRepository->findOrFail($id);

        $links = [];

        foreach ($product->downloadable_links as $link) {
            $links[] = [
                'id' => $link->id,
                'title' => $link->title,
                'price' => $link->price,
                'formatted_price' => core()->formatPrice($link->price),
            ];
        }

        return new JsonResponse([
            'data' => $links,
        ]);
    }

    /**
     * Create a single downloadable link for the product without touching the rest of the product form.
     */
    public function storeLink(int $id): JsonResponse
    {
        $this->productRepository->findOrFail($id);

        $link = $this->productDownloadableLinkRepository->create(array_merge(
            $this->validateLink(),
            ['product_id' => $id]
        ));

        return new JsonResponse($link);
    }

    /**
     * Update a single downloadable link for the product without touching the rest of the product form.
     */
    public function updateLink(int $id, int $linkId): JsonResponse
    {
        $link = $this->productDownloadableLinkRepository->findOneWhere([
            'id'         => $linkId,
            'product_id' => $id,
        ]);

        if (! $link) {
            abort(404);
        }

        $link = $this->productDownloadableLinkRepository->update($this->validateLink(), $linkId);

        return new JsonResponse($link);
    }

    /**
     * Delete a single downloadable link for the product without touching the rest of the product form.
     */
    public function destroyLink(int $id, int $linkId): JsonResponse
    {
        $link = $this->productDownloadableLinkRepository->findOneWhere([
            'id'         => $linkId,
            'product_id' => $id,
        ]);

        if (! $link) {
            abort(404);
        }

        $this->productDownloadableLinkRepository->delete($linkId);

        return new JsonResponse(['message' => trans('admin::app.catalog.products.edit.types.downloadable.links.delete-success')]);
    }

    /**
     * Validate the request and shape it into the link's fillable attributes,
     * clearing whichever file/url pair does not match the selected type so
     * switching type doesn't leave stale data behind.
     */
    protected function validateLink(): array
    {
        $data = request()->validate([
            'title'       => 'required',
            'price'       => 'required|numeric|min:0',
            'downloads'   => 'required|integer|min:1',
            'sort_order'  => 'nullable|integer',
            'type'        => 'required|in:file,url',
            'file'        => 'required_if:type,file',
            'file_name'   => 'nullable',
            'url'         => 'required_if:type,url',
            'sample_type'      => 'nullable|in:file,url',
            'sample_file'      => 'required_if:sample_type,file',
            'sample_file_name' => 'nullable',
            'sample_url'       => 'required_if:sample_type,url',
        ]);

        return [
            core()->getRequestedLocaleCode() => [
                'title' => $data['title'],
            ],
            'price'             => $data['price'],
            'downloads'         => $data['downloads'],
            'sort_order'        => $data['sort_order'] ?? 0,
            'type'              => $data['type'],
            'file'              => $data['type'] === 'file' ? $data['file'] : null,
            'file_name'         => $data['type'] === 'file' ? ($data['file_name'] ?? null) : null,
            'url'               => $data['type'] === 'url' ? $data['url'] : null,
            'sample_type'       => $data['sample_type'] ?? null,
            'sample_file'       => ($data['sample_type'] ?? null) === 'file' ? $data['sample_file'] : null,
            'sample_file_name'  => ($data['sample_type'] ?? null) === 'file' ? ($data['sample_file_name'] ?? null) : null,
            'sample_url'        => ($data['sample_type'] ?? null) === 'url' ? $data['sample_url'] : null,
        ];
    }
}
