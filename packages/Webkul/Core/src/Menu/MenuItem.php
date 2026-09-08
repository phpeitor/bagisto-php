<?php

namespace Webkul\Core\Menu;

use Illuminate\Support\Collection;

class MenuItem
{
    /**
     * Create a new MenuItem instance.
     *
     * @return void
     */
    public function __construct(
        public string $key,
        public string $name,
        public string $route,
        public int $sort,
        public string $icon,
        public Collection $children,
    ) {}

    /**
     * Get name of menu item.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the icon of menu item.
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Get current route.
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * Get the url of the menu item.
     */
    public function getUrl(): string
    {
        return route($this->getRoute());
    }

    /**
     * Get the key of the menu item.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Check weather menu item have children or not.
     */
    public function haveChildren(): bool
    {
        return $this->children->isNotEmpty();
    }

    /**
     * Get children of menu item.
     */
    public function getChildren(): Collection
    {
        if (! $this->haveChildren()) {
            return collect();
        }

        return $this->children;
    }

    /**
     * Check weather menu item is active or not.
     */
    public function isActive(): bool
    {
        if (request()->routeIs($this->getRoutePattern())) {
            return true;
        }

        if ($this->haveChildren()) {
            foreach ($this->getChildren() as $child) {
                if ($child->isActive()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the route name pattern used to match this item as active.
     *
     * Matches by route name namespace (e.g. "admin.customers.gdpr.*")
     * rather than by URL prefix, so sibling resources that happen to
     * share a URL prefix (e.g. "admin/customers" and "admin/customers/gdpr")
     * don't both light up as active at the same time.
     */
    private function getRoutePattern(): string
    {
        $segments = explode('.', $this->route);

        if (count($segments) < 3) {
            return $this->route;
        }

        array_pop($segments);

        return implode('.', $segments).'.*';
    }
}
