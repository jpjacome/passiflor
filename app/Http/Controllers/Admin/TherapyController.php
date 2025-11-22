<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Therapy;
use App\Models\TherapyPage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class TherapyController extends Controller
{
    public function __construct()
    {
        // Apply resource authorization using the TherapyPolicy
        $this->authorizeResource(\App\Models\Therapy::class, 'therapy');
    }
    public function index()
    {
        $therapies = Therapy::with(['author','therapist','assignedPatient'])->orderBy('created_at', 'desc')->paginate(12);
        return view('admin.therapies.index', compact('therapies'));
    }

    public function create()
    {
        $therapists = \App\Models\User::where('role', 'therapist')->orderBy('name')->get();
        $patients = \App\Models\User::where('role', 'patient')->orderBy('name')->get();
        return view('admin.therapies.create', compact('therapists', 'patients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|alpha_dash|unique:therapies,slug',
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            // hero/intro fields (collected at creation)
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'therapist_id' => 'nullable|exists:users,id',
            'assigned_patient_id' => 'nullable|exists:users,id',
            'published' => 'sometimes|boolean',
            'cover_image' => 'nullable|file|mimes:jpg,jpeg,png,svg,webp|max:3072',
            // note: hero page content is created from the therapy title + subtitle/description inputs
            // additional pages (only extra page types allowed)
            'pages' => 'nullable|array',
        ]);
        $data['author_id'] = $request->user()->id;
        $data['published'] = $request->boolean('published');

        // create therapy first so we have an id to store files under
    // persist therapist selection if provided
    $therapy = Therapy::create(Arr::except($data, ['cover_image']));

        // cover image
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('therapies/' . $therapy->id, 'public');
            $therapy->cover_image = $path;
            $therapy->save();
        }

        // create hero page from the provided title + subtitle/description (hero is always position 0)
        $therapy->pages()->create([
            'position' => 0,
            'type' => 'hero',
            'number' => null,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'body' => $data['description'] ?? null,
            'list_items' => null,
            'note' => null,
        ]);

        // handle additional pages (only non-hero types allowed here)
        if ($request->has('pages')) {
            foreach ($request->input('pages') as $i => $page) {
                $pageData = [
                    // shift positions by 1 because hero occupies position 0
                    'position' => $i + 1,
                    'type' => in_array($page['type'] ?? 'step', ['step', 'info']) ? $page['type'] : 'step',
                    'number' => $page['number'] ?? null,
                    'title' => $page['title'] ?? null,
                    'subtitle' => $page['subtitle'] ?? null,
                    'body' => $page['body'] ?? null,
                    'list_items' => isset($page['list_items']) ? array_values(array_filter($page['list_items'])) : null,
                    'note' => $page['note'] ?? null,
                ];

                $therapy->pages()->create($pageData);
            }
        }

        return redirect()->route('admin.therapies.index')->with('success', 'Therapy created.');
    }

    public function edit(Therapy $therapy)
    {
        $therapy->load('pages');
        $therapists = \App\Models\User::where('role', 'therapist')->orderBy('name')->get();
        $patients = \App\Models\User::where('role', 'patient')->orderBy('name')->get();
        return view('admin.therapies.edit', compact('therapy', 'therapists', 'patients'));
    }

    public function update(Request $request, Therapy $therapy)
    {
        $data = $request->validate([
            'slug' => 'required|alpha_dash|unique:therapies,slug,' . $therapy->id,
            // hero fields (now editable from edit form)
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'therapist_id' => 'nullable|exists:users,id',
            'assigned_patient_id' => 'nullable|exists:users,id',
            'published' => 'sometimes|boolean',
            'cover_image' => 'nullable|file|mimes:jpg,jpeg,png,svg,webp|max:3072',
            // pages (non-hero)
            'pages' => 'nullable|array',
        ]);

        if ($request->hasFile('cover_image')) {
            // delete old file if present
            if ($therapy->cover_image) {
                Storage::disk('public')->delete($therapy->cover_image);
            }
            $path = $request->file('cover_image')->store('therapies/' . $therapy->id, 'public');
            $data['cover_image'] = $path;
        }

        $data['published'] = $request->boolean('published');

        $therapy->update($data);

        // persist hero edits (title/subtitle/description) — update existing hero page or create one
        $heroPage = $therapy->pages()->firstWhere('type', 'hero');
        $heroAttrs = [
            'position' => 0,
            'type' => 'hero',
            'number' => null,
            'title' => $request->input('title'),
            'subtitle' => $request->input('subtitle'),
            'body' => $request->input('description'),
            'list_items' => null,
            'note' => null,
        ];

        if ($heroPage) {
            $heroPage->update($heroAttrs);
        } else {
            $therapy->pages()->create($heroAttrs);
        }

        // pages: preserve existing pages when possible, update or create new ones
        if ($request->has('pages')) {
            $incoming = $request->input('pages');
            $keptIds = $heroPage ? [$heroPage->id] : [];

            foreach ($incoming as $i => $page) {
                // positions start at 1 because hero is position 0
                $pageAttrs = [
                    'position' => $i + 1,
                    'type' => in_array($page['type'] ?? 'step', ['step', 'info']) ? $page['type'] : 'step',
                    'number' => $page['number'] ?? null,
                    'title' => $page['title'] ?? null,
                    'subtitle' => $page['subtitle'] ?? null,
                    'body' => $page['body'] ?? null,
                    'list_items' => isset($page['list_items']) ? array_values(array_filter($page['list_items'])) : null,
                    'note' => $page['note'] ?? null,
                ];

                if (!empty($page['id'])) {
                    $pageModel = TherapyPage::find($page['id']);
                    if ($pageModel && $pageModel->therapy_id == $therapy->id) {
                        $pageModel->update($pageAttrs);
                        $keptIds[] = $pageModel->id;
                        continue;
                    }
                }

                // create new page
                $new = $therapy->pages()->create($pageAttrs);
                $keptIds[] = $new->id;
            }

            // delete pages that were removed (but keep hero)
            $therapy->pages()->whereNotIn('id', $keptIds)->get()->each(function($p) {
                if ($p->image) {
                    Storage::disk('public')->delete($p->image);
                }
                $p->delete();
            });
        }

        return redirect()->route('admin.therapies.index')->with('success', 'Therapy updated.');
    }

    public function destroy(Therapy $therapy)
    {
        // Remove stored files for this therapy (cover + pages)
        try {
            Storage::disk('public')->deleteDirectory('therapies/' . $therapy->id);
        } catch (\Exception $e) {
            // ignore storage errors but log if needed
        }

        $therapy->delete();
        return redirect()->route('admin.therapies.index')->with('success', 'Therapy deleted.');
    }
}
