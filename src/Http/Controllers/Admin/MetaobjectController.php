<?php

namespace LaravelShopper\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use LaravelShopper\Models\Metaobject;
use LaravelShopper\Models\MetaobjectDefinition;

class MetaobjectController extends Controller
{
    public function definitions(): Response
    {
        $definitions = MetaobjectDefinition::with(['metaobjects'])
            ->withCount('metaobjects')
            ->latest()
            ->get();

        return Inertia::render('Metaobjects/definitions-index', [
            'definitions' => $definitions,
        ]);
    }

    public function createDefinition(): Response
    {
        return Inertia::render('Metaobjects/definition-form', [
            'definition' => null,
            'fieldTypes' => $this->getAvailableFieldTypes(),
        ]);
    }

    public function storeDefinition(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255|unique:shopper_metaobject_definitions,type',
            'description' => 'nullable|string',
            'is_single' => 'boolean',
            'fields' => 'required|array|min:1',
            'fields.*.key' => 'required|string',
            'fields.*.name' => 'required|string',
            'fields.*.type' => 'required|string',
            'fields.*.required' => 'boolean',
            'fields.*.description' => 'nullable|string',
        ]);

        $definition = MetaobjectDefinition::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_single' => $validated['is_single'] ?? false,
            'capabilities' => [
                'publishable' => true,
                'searchable' => true,
                'fields' => $validated['fields'],
            ],
            'displayable_fields' => [$validated['fields'][0]['key'] ?? null],
        ]);

        return redirect()->route('admin.metaobjects.definitions')
            ->with('success', 'Metaobject definition created successfully');
    }

    public function editDefinition(MetaobjectDefinition $definition): Response
    {
        return Inertia::render('Metaobjects/definition-form', [
            'definition' => $definition,
            'fieldTypes' => $this->getAvailableFieldTypes(),
        ]);
    }

    public function updateDefinition(Request $request, MetaobjectDefinition $definition)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fields' => 'required|array|min:1',
            'fields.*.key' => 'required|string',
            'fields.*.name' => 'required|string',
            'fields.*.type' => 'required|string',
            'fields.*.required' => 'boolean',
            'fields.*.description' => 'nullable|string',
        ]);

        $definition->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'capabilities' => array_merge($definition->capabilities ?? [], [
                'fields' => $validated['fields'],
            ]),
        ]);

        return redirect()->route('admin.metaobjects.definitions')
            ->with('success', 'Metaobject definition updated successfully');
    }

    public function index(Request $request): Response
    {
        $query = Metaobject::with(['definition']);

        // Filter by definition
        if ($definitionId = $request->get('definition')) {
            $query->where('definition_id', $definitionId);
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where('handle', 'LIKE', "%{$search}%");
        }

        // Published filter
        if ($request->has('published')) {
            if ($request->get('published') === '1') {
                $query->published();
            } else {
                $query->draft();
            }
        }

        $metaobjects = $query->latest()->paginate(20)->withQueryString();

        $definitions = MetaobjectDefinition::orderBy('name')->get();

        return Inertia::render('Metaobjects/metaobjects-index', [
            'metaobjects' => $metaobjects,
            'definitions' => $definitions,
            'filters' => $request->only(['definition', 'search', 'published']),
        ]);
    }

    public function create(Request $request): Response
    {
        $definitionId = $request->get('definition');
        $definition = null;

        if ($definitionId) {
            $definition = MetaobjectDefinition::findOrFail($definitionId);
        }

        $definitions = MetaobjectDefinition::orderBy('name')->get();

        return Inertia::render('Metaobjects/metaobject-form', [
            'metaobject' => null,
            'definition' => $definition,
            'definitions' => $definitions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'definition_id' => 'required|exists:shopper_metaobject_definitions,id',
            'handle' => 'required|string|max:255|unique:shopper_metaobjects,handle',
            'fields' => 'required|array',
            'published_at' => 'nullable|date',
        ]);

        $metaobject = Metaobject::create($validated);

        return redirect()->route('admin.metaobjects.show', $metaobject)
            ->with('success', 'Metaobject created successfully');
    }

    public function show(Metaobject $metaobject): Response
    {
        $metaobject->load(['definition']);

        return Inertia::render('Metaobjects/metaobject-show', [
            'metaobject' => $metaobject,
        ]);
    }

    public function edit(Metaobject $metaobject): Response
    {
        $metaobject->load(['definition']);
        $definitions = MetaobjectDefinition::orderBy('name')->get();

        return Inertia::render('Metaobjects/metaobject-form', [
            'metaobject' => $metaobject,
            'definition' => $metaobject->definition,
            'definitions' => $definitions,
        ]);
    }

    public function update(Request $request, Metaobject $metaobject)
    {
        $validated = $request->validate([
            'handle' => 'required|string|max:255|unique:shopper_metaobjects,handle,'.$metaobject->id,
            'fields' => 'required|array',
            'published_at' => 'nullable|date',
        ]);

        $metaobject->update($validated);

        return redirect()->route('admin.metaobjects.show', $metaobject)
            ->with('success', 'Metaobject updated successfully');
    }

    public function destroy(Metaobject $metaobject)
    {
        $metaobject->delete();

        return redirect()->route('admin.metaobjects.index')
            ->with('success', 'Metaobject deleted successfully');
    }

    public function publish(Metaobject $metaobject)
    {
        $metaobject->publish();

        return back()->with('success', 'Metaobject published successfully');
    }

    public function unpublish(Metaobject $metaobject)
    {
        $metaobject->unpublish();

        return back()->with('success', 'Metaobject unpublished successfully');
    }

    private function getAvailableFieldTypes(): array
    {
        return [
            ['key' => 'single_line_text', 'name' => 'Single line text', 'validations' => ['min_length', 'max_length']],
            ['key' => 'multi_line_text', 'name' => 'Multi-line text', 'validations' => ['min_length', 'max_length']],
            ['key' => 'rich_text', 'name' => 'Rich text', 'validations' => []],
            ['key' => 'number_integer', 'name' => 'Number (Integer)', 'validations' => ['min_value', 'max_value']],
            ['key' => 'number_decimal', 'name' => 'Number (Decimal)', 'validations' => ['min_value', 'max_value']],
            ['key' => 'date', 'name' => 'Date', 'validations' => []],
            ['key' => 'date_time', 'name' => 'Date and time', 'validations' => []],
            ['key' => 'boolean', 'name' => 'True or false', 'validations' => []],
            ['key' => 'color', 'name' => 'Color', 'validations' => []],
            ['key' => 'url', 'name' => 'URL', 'validations' => []],
            ['key' => 'json', 'name' => 'JSON', 'validations' => []],
            ['key' => 'file_reference', 'name' => 'File reference', 'validations' => []],
            ['key' => 'list.single_line_text', 'name' => 'List of single line text', 'validations' => []],
            ['key' => 'list.file_reference', 'name' => 'List of files', 'validations' => []],
        ];
    }
}
