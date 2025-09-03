<?php

namespace Shopper\Http\Controllers\Cp;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Shopper\Http\Controllers\Controller;
use Shopper\Http\Requests\Menu\StoreMenuRequest;
use Shopper\Http\Requests\Menu\UpdateMenuRequest;
use Shopper\Http\Requests\Menu\StoreMenuItemRequest;
use Shopper\Http\Requests\Menu\UpdateMenuItemRequest;
use Shopper\Http\Requests\Menu\ReorderMenuItemsRequest;
use Shopper\Models\Menu;
use Shopper\Models\MenuItem;
use Shopper\Services\MenuService;

class MenuController extends Controller
{
    public function __construct(
        private MenuService $menuService
    ) {}

    public function index(): Response
    {
        $menus = $this->menuService->getAllMenus();

        return Inertia::render('menus/index', [
            'menus' => $menus,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('menus/create');
    }

    public function store(StoreMenuRequest $request)
    {
        $menu = $this->menuService->createMenu($request->validated());

        return redirect()
            ->route('cp.menus.edit', $menu->handle)
            ->with('success', 'Menu created successfully.');
    }

    public function edit(string $handle): Response
    {
        $menu = Menu::where('handle', $handle)->firstOrFail();
        $menuData = $this->menuService->getMenu($handle);

        return Inertia::render('menus/edit', [
            'menu' => $menuData,
        ]);
    }

    public function update(UpdateMenuRequest $request, string $handle)
    {
        $menu = Menu::where('handle', $handle)->firstOrFail();
        $this->menuService->updateMenu($menu, $request->validated());

        return back()->with('success', 'Menu updated successfully.');
    }

    public function destroy(string $handle)
    {
        $menu = Menu::where('handle', $handle)->firstOrFail();
        $this->menuService->deleteMenu($menu);

        return redirect()
            ->route('cp.menus.index')
            ->with('success', 'Menu deleted successfully.');
    }

    public function duplicate(string $handle)
    {
        $menu = Menu::where('handle', $handle)->firstOrFail();
        $newMenu = $this->menuService->duplicateMenu($menu);

        return redirect()
            ->route('cp.menus.edit', $newMenu->handle)
            ->with('success', 'Menu duplicated successfully.');
    }

    // Menu Items Management
    public function storeItem(StoreMenuItemRequest $request, string $menuHandle): JsonResponse
    {
        $menu = Menu::where('handle', $menuHandle)->firstOrFail();
        $item = $this->menuService->createMenuItem($menu, $request->validated());

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => 'Menu item created successfully.',
        ]);
    }

    public function updateItem(UpdateMenuItemRequest $request, string $menuHandle, MenuItem $item): JsonResponse
    {
        $item = $this->menuService->updateMenuItem($item, $request->validated());

        return response()->json([
            'success' => true,
            'item' => $item,
            'message' => 'Menu item updated successfully.',
        ]);
    }

    public function destroyItem(string $menuHandle, MenuItem $item): JsonResponse
    {
        $this->menuService->deleteMenuItem($item);

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully.',
        ]);
    }

    public function reorderItems(ReorderMenuItemsRequest $request, string $menuHandle): JsonResponse
    {
        $menu = Menu::where('handle', $menuHandle)->firstOrFail();
        $this->menuService->reorderMenuItems($menu, $request->validated()['items']);

        return response()->json([
            'success' => true,
            'message' => 'Menu items reordered successfully.',
        ]);
    }

    public function moveItem(Request $request, string $menuHandle, MenuItem $item): JsonResponse
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
