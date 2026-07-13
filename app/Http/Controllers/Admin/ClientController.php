<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Support\DisplayOrder;
use App\Support\PublicMedia;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        return view('admin.clients.index', ['clients' => Client::orderBy('display_order')->get()]);
    }

    public function create()
    {
        return view('admin.clients.form', ['client' => new Client, 'maxOrder' => DisplayOrder::maxForForm(Client::class)]);
    }

    public function store(Request $request)
    {
        $data = $this->data($request, true);
        $data['logo_path'] = PublicMedia::store($request->file('logo'), 'clients');
        try {
            DisplayOrder::save(new Client, $data);
        } catch (\Throwable $e) {
            PublicMedia::delete($data['logo_path']);
            throw $e;
        }

return redirect()->route('admin.clients.index')->with('ok', 'Klien ditambahkan.');
    }

    public function edit(Client $client)
    {
        return view('admin.clients.form', compact('client') + ['maxOrder' => DisplayOrder::maxForForm(Client::class, $client)]);
    }

    public function update(Request $request, Client $client)
    {
        $data = $this->data($request, false);
        $old = $client->logo_path;
        $new = null;
        if ($request->hasFile('logo')) {
            $new = PublicMedia::store($request->file('logo'), 'clients');
            $data['logo_path'] = $new;
        } try {
            DisplayOrder::save($client, $data);
        } catch (\Throwable $e) {
            PublicMedia::delete($new);
            throw $e;
        } if ($new && $old !== $new) {
            PublicMedia::delete($old);
        }

return redirect()->route('admin.clients.index')->with('ok', 'Klien diperbarui.');
    }

    public function destroy(Client $client)
    {
        $path = $client->logo_path;
        DisplayOrder::delete($client);
        PublicMedia::delete($path);

        return back()->with('ok', 'Klien dihapus.');
    }

    private function data(Request $request, bool $required): array
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:160'], 'website_url' => ['nullable', 'url:http,https', 'max:500'], 'industry' => ['nullable', 'string', 'max:100'], 'logo' => [$required ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], 'display_order' => ['required', 'integer', 'min:1']]);
        unset($data['logo']);

        return $data + ['is_active' => $request->boolean('is_active')];
    }
}
