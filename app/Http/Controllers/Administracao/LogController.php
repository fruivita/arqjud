<?php

namespace App\Http\Controllers\Administracao;

use App\Enums\Policy;
use App\Http\Controllers\Controller;
use App\Http\Resources\Log\LogCollection;
use App\Http\Resources\Log\LogContentResource;
use App\Http\Traits\ComFeedback;
use App\Http\Traits\ComPaginacaoEmCache;
use FruiVita\LineReader\Facades\LineReader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * @see https://laravel.com/docs/controllers
 * @see https://inertiajs.com/server-side-setup
 */
class LogController extends Controller
{
    use ComFeedback;
    use ComPaginacaoEmCache;

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $this->authorize(Policy::LogViewAny->value);

        return Inertia::render('Administracao/Log/Index', [
            'arquivos' => fn () => LogCollection::make(
                collect(File::allFiles($this->storage()->path('')))
                    ->sortBy(fn (\SplFileInfo $arquivo) => $arquivo->getMTime())
                    ->values()
            )->preserveQuery(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $log
     * @return \Inertia\Response
     */
    public function show(string $log)
    {
        $this->authorize(Policy::LogView->value);

        abort_if(
            $this->storage()->missing($log),
            404,
            __('Arquivo nao encontrado')
        );

        return Inertia::render('Administracao/Log/Show', [
            'conteudo' => fn () => LogContentResource::collection(
                LineReader::readPaginatedLines(
                    file_path: $this->storage()->path($log),
                    per_page: $this->perPage(),
                    page: request()->integer('page', 1)
                )
            )->additional(['meta' => [
                'arquivo' => $log,
                'links' => collect(['download' => route('administracao.log.download', $log)])
                    ->when(
                        auth()->user()->can(Policy::LogDelete->value),
                        fn (Collection $collection) => $collection->put('delete', route('administracao.log.destroy', $log)),
                    )
                    ->toArray()
            ]])->preserveQuery()
        ]);
    }

    /**
     * Download the specified resource.
     *
     * @param  string  $log
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(string $log)
    {
        $this->authorize(Policy::LogView->value);

        abort_if(
            $this->storage()->missing($log),
            404,
            __('Arquivo nao encontrado')
        );

        return $this->storage()->download(
            path: $log,
            name: $log,
            headers: [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => "attachment; filename={$log}",
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string $log
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $log)
    {
        $this->authorize(Policy::LogDelete->value);

        abort_if(
            $this->storage()->missing($log),
            404,
            __('Arquivo nao encontrado')
        );

        $excluido = $this->storage()->delete($log);

        return redirect()->action([self::class, 'index'])->with($this->feedback($excluido));
    }

    /**
     * Storage de armazenamento dos arquivos de log.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    private function storage()
    {
        return Storage::disk('log-aplicacao');
    }
}
