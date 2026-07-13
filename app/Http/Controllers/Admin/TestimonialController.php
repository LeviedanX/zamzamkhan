<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Support\DisplayOrder;
use App\Support\PublicMedia;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        return view('admin.testimonials.index', ['testimonials' => Testimonial::orderBy('display_order')->get()]);
    }

    public function create()
    {
        return view('admin.testimonials.form', ['testimonial' => new Testimonial, 'maxOrder' => DisplayOrder::maxForForm(Testimonial::class)]);
    }

    public function store(Request $request)
    {
        $data = $this->data($request, true);
        $data['image_path'] = PublicMedia::store($request->file('image'), 'testimonials');
        try {
            DisplayOrder::save(new Testimonial, $data);
        } catch (\Throwable $e) {
            PublicMedia::delete($data['image_path']);
            throw $e;
        }

return redirect()->route('admin.testimonials.index')->with('ok', 'Testimoni ditambahkan.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', compact('testimonial') + ['maxOrder' => DisplayOrder::maxForForm(Testimonial::class, $testimonial)]);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $this->data($request, false);
        $old = $testimonial->image_path;
        $new = null;
        if ($request->hasFile('image')) {
            $new = PublicMedia::store($request->file('image'), 'testimonials');
            $data['image_path'] = $new;
        } try {
            DisplayOrder::save($testimonial, $data);
        } catch (\Throwable $e) {
            PublicMedia::delete($new);
            throw $e;
        } if ($new && $old !== $new) {
            PublicMedia::delete($old);
        }

return redirect()->route('admin.testimonials.index')->with('ok', 'Testimoni diperbarui.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $path = $testimonial->image_path;
        DisplayOrder::delete($testimonial);
        PublicMedia::delete($path);

        return back()->with('ok', 'Testimoni dihapus.');
    }

    private function data(Request $request, bool $required): array
    {
        $data = $request->validate(['client_name' => ['required', 'string', 'max:160'], 'service_name' => ['nullable', 'string', 'max:160'], 'content' => ['required', 'string', 'max:1500'], 'image_alt' => ['nullable', 'string', 'max:255'], 'image' => [$required ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'], 'display_order' => ['required', 'integer', 'min:1']]);
        unset($data['image']);

        return $data + ['is_active' => $request->boolean('is_active')];
    }
}
