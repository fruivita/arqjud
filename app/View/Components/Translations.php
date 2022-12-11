<?php

namespace App\View\Components;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\View\Component;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Disponibiliza ao frontend as translations Laravel evitando-se que elas sejam
 * (re)enviadas a cada request.
 *
 * @link https://laravel.com/docs/9.x/blade
 * @link https://www.youtube.com/watch?v=IZIzcjDdPIw
 */
class Translations extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $locale = App::currentLocale();

        $translations = Cache::rememberForever("translations_{$locale}", function () use ($locale) {
            return array_merge(
                $this->phpTranslations($locale),
                $this->jsonTranslations($locale)
            );
        });

        return view('components.translations', [
            'translations' => $translations,
        ]);
    }

    /**
     * Translations existentes na aplicação armazenadas em formato PHP.
     *
     * @param  string  $locale
     * @return array<string, string>
     */
    private function phpTranslations(string $locale)
    {
        return (File::exists(lang_path($locale)))
            ? collect(File::allFiles(lang_path($locale)))
            ->filter(function (SplFileInfo $file) {
                return $file->getExtension() === 'php';
            })->flatMap(function (SplFileInfo $file) {
                return Arr::dot(File::getRequire($file->getRealPath()));
            })->toArray()
            : [];
    }

    /**
     * Translations existentes na aplicação armazenadas em formato JSON.
     *
     * @param  string  $locale
     * @return array<string, string>
     */
    private function jsonTranslations(string $locale)
    {
        return (File::exists(lang_path("{$locale}.json")))
            ? json_decode(File::get(lang_path("{$locale}.json")), true)
            : [];
    }
}
