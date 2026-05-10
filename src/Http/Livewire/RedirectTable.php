<?php

namespace Step2dev\LazySeoRedirect\Http\Livewire;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Livewire\Component;
use Livewire\WithPagination;
use Step2dev\LazySeoRedirect\Models\SeoRedirect;

class RedirectTable extends Component
{
    use WithPagination;

    public function render()
    {
        return app(ViewFactory::class)->make('lazy-seo-redirect::livewire.redirect-table', [
            'redirects' => SeoRedirect::query()->latest()->paginate(10),
        ]);
    }
}
