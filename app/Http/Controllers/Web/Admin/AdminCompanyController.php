<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Managers\OwnedCompanyManager;
use App\Models\OwnedCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class AdminCompanyController extends Controller
{
    /** Logos stored under public/ so they can be committed to the repo. */
    private const LOGO_DIR = 'company-logos';

    public function __construct(
        private OwnedCompanyManager $ownedCompanyManager
    ) {}

    public function index(): View
    {
        $companies = $this->ownedCompanyManager->getAll();

        return view('admin.companies.index', compact('companies'));
    }

    public function show(OwnedCompany $ownedCompany): View
    {
        $ownedCompany->loadCount('employees');

        return view('admin.companies.show', compact('ownedCompany'));
    }

    public function update(Request $request, OwnedCompany $ownedCompany): RedirectResponse
    {
        $request->merge(['color' => $request->input('color') ?: null]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:owned_companies,slug,' . $ownedCompany->id],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'string', 'size:7', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $logoDir = public_path(self::LOGO_DIR);
        $currentLogo = $ownedCompany->logo;

        if (! empty($validated['remove_logo']) || $request->hasFile('logo')) {
            if ($currentLogo) {
                $path = $logoDir . '/' . basename($currentLogo);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
            $currentLogo = null;
        }

        if ($request->hasFile('logo')) {
            if (! File::isDirectory($logoDir)) {
                File::makeDirectory($logoDir, 0755, true);
            }
            $file = $request->file('logo');
            $filename = $ownedCompany->id . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($logoDir, $filename);
            $currentLogo = $filename;
        }

        unset($validated['logo'], $validated['remove_logo']);
        $validated['logo'] = $currentLogo;

        $this->ownedCompanyManager->update($ownedCompany, $validated);

        return redirect()->route('admin.companies.show', $ownedCompany)->with('success', 'Company updated.');
    }
}
