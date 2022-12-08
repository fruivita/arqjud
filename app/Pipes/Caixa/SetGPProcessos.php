<?php

namespace App\Pipes\Caixa;

use App\Models\Caixa;
use App\Models\Processo;
use Closure;

/**
 * @see https://www.youtube.com/watch?v=FByQN_d876c
 */
class SetGPProcessos
{
    /**
     * Pipe de atualização do status de guarda permanente de todos os processos
     * da caixa de acordo com o status de guarda permanente desta.
     *
     * @param  \App\Models\Caixa  $caixa
     * @param  \Closure  $next
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function handle(Caixa $caixa, Closure $next)
    {
        $volumes_id = $caixa
            ->load('volumes')
            ->volumes
            ->pluck('id');

        Processo::query()
            ->whereIn('volume_caixa_id', $volumes_id)
            ->update(['guarda_permanente' => $caixa->guarda_permanente]);

        return $next($caixa);
    }
}
