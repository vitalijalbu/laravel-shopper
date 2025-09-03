<?php

namespace Shopper\Http\Controllers\Cp;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Http\Controllers\Controller;
use Shopper\Models\Menu;
use Shopper\Models\MenuItem;
use Shopper\Services\MenuService;

class MenuItemController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    public function store(Request $request, string $menuHandle): JsonResponse
    {
        $menu = Menu::where('handle', $menuHandle)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'type' => 'required|string|in:link,collection,entry,external',
            'parent_id' => 'nullable|exists:menu_items,id',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'data' => 'nullable|array',
            'is_enabled' => 'boolean',
            'opens_in_new_window' => 'boolean',
            'css_class' => 'nullable|string|max:255',
        ]);

        $item = $this->menuService->createMenuItem($menu, $validated);

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => 'Menu item created successfully.',
        ]);
    }

    public function update(Request $request, string $menuHandle, MenuItem $item): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string|max:255',
            'type' => 'required|string|in:link,collection,entry,external',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'data' => 'nullable|array',
            'is_enabled' => 'boolean',
            'opens_in_new_window' => 'boolean',
            'css_class' => 'nullable|string|max:255',
        ]);

        $item = $this->menuService->updateMenuItem($item, $validated);

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => 'Menu item updated successfully.',
        ]);
    }

    public function destroy(string $menuHandle, MenuItem $item): JsonResponse
    {
        $this->menuService->deleteMenuItem($item);

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully.',
        ]);
    }

    public function reorder(Request $request, string $menuHandle): JsonResponse
    {
        $menu = Menu::where('handle', $menuHandle)->firstOrFail();

        $validated = $request->validate([
            'items' => 'required|array',
        ]);

        $this->menuService->reorderMenuItems($menu, $validated['items']);

        return response()->json([
            'success' => true,
            'message' => 'Menu items reordered successfully.',
        ]);
    }

    public function move(Request $request, string $menuHandle, MenuItem $item): JsonResponse
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:menu_items,id',
            'sort_order' => 'required|integer|min:0',
        ]);

        $item = $this->menuService->moveMenuItem(
            $item,
            $validated['parent_id'],
            $validated['sort_order']
        );

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => 'Menu item moved successfully.',
        ]);
    }
}
