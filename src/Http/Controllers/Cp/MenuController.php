<?php

namespace Shopper\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Shopper\Http\Controllers\Controller;
use Shopper\Models\Menu;
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'handle' => 'nullable|string|max:255|unique:menus,handle',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $menu = $this->menuService->createMenu($validated);

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

    public function update(Request $request, string $handle)
    {
        $menu = Menu::where('handle', $handle)->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $this->menuService->updateMenu($menu, $validated);

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
}
